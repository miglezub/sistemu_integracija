<?php
    $logged_in = false;
	$is_admin = false;
	class Result {}

	$google_redirect_url = 'https://localhost/sis_int_3/index.php';
	session_start();
	include_once __DIR__ . '\..\vendor\autoload.php';
	require_once __DIR__ . '\..\calendar\_db.php';
	// New Google client
	$gClient = new Google_Client();
	$gClient->setApplicationName('Sistemų integracijos technologijos L3');
	$gClient->setAuthConfig(__DIR__ . '\..\client_credentials.json');
	$gClient->addScope(Google_Service_Oauth2::USERINFO_PROFILE);
	$gClient->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
	$gClient->addScope(Google_Service_Calendar::CALENDAR);
	$gClient->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
	$gClient->setRedirectUri($google_redirect_url);
	// New Google Service
	$google_oauthV2 = new Google_Service_Oauth2($gClient);
	// LOGOUT?
	if (isset($_REQUEST['logout'])) 
	{
		unset($_SESSION["auto"]);
		unset($_SESSION['token']);
		unset($_SESSION['user_id']);
	}
	// GOOGLE CALLBACK?
	if (isset($_GET['code'])) 
	{
		$gClient->fetchAccessTokenWithAuthCode($_GET['code']);
		$_SESSION['token'] = $gClient->getAccessToken();
		header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
		return;
	}
	// PAGE RELOAD?
	if (isset($_SESSION['token'])) 
	{
		$gClient->setAccessToken($_SESSION['token']);
	}
	// Autologin?
	if(isset($_GET["auto"]))
	{
		$_SESSION['auto'] = $_GET["auto"];
	}
	// LOGGED IN?
	if ($gClient->getAccessToken()) // Sign in
	{
		$logged_in = true;
		//For logged in user, get details from google using access token
		try {
			$user = $google_oauthV2->userinfo->get();
			$user_id              = $user['id'];
			$user_name            = filter_var($user['givenName'], FILTER_SANITIZE_SPECIAL_CHARS);
			$email                = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
			// $gender               = filter_var($user['gender'], FILTER_SANITIZE_SPECIAL_CHARS);
			// $profile_url          = filter_var($user['link'], FILTER_VALIDATE_URL);
			// $profile_image_url    = filter_var($user['picture'], FILTER_VALIDATE_URL);
			// $personMarkup         = "$email<div><img src='$profile_image_url?sz=50'></div>";
			$_SESSION['token']    = $gClient->getAccessToken();
			
			$boolarray = Array(false => 'false', true => 'true');
			
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
			$db_user = $stmt->fetch();
			if(!$db_user) {
				$insert = "INSERT INTO users (email, is_admin) VALUES (:email, 0)";
				$stmt = $db->prepare($insert);
				$stmt->bindParam(':email', $email);

				$stmt->execute();

				$user_select = "SELECT * FROM users WHERE email = :email";
				$stmt = $db->prepare($user_select);
				$stmt->bindParam(':email', $email);
				$stmt->execute();

				$db_user = $stmt->fetch();
            }
            if($db_user['is_admin'] == 1) {
                $is_admin = true;
			}   
			$_SESSION['user_id'] = $db_user['user_id'];
		} catch (Exception $e) {
			// The user revoke the permission for this App! Therefore reset session token	
			unset($_SESSION["auto"]);
			unset($_SESSION['token']);
			unset($_SESSION['user_id']);
			header('Location: ' . filter_var($google_redirect_url, FILTER_SANITIZE_URL));
		}
	}
	else // Sign up
	{
		//For Guest user, get google login url
		// $authUrl = $gClient->createAuthUrl();
		
		$auth_url = $gClient->createAuthUrl();
		// Fast access or manual login button?
		if(isset($_GET["auto"]))
		{
	
			header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
		}
		// else
		// {
		// 	echo '<p>Login?</p>';
		// 	// echo '<a class="login" href="'.$authUrl.'"><img src="images/google-login-button.png" /></a>';
		// 	echo "<a href='$auth_url'>Login Through Google </a>";
		// }
	}
	if(isset($_POST["event"])) {
		addEvent($gClient, $db, $_POST);
	}
	if(isset($_POST["cancel_event"])) {
		cancelEvent($gClient, $db, $_POST);
	}
	if(isset($_POST["freebusy"])) {
		checkFreebusy($gClient,  $_POST);
	}

	function addEvent($gClient, $db,  $post) {
		$service = new Google_Service_Calendar($gClient);
		$event = new Google_Service_Calendar_Event(array(
			'summary' => $post['title'],
			'location' => $post['place'],
			'description' => 'Įvykis automatiškai sugeneruotas pagal registraciją treniruotei',
			'start' => array(
			'dateTime' => $post['timeStart'],
			'timeZone' => 'Europe/Vilnius',
			),
			'end' => array(
			'dateTime' => $post['timeEnd'],
			'timeZone' => 'Europe/Vilnius',
			),
			'reminders' => array(
			'useDefault' => TRUE,
			),
		));
		
		$calendarId = $post['calendar_id'];
		$event = $service->events->insert($calendarId, $event);
		$insert = "INSERT INTO event_to_user (event_id, user_id, arrived, google_event_id, google_calendar_id) VALUES (:event_id, :user_id, 0, :google_event_id, :google_calendar_id)";

		$stmt = $db->prepare($insert);

		$stmt->bindParam(':event_id', $post['event']);
		$stmt->bindParam(':user_id', $_SESSION['user_id']);
		$stmt->bindParam(':google_event_id', $event->id);
		$stmt->bindParam(':google_calendar_id', $post['calendar_id']);
		$stmt->execute();

		$response = new Result();
		$response->result = 'OK';

		header('Content-Type: application/json');
		echo json_encode($response);
	}

	function cancelEvent($gClient, $db,  $post) {
		$service = new Google_Service_Calendar($gClient);

		$eventQuery = $db->prepare("SELECT * FROM event_to_user WHERE event_id = :event_id AND user_id = :user_id");
		$eventQuery->bindParam(':event_id', $post['cancel_event']);
		$eventQuery->bindParam(':user_id', $_SESSION['user_id']);
		$eventQuery->execute();
		$event = $eventQuery->fetch();
		if($event && isset($event['google_event_id']) && isset($event['google_calendar_id'])) {
			$googleCalendarId = $event['google_calendar_id'];
			$googleEventId = $event['google_event_id'];
			$event = $service->events->delete($googleCalendarId, $googleEventId);
			$insert = "DELETE FROM event_to_user WHERE user_id = :user_id AND event_id = :event_id";

			$stmt = $db->prepare($insert);

			$stmt->bindParam(':event_id', $post['cancel_event']);
			$stmt->bindParam(':user_id', $_SESSION['user_id']);
			$stmt->execute();

			$response = new Result();
			$response->result = 'OK';

			header('Content-Type: application/json');
			echo json_encode($response);
			return;
		}
		$response = new Result();
		$response->result = 'ERROR';

		header('Content-Type: application/json');
		echo json_encode($response);
	}

	function checkFreebusy($gClient, $post) {
		$service = new Google_Service_Calendar($gClient);
		$freebusy_req = new Google_Service_Calendar_FreeBusyRequest($gClient);
		$freebusy_req->setTimeMin(date(DateTime::ATOM, strtotime($post['timeStart'])));
		$freebusy_req->setTimeMax(date(DateTime::ATOM, strtotime($post['timeEnd'])));
		$freebusy_req->setTimeZone('Europe/Vilnius');
		$freebusy_req->setCalendarExpansionMax(10);
		$freebusy_req->setGroupExpansionMax(10);
		$item = new Google_Service_Calendar_FreeBusyRequestItem();
		$item->setId($post['calendar_id']);
		$freebusy_req->setItems(array($item));
		$query = $service->freebusy->query($freebusy_req);
		echo json_encode($query->getCalendars()[$post['calendar_id']]);
	}
?>
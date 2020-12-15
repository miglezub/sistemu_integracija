<?php include("include/login_handler.php"); ?>
<html>
	<head>
		<script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCE6LDTVHDg17JwOoOnTVcvkA4Ef6EDSbw&callback=initMap"></script>
		<script src="./js/script.js"></script>
		<script src="./calendar/js/daypilot/daypilot-all.min.js"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

		<meta charset="UTF-8">
		<title>Sistemų integracijos projektas</title>
	</head>
	<body>
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<a class="navbar-brand" href="#">Sistemų integracijos technologijos</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav mr-auto">
			</ul>
			<span class="navbar-text">
				<?php if($logged_in) { ?>
					Sveiki, <?php echo $user_name . " (" . $email . ") "; ?>
					<a href="?logout=1">Atsijungti</a>
				<?php } else { ?>
					<a href="<?php echo $auth_url; ?>">Prisijungti</a>
				<?php } ?>
			</span>
		</div>
	</nav>
	<div id="dpCalendar" class="mx-auto my-3 px-0 mx-0"></div>
		<div class="row" style="height: 480px">
			<!-- <div class="col-md-4 p-5" id="position">
			</div> -->
			<div class="col-md-12 pr-0">
				<div id="map" style="height: 480px"></div>
			</div>
		</div>
		<?php if($is_admin) { ?>
			<div class="modal fade" id="newEventModal" tabindex="-1" role="dialog" aria-labelledby="newEventModalTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="newEventModalTitle">Naujas įvykis</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="eventTitle">Pavadinimas</label>
							<input type="text" class="form-control" id="eventTitle" placeholder="Pavadinimas">
						</div>
						<div class="form-group">
							<label for="eventPlace">Vieta</label>
							<input type="text" class="form-control" id="eventPlace" placeholder="Vieta">
						</div>
						<div class="form-group">
							<label for="eventCoordinates">Koordinatės</label>
							<input type="text" class="form-control" id="eventCoordinates" placeholder="Koordinatės">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Atšaukti</button>
						<button type="button" class="btn btn-primary" onclick="saveEvent()">Išsaugoti</button>
					</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="eventInfoModal" tabindex="-1" role="dialog" aria-labelledby="eventInfoModalTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="eventInfoModalTitle">Treniruotės informacija</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="eventTitle">Pavadinimas</label>
							<input type="text" class="form-control" disabled id="eventInfoTitle" placeholder="Pavadinimas">
						</div>
						<div class="form-group">
							<label for="eventPlace">Vieta</label>
							<input type="text" class="form-control" disabled id="eventInfoPlace" placeholder="Vieta">
						</div>
						<div class="px-3" id="eventInfoText"></div>
					</div>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<div class="modal fade" id="registerEventModal" tabindex="-1" role="dialog" aria-labelledby="registerEventModalTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="registerEventModalTitle">Registracija treniruotei</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="registrationTitle">Pavadinimas</label>
							<input type="text" disabled class="form-control" id="registrationTitle" placeholder="">
						</div>
						<div class="form-group">
							<label for="registrationPlace">Vieta</label>
							<input type="text" disabled class="form-control" id="registrationPlace" placeholder="">
						</div>
						<div class="form-group">
							<label for="registrationTime">Laikas</label>
							<input type="text" disabled class="form-control" id="registrationTime" placeholder="">
						</div>
						<?php include("select_google_calendar.php"); ?>
						<div id="freebusy_response" class="px-3"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Atšaukti</button>
						<button type="button" class="btn btn-primary" id="registerSubmitButton" onclick="saveRegistration()">Išsaugoti</button>
					</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="cancelEventModal" tabindex="-1" role="dialog" aria-labelledby="cancelEventModalTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="cancelEventModalTitle">Registracijos atšaukimas</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Ar tikrai norite atšaukti treniruotę <span id="cancelTitle"></span> (<span id="cancelPlace"></span>) <span id="cancelTime"></span>?<br>
						Treniruotė bus automatiškai ištrinta iš Google kalendoriaus.
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Ne</button>
						<button type="button" class="btn btn-success" onclick="cancelRegistration()">Taip</button>
					</div>
					</div>
				</div>
			</div>
		<?php } ?>
	</body>
</html>
<?php include("include/calendar_scripts.php"); ?>
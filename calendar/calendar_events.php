<?php
require_once '_db.php';

// .events.load() passes start and end as query string parameters by default
$start = $_GET["start"];
$end = $_GET["end"];
   
$stmt = $db->prepare('SELECT * FROM events WHERE NOT ((end <= :start) OR (start >= :end))');

$stmt->bindParam(':start', $start);
$stmt->bindParam(':end', $end);

$stmt->execute();
$result = $stmt->fetchAll();

class Event {}
$events = array();

foreach($result as $row) {
  $e = new Event();
  $e->id = $row['id'];
  $e->text = $row['name'] . " (" . $row['place'] . ")";
  $e->start = $row['start'];
  $e->end = $row['end'];
  $e->backColor = $row['color'];
  $e->registered = 0;
  if(isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT * FROM event_to_user WHERE user_id = :user_id AND event_id = :event_id');
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':event_id', $row['id']);
    $stmt->execute();
    $user_events = $stmt->fetch();
    if($user_events) {
      $e->registered = 1;
      if($user_events['arrived'] == 0 && strtotime($row['start']) > strtotime("now")) {
        $e->barColor = "orange";
      } else if($user_events['arrived'] == 1){
        $e->barColor = "green";
      } else {
        $e->barColor = "red";
      }
    }
  }
  $e->place = $row['place'];
  $e->coordinates = $row['coordinates'];
  $events[] = $e;
}

header('Content-Type: application/json');
echo json_encode($events);

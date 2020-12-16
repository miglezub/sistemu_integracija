<?php
require_once __DIR__ . '\..\calendar\_db.php';
$arrived = $_POST['arrived'] == true ? 1 : 0;
$stmt = $db->prepare("UPDATE event_to_user SET arrived = :arrived WHERE user_id = :user_id AND event_id = :event_id");
$stmt->bindParam(':arrived', $arrived);
$stmt->bindParam(':user_id', $_POST['user']);
$stmt->bindParam(':event_id', $_POST['event']);

$stmt->execute();

class Result {}

$response = new Result();
$response->result = 'OK';
$response->message = 'Update successful';

header('Content-Type: application/json');
echo json_encode($response);

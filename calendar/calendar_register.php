<?php
require_once '_db.php';

$json = file_get_contents('php://input');
$params = json_decode($json);
var_dump($params);
$insert = "INSERT INTO event_to_user (event_id, user_id, arrived) VALUES (:event_id, :user_id, 0)";

$stmt = $db->prepare($insert);

$stmt->bindParam(':event_id', $params->id);
$stmt->bindParam(':user_id', $params->user_id);
$stmt->execute();

class Result {}

$response = new Result();
$response->result = 'OK';
$response->id = $db->lastInsertId();
$response->message = 'Created with id: '.$db->lastInsertId();

header('Content-Type: application/json');
echo json_encode($response);

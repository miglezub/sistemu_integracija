<?php
require_once '_db.php';

$json = file_get_contents('php://input');
$params = json_decode($json);

$insert = "UPDATE events SET name = :name WHERE id = :id";

$stmt = $db->prepare($insert);

$stmt->bindParam(':name', $params->name);
$stmt->bindParam(':id', $params->id);
$stmt->execute();

class Result {}

$response = new Result();
$response->result = 'OK';
$response->message = 'Update successful';

header('Content-Type: application/json');
echo json_encode($response);

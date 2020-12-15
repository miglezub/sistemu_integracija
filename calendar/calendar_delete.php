<?php
require_once '_db.php';

$json = file_get_contents('php://input');
$params = json_decode($json);

$insert = "DELETE FROM events WHERE id = :id";

$stmt = $db->prepare($insert);

$stmt->bindParam(':id', $params->e->id);

$stmt->execute();

class Result {}

$response = new Result();
$response->result = 'OK';
$response->message = 'Update successful';

header('Content-Type: application/json');
echo json_encode($response);

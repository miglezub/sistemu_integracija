<?php
require_once '_db.php';

$stmt = $db->prepare("SELECT u.email, u.user_id, e2u.arrived FROM users u 
    INNER JOIN event_to_user e2u ON (u.user_id = e2u.user_id) 
    WHERE e2u.event_id = :event_id");

$stmt->bindParam(':event_id', $_POST['event_id']);
$stmt->execute();
$results = $stmt->fetchAll();

// class Result {}

// $response = new Result();
// $response->result = 'OK';

header('Content-Type: application/json');
echo json_encode($results);

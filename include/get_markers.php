<?php
require_once __DIR__ . '\..\calendar\_db.php';

$stmt = $db->prepare("SELECT * FROM events GROUP BY coordinates");

$stmt->execute();

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());

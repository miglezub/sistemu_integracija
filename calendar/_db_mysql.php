<?php
$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "sistemu_integracija";

$db = new PDO("mysql:host=$host;port=$port",
    $username,
    $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// other init
date_default_timezone_set("Europe/Vilnius");
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$db->exec("CREATE DATABASE IF NOT EXISTS `$database`");
$db->exec("use `$database`");

function tableExists($dbh, $id)
{
  $results = $dbh->query("SHOW TABLES LIKE '$id'");
  if(!$results) {
    return false;
  }
  if($results->rowCount() > 0) {
    return true;
  }
  return false;
}

$exists = tableExists($db, "events");

if (!$exists) {
  $db->exec("CREATE TABLE IF NOT EXISTS events (
                        id INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL, 
                        name TEXT, 
                        start DATETIME NOT NULL, 
                        end DATETIME NOT NULL,
                        color VARCHAR(30)
                        )");

  $items = array(
      array('name' => 'Event 1',
          'start' => '2020-12-15T10:00:00',
          'end' => '2020-12-15T12:00:00',
          'color' => '')
  );

  $insert = "INSERT INTO events (name, start, end, color) VALUES (:name, :start, :end, :color)";
  $stmt = $db->prepare($insert);

  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':start', $start);
  $stmt->bindParam(':end', $end);
  $stmt->bindParam(':color', $color);

  foreach ($items as $it) {
    $name = $it['name'];
    $start = $it['start'];
    $end = $it['end'];
    $color = $it['color'];
    $stmt->execute();
  }

}

<?php
session_start();
require '../includes/db_connection.php';

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    $stmt = $pdo->prepare("SELECT title, description, event_date, event_time, location, banner, image FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if ($event) {
        echo implode('|', [
            $event['title'],
            $event['banner'],  
            $event['description'],
            $event['event_date'],
            $event['event_time'],
            $event['location']
        ]);
    } else {
        echo "Event tidak ditemukan.";
    }
} else {
    echo "ID event tidak diberikan.";
}

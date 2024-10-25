<?php
session_start();
require '../includes/db_connection.php';

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id']; 


    $user_id = $_SESSION['user_id']; 


    $stmt = $pdo->prepare("SELECT title, description, event_date, event_time, location, banner FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    $registered_stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND id = ?");
    $registered_stmt->execute([$event_id, $user_id]);
    $is_registered = $registered_stmt->fetchColumn() > 0;

  
    if ($event) {
        echo implode('|', [
            $event['title'],
            $event['banner'],
            $event['description'],
            $event['event_date'],
            $event['event_time'],
            $event['location'],
        ]);
        echo '|' . ($is_registered ? 'registered' : 'not_registered');
    } else {
        echo "Event not found.";
    }
} else {
    echo "Event ID not provided.";
}
?>

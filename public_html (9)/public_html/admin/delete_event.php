<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
    $stmt->execute(['event_id' => $event_id]);

    if ($stmt->rowCount()) {
        echo "Event deleted successfully";
    } else {
        echo "Error deleting event";
    }
} else {
    echo "No event ID provided";
}
?>

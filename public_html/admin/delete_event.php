<?php
session_start();
require '../includes/db_connection.php';

header('Content-Type: application/json');  // Set the response type to JSON

if ($_SESSION['role'] != 'admin') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event_id]);

        if ($stmt->rowCount()) {
            echo json_encode(["success" => true, "message" => "Event deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting event or event not found"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No event ID provided"]);
}
?>

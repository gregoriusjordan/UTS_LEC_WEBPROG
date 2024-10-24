<?php
session_start();
require '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $user_id = $_POST['user_id'];

    try {
        $checkStmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ? AND event_id = ?");
        $checkStmt->execute([$user_id, $event_id]);

        if ($checkStmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You have already registered for this event.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO registrations (id, event_id, registered_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$user_id, $event_id])) {
            echo json_encode(['status' => 'success']);
        } else {
            $errorInfo = $stmt->errorInfo();
            error_log(print_r($errorInfo, true)); 
            echo json_encode(['status' => 'error', 'message' => 'Database error occurred.']);
        }
    } catch (Exception $e) {
        error_log($e->getMessage()); 
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

?>

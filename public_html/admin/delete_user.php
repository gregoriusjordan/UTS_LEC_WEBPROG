<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['role'] == 'admin') {
            echo "Admin users cannot be deleted.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $user_id]);

            if ($stmt->rowCount()) {
                echo "User deleted successfully";
            } else {
                echo "Error deleting user";
            }
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "No user ID provided";
}
?>

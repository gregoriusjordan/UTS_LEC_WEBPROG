<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>

<h1>View Users</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Registration Date</th>
            <th>Action</th> 
        </tr>
    </thead>
    <tbody>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td>
                        <?php if ($user['role'] != 'admin'): ?>
                            <button onclick="confirmDeleteUser(<?= $user['id'] ?>)">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No users found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    function confirmDeleteUser(userId) {
        const confirmDelete = confirm('Are you sure you want to delete this user? This action cannot be undone.');
        if (confirmDelete) {
            deleteUser(userId);
        }
    }

    function deleteUser(userId) {
        fetch(`delete_user.php?id=${userId}`, {
            method: 'POST',
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to delete user');
            }
            return response.text();
        })
        .then(result => {
            alert('User deleted successfully!');
            location.reload();  
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            alert('An error occurred while trying to delete the user.');
        });
    }
</script>

<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    button {
        padding: 5px 10px;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
    }
    button:hover {
        background-color: #c82333;
    }
</style>

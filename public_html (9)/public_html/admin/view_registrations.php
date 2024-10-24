<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->query("
    SELECT users.name, users.email, events.title
    FROM registrations 
    JOIN users ON registrations.id = users.id 
    JOIN events ON registrations.event_id = events.event_id 
");

$registrations = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=registrations.csv');

    $output = fopen('php://output', 'w');

    fputcsv($output, ['UserName', 'Email', 'Event Title']);

    foreach ($registrations as $registration) {
        fputcsv($output, [
            $registration['name'],
            $registration['email'],
            $registration['title']
        ]);
    }

    fclose($output);

    exit;
}
?>

<?php include '../includes/navbar.php'; ?>

<h1>View Event Registrations</h1>

<a href="view_registrations.php?export=csv" class="btn btn-primary">Export CSV</a>

<table>
    <thead>
        <tr>    
            <th>UserName</th>
            <th>Email</th>
            <th>Event Title</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($registrations) > 0): ?>
            <?php foreach ($registrations as $registration): ?>
                <tr>
                    <td><?= htmlspecialchars($registration['name']) ?></td>
                    <td><?= htmlspecialchars($registration['email']) ?></td>
                    <td><?= htmlspecialchars($registration['title']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No registrations found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

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
</style>

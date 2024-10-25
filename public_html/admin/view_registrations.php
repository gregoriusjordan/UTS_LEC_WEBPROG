<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

// Check if event_id is provided in the URL
if (!isset($_GET['event_id'])) {
    header("Location: manage_events.php"); // Redirect if event_id is missing
    exit;
}

$event_id = $_GET['event_id'];

// Fetch the event title for the specified event
$stmt = $pdo->prepare("SELECT title FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: manage_events.php"); // Redirect if event doesn't exist
    exit;
}

// Fetch registrations for the specific event
$stmt = $pdo->prepare("
    SELECT users.name, users.email, registrations.id AS user_id
    FROM registrations 
    JOIN users ON registrations.id = users.id 
    WHERE registrations.event_id = :event_id
");
$stmt->execute(['event_id' => $event_id]);
$registrations = $stmt->fetchAll();

// CSV Export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=registrations.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['UserName', 'Email']);

    foreach ($registrations as $registration) {
        fputcsv($output, [$registration['name'], $registration['email']]);
    }
    fclose($output);
    exit;
}

$showPopup = false;
$userIdToDelete = null;
$successMessage = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $userIdToDelete = $_POST['user_id'];
    $showPopup = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_confirm'])) {
    if (isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = :user_id AND event_id = :event_id");
        $stmt->execute(['user_id' => $user_id, 'event_id' => $event_id]);
        $successMessage = true;
    }
}
?>

<?php include '../includes/navbar.php'?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bluey: '#3D41C1',
                        lilac: '#8B63DA',
                        pinky: '#CB98ED',
                        dlilac: '#7A53C7'
                    },
                    fontFamily: {
                        'mont': 'Montserrat',
                        'inter': 'Inter'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            }
                        },
                        slideIn: {
                            '0%': {
                                transform: 'translateY(100px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateY(0)',
                                opacity: '1'
                            }
                        }
                    },
                    animation: {
                        fadeIn: 'fadeIn 1s ease-out',
                        slideIn: 'slideIn 0.4s ease-out',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100">

    <div class="max-w-6xl mx-auto mt-10 p-6 ">
        <h1 class="text-4xl font-bold mb-2 text-lilac text-center">List of Registered Users </h1>
        <h1 class="text-3xl font-bold mb-2 text-dlilac text-center mt-2"> <?= htmlspecialchars($event['title']) ?></h1>

        <div class="<?= $showPopup ? 'blur' : '' ?> mt-8 ">
            <div class="overflow-x-auto rounded-[22px] shadow-md ">
                <table class="min-w-full table-auto text-lg font-inter">
                    <thead>
                        <tr class="bg-lilac text-white text-sm leading-normal ">
                            <th class="py-4 px-10 text-left text-lg rounded-tl-lg">Username</th>
                            <th class="py-4 px-5 text-left text-lg">Email</th>
                            <th class="py-4 text-left text-lg">Event Name</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm font-semibold bg-white">
                        <?php if (count($registrations) > 0): ?>
                            <?php foreach ($registrations as $registration): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    <td class="py-4 px-10 text-left text-base"><?= htmlspecialchars($registration['name']) ?></td>
                                    <td class="py-4 px-5 text-left text-base"><?= htmlspecialchars($registration['email']) ?></td>
                                    <td class="py-4 text-left text-base"><?= htmlspecialchars($event['title']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-4 px-6 text-center">No registrations found for this event.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex justify-end mb-4 px-20">
        <a href="view_registrations.php?event_id=<?= $event_id ?>&export=csv" class="justify-right bg-lilac text-white font-bold py-2 px-4 rounded-full hover:bg-dlilac transition duration-200">
            Export CSV
        </a>
    </div>

</body>
</html>

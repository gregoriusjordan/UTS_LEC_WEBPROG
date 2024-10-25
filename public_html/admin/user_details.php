<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_GET['id'])) { 
    header("Location: view_users.php"); 
    exit; 
}

$user_id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT name, email, profile_picture FROM users WHERE id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

$eventsStmt = $pdo->prepare("SELECT title, event_date, event_time FROM events WHERE event_id = :user_id");
$eventsStmt->execute(['user_id' => $user_id]);
$events = $eventsStmt->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - Eventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
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
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                    },
                    animation: {
                        fadeIn: 'fadeIn 1s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .bg-light { background-color: #F5F0FF; }
        .text-lilac { color: #8B5CF6; }
    </style>
</head>

<body class="bg-light">
    <div class="container w-[800px] mx-auto p-6">
        <div class="bg-white rounded-[20px] shadow-lg p-6 mb-8 mt-8">
            <h2 class="text-2xl font-bold text-lilac mb-6">My Profile</h2>
            <div class="flex justify-between space-x-16">
                <div class="w-[180px] h-[180px] bg-gray-100 rounded-lg overflow-hidden">
                    <img src="../assets/images/profile_pictures/<?= !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : 'default.jpg' ?>" alt="Profile" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 space-y-4">
                    <div>
                        <h3 class="text-lilac font-semibold mb-1">Username</h3>
                        <p><?= htmlspecialchars($user['name']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-lilac font-semibold mb-1">Email</h3>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <div>
                        <h3 class="text-lilac font-semibold mb-1">Password</h3>
                        <p>•••••••</p>
                    </div>
                    <div class="flex space-x-4 mt-4">
                        <a href="edit_profile.php" class="flex items-center space-x-2 px-4 py-2 border border-lilac text-lilac rounded-lg hover:bg-lilac hover:text-white transition">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="history-events" class="mt-24 mb-10">
            <h2 class="text-2xl font-bold text-lilac mb-4">History Events</h2>

            <?php if ($events && count($events) > 0): ?>
                <div class="bg-white rounded-[20px] shadow-lg overflow-hidden">
                    <div class="grid grid-cols-3 bg-lilac text-white p-4">
                        <div>Event</div>
                        <div>Date</div>
                        <div>Time</div>
                    </div>
                    <?php foreach ($events as $event): ?>
                        <div class="grid grid-cols-3 p-4 border-b border-gray-100 last:border-b-0">
                            <div><?= htmlspecialchars($event['title']) ?></div>
                            <div><?= date('d-m-Y', strtotime($event['event_date'])) ?></div>
                            <div><?= date('H:i', strtotime($event['event_time'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">There are no available events for now.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

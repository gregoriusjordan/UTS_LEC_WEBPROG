<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view_events.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email, profile_picture FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT e.title, e.event_date, e.event_time, e.location 
    FROM registrations er 
    JOIN events e ON er.event_id = e.event_id 
    WHERE er.id = :user_id 
    ORDER BY e.event_date DESC
");
$stmt->execute(['user_id' => $user_id]);
$events = $stmt->fetchAll();
?>

<?php include '../includes/navbar.php' ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - Eventory </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@500&display=swap" rel="stylesheet">
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

<body class="bg-light font-inter">

    <div class="container mx-auto p-6 max-w-[800px]">

        <div class="bg-white rounded-[20px] shadow-lg p-6 mb-8 mt-8">
            <h2 class="text-2xl font-bold text-lilac mb-6">My Profile</h2>

            <div class="flex flex-col md:flex-row justify-between space-y-6 md:space-y-0 md:space-x-8">

                <div class="w-[180px] h-[180px] bg-gray-100 rounded-lg overflow-hidden mx-auto md:mx-0">
                    <img
                        src="../assets/images/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>"
                        alt="Profile"
                        class="w-full h-full object-cover">
                </div>

                <div class="flex-1 space-y-4 text-center md:text-left">
                    <div>
                        <h3 class="text-lilac font-montserrat font-semibold mb-1">Username</h3>
                        <p class="font-montserrat text-gray-800"><?= htmlspecialchars($user['name']) ?></p>
                    </div>

                    <div>
                        <h3 class="text-lilac font-montserrat font-semibold mb-1">Email</h3>
                        <p class="font-montserrat text-gray-800"><?= htmlspecialchars($user['email']) ?></p>
                    </div>

                    <div>
                        <h3 class="text-lilac font-montserrat font-semibold mb-1">Password</h3>
                        <p class="font-montserrat text-gray-800">•••••••</p>
                    </div>

                    <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 mt-4">
                        <a href="edit_profile.php" class="flex items-center justify-center space-x-2 px-4 py-2 border border-lilac text-lilac rounded-lg transition hover:bg-lilac hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            <span>Edit Profile</span>
                        </a>
                        <button id="view-history-button" class="flex items-center justify-center space-x-2 px-4 py-2 border border-lilac text-lilac rounded-lg transition hover:bg-lilac hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span>View History Events</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="history-events" class="mt-12 mb-10">
            <h2 class="text-2xl font-bold text-lilac mb-4">History Events</h2>

            <?php if ($events && count($events) > 0): ?>
                <div class="bg-white rounded-[20px] shadow-lg overflow-hidden">
                    <div class="grid grid-cols-3 bg-lilac text-white p-4 text-center">
                        <div class="font-montserrat font-semibold">Event</div>
                        <div class="font-montserrat font-semibold">Date</div>
                        <div class="font-montserrat font-semibold">Time</div>
                    </div>

                    <?php foreach ($events as $event): ?>
                        <div class="grid grid-cols-3 p-4 border-b border-gray-100 last:border-b-0 text-center">
                            <div class="font-inter"><?= htmlspecialchars($event['title']) ?></div>
                            <div class="font-inter"><?= date('d-m-Y', strtotime($event['event_date'])) ?></div>
                            <div class="font-inter"><?= date('H:i', strtotime($event['event_time'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500 font-inter">
                    There is no available events for now.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('view-history-button').addEventListener('click', function() {
            document.getElementById('history-events').scrollIntoView({
                behavior: 'smooth'
            });
        });
    </script>
</body>

</html>

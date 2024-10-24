<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view_events.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id");
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

<?php include '../includes/navbar.php'?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@500&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-purple: #8B5CF6;
            --light-purple: #EDE9FE;
            --white: #FFFFFF;
            --gray-100: #F3F4F6;
            --gray-300: #D1D5DB;
        }

        .font-inter {
            font-family: 'Inter', sans-serif;
        }
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
        .font-rethink {
            font-family: 'Red Hat Display', sans-serif;
        }
        .bg-primary {
            background-color: #8B5CF6;
        }
        .text-primary {
            color: #8B5CF6;
        }
        .border-primary {
            border-color: #8B5CF6;
        }
        .bg-light {
            background-color: #F5F0FF;
        }

        /* Navbar Styles */
        .navbar {
            background: linear-gradient(90deg, #8B5CF6 0%, #D8B4FE 100%);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar .logo {
            color: var(--white);
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .navbar .nav-links a, 
        .navbar .nav-links span {
            color: var(--white);
            text-decoration: none;
            font-family: 'Rethink Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-light min-h-screen">

    <!-- Main Content -->
    <div class="container mx-auto p-6">
        <!-- Profile Card -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-inter font-bold text-primary mb-6">My Profile</h2>
            
            <div class="flex items-start space-x-8">
                <!-- Profile Image -->
                <div class="w-32 h-32 bg-gray-100 rounded-lg overflow-hidden">
                    <img 
                        src="<?= isset($user['avatar']) ? htmlspecialchars($user['avatar']) : 'path_to_default_avatar.jpg' ?>" 
                        alt="Profile" 
                        class="w-full h-full object-cover"
                    >
                </div>
                
                <!-- Profile Information -->
                <div class="flex-1 space-y-4">
                    <div>
                        <h3 class="text-primary font-montserrat font-semibold mb-1">Username</h3>
                        <p class="font-rethink text-gray-800"><?= htmlspecialchars($user['name']) ?></p>
                    </div>
                    
                    <div>
                        <h3 class="text-primary font-montserrat font-semibold mb-1">Email</h3>
                        <p class="font-rethink text-gray-800"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    
                    <div>
                        <h3 class="text-primary font-montserrat font-semibold mb-1">Password</h3>
                        <p class="font-rethink text-gray-800">•••••••</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-4 mt-4 group">
                        <a href="edit_profile.php" class="flex items-center space-x-2 px-4 py-2 border border-primary text-primary rounded-lg transition-colors group-hover:bg-">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            <span>Edit Profile</span>
                        </a>
                        <button class="flex items-center space-x-2 px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            <span>View History Events</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Events Section -->
        <div>
            <h2 class="text-2xl font-inter font-bold text-primary mb-4">History Events</h2>
            
            <?php if ($events && count($events) > 0): ?>
                <div class="bg-white rounded-lg overflow-hidden">
                    <!-- Table Header -->
                    <div class="grid grid-cols-3 bg-primary text-white p-4">
                        <div class="font-montserrat font-semibold">Event</div>
                        <div class="font-montserrat font-semibold">Date</div>
                        <div class="font-montserrat font-semibold">Time</div>
                    </div>
                    
                    <!-- Table Content -->
                    <?php foreach ($events as $event): ?>
                        <div class="grid grid-cols-3 p-4 border-b border-gray-100 last:border-b-0">
                            <div class="font-rethink"><?= htmlspecialchars($event['title']) ?></div>
                            <div class="font-rethink"><?= date('d-m-Y', strtotime($event['event_date'])) ?></div>
                            <div class="font-rethink"><?= date('H:i:s', strtotime($event['event_time'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500 font-rethink">
                    There is no available events for now.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
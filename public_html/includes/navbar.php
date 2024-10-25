<?php

require '../includes/db_connection.php';

$currentPage = basename($_SERVER['PHP_SELF'], ".php");

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$default_picture = 'default.jpg';
if (empty($user['profile_picture']) || !file_exists($user['profile_picture'])) {
    $user['profile_picture'] = $default_picture;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Manager</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/f62928dd38.js" crossorigin="anonymous"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bluey: '#3D41C1',
                    },
                    fontFamily: {
                        'montserrat': 'Montserrat',
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
                        slideIn: 'slideIn 1s ease-out',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-[#F2ECF7] font-inter">
    <nav class="bg-gradient-to-r from-[#8B63DA] via-[#C598E6] to-[#FFCCF2] shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[90px]">
                <div class="flex items-center space-x-8">
                    <a href="#" class="text-white font-bold text-xl flex-shrink-0">
                        <img class="w-[140px]" src="../assets/images/logo.svg" alt="Logo">
                    </a>

                    <!-- desktop menu -->
                    <div class="hidden md:flex space-x-8 font-bold text-white transition">
                        <?php if (isset($_SESSION['role'])): ?>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="dashboard.php" class="hover:text-white transition ms-6">
                                    <i class="fa-solid fa-grip me-2"></i>
                                    <span class="">Dashboard</span></a>
                                <a href="add_event.php" class="hover:text-white transition">
                                    <i class="fa-regular fa-calendar-plus"></i>
                                    <span class="ms-2">Add Event</span>
                                </a>
                                <a href="manage_events.php" class="hover:text-white transition">
                                <i class="fa-regular fa-clipboard me-2"></i>
                                Manage Events</a>
                                <a href="view_users.php" class="hover:text-white transition"><i class="fa-solid fa-address-book me-2"></i>Manage Users</a>
                            <?php elseif ($_SESSION['role'] === 'user'): ?>
                                <a href="view_events.php" class="hover:text-white transition">
                                    <i class="fa-solid fa-grip me-2"></i>
                                    <span class="me-4">Dashboard</span>
                                </a>
                                <a href="registered_event.php" class="hover:text-white transition">
                                    <i class="fa-solid fa-calendar-check me-2"></i>
                                    Registered Event
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div id="user-menu" class="hidden absolute right-[120px] top-[70px] mt-2 w-[200px] bg-white rounded-lg shadow-lg py-2 z-20 font-bold">
                            <a href="view_profile.php" class="block px-4 py-2 text-[#8B63DA] hover:bg-gray-200">My Profile</a>
                            <hr />
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="../logout.php" class="block px-4 py-2 text-[#8B63DA] hover:bg-gray-200">Log Out</a>
                            <?php else: ?>
                                <a href="../logout.php" class="block px-4 py-2 text-[#8B63DA] hover:bg-gray-200">Log Out</a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
                    <div id="user-menu-toggle" class="hidden md:flex items-center cursor-pointer">
                        <span class="text-white text-lg mx-2">Hello, <b><?= htmlspecialchars($user['name']) ?></b></span>
                        <i class="fas fa-chevron-down text-white text-sm me-4"></i>
                        <img src="../assets/images/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>?<?= time() ?>" class="h-12 w-12 rounded-full border-2 border-white" alt="Profile">
                    </div>
    

                <div class="md:hidden">
                    <button id="mobile-menu-toggle" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl me-4"></i>
                    </button>
                </div>
            </div>
        </div>


        <!-- mobile menu -->
        <div id="mobile-menu" class="hidden bg-gradient-to-r from-[#8B63DA] from-40% via-[#C598E6] to-[#FFCCF2] px-4 pt-2 pb-3 space-y-2 font-inter font-bold text-center">
            <hr />
            <?php if (isset($_SESSION['role'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="manage_events.php" class="block text-white hover:text-gray-300 transition">Home</a>
                    <a href="add_event.php" class="block text-white hover:text-gray-300 transition">Add Event</a>
                    <a href="manage_events.php" class="block text-white  hover:text-gray-300 transition">Manage Events</a>
                    <a href="view_users.php" class="block text-white hover:text-gray-300 transition">Manage Users</a>
                    <a href="view_registrations.php" class="block text-white hover:text-gray-300 transition">View Registrations</a>
                <?php elseif ($_SESSION['role'] === 'user'): ?>
                    <a href="view_events.php" class="block text-white hover:text-gray-300 transition">Home</a>
                    <a href="registered_event.php" class="block text-white hover:text-gray-300 transition">Registered Event</a>
                <?php endif; ?>
                <a href="view_profile.php" class="block text-white  transition">My Profile</a>
                <a href="logout.php" class="block text-white px-3 pb-2 rounded-md">Log Out</a>
            <?php else: ?>
                <a href="login.php" class="block text-white hover:text-gray-300 transition">Login</a>
                <a href="register.php" class="block text-white hover:text-gray-300 transition">Register</a>
            <?php endif; ?>
        </div>
    </nav>

    <script>
        document.getElementById('user-menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        });


        document.addEventListener('click', function(event) {
            const menu = document.getElementById('user-menu');
            const toggle = document.getElementById('user-menu-toggle');
            if (!toggle.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>

</body>

</html>
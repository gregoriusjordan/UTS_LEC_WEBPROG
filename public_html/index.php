<?php
session_start();
require 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin/manage_events.php");
        } else {
            header("Location: user/view_events.php");
        }
        exit();
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Eventory</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        lilac: '#3D41C1',
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
                        slideIn: 'slideIn 1s ease-out',
                    }
                }
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/f62928dd38.js" crossorigin="anonymous"></script>

</head>

<body class="h-screen flex overflow-hidden font-mont">

    <div class="relative w-full h-full flex justify-center items-center bg-gradient-to-br from-pinky to-lilac md:h-1/2 lg:w-[900px] lg:w-1/2 lg:h-full animate-fadeIn">
        
        <img src="assets/images/logo.svg" alt="logo" class="absolute top-0 left-0 z-20 mx-6 my-4 max-sm:hidden">
        <div class="max-lg:absolute max-lg:bottom-[1px]">
            <img class="w-[660px]" src="assets/images/design/bg_login.png" alt="bg">
        </div>
        <div class="absolute p-4 top-4 left-4 lg:top-auto lg:left-auto md:flex md:justify-center animate-slideIn">
            <img src="assets/images/logo.svg" alt="logo" class="h-[80px] md:h-[120px] lg:hidden">
        </div>
    </div>

    <div class="absolute inset-0 flex items-center justify-center md:static lg:relative lg:w-[500px] lg:flex lg:items-center lg:justify-center max-md:justify-center animate-slideIn mx-4">
    <div class="max-sm:bg-white max-w-md w-full md:w-3/4 p-6 max-md:rounded-lg max-md:shadow-lg max-sm:m-4 md:absolute md:bg-opacity-80 md:backdrop-filter md:backdrop-blur-sm">
            <h2 class="text-3xl md:text-4xl font-montserrat font-bold text-center mb-6 md:mb-10 text-lilac tracking-tight">Welcome Back</h2>

            <?php if (!empty($error)): ?>
                <div class="text-red-500 text-center mb-4 font-semibold">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="relative mb-4">
                    <i class="fa fa-envelope-o absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" class="p-2 px-4 pl-10 mt-1 block w-full h-12 border border-gray-300 bg-gray-200 rounded-lg" id="username" name="email" placeholder="Email" required>
                </div>

                <div class="relative mb-2">
                    <i class="fa fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" class="p-2 px-4 pl-10 mt-1 block w-full h-12 border border-gray-300 bg-gray-200 rounded-lg" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="flex justify-end mb-6">
                    <a class="text-sm text-gray-400 hover:underline" href="forgot.php">Forgot password?</a>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="w-2/6 md:w-2/6 bg-lilac text-white font-bold py-2 rounded-[40px] hover:bg-blue-800 transition duration-200">Log in</button>
                </div>
                <p class="mt-4 text-center text-sm font-mont font-semibold text-lg">Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register here</a></p>
            </form>
        </div>
    </div>

</body>

</html>
<?php
require 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']); 
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $role = 'user';  

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    $stmt->execute(['name' => $name, 'email' => $email, 'password' => $password, 'role' => $role]);

    header("Location: index.php");
    exit(); 
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
        
        <div class="">
            <img class="w-[590px]" src="assets/images/design/bg_reg.png" alt="bg">
        </div>
        <div class="absolute p-4 top-4 left-4 lg:top-auto lg:left-auto md:flex md:justify-center animate-slideIn">
            <img src="assets/images/logo.svg" alt="logo" class="h-[80px] md:h-[120px] md:hidden">
        </div>
    </div>


    <div class="absolute inset-0 flex items-center justify-center md:static lg:relative lg:w-[500px] lg:flex lg:items-center lg:justify-center max-md:justify-center animate-slideIn mx-4">
        <div class="max-sm:bg-white max-w-md w-full md:w-3/4 p-6 max-md:rounded-lg max-md:shadow-lg max-sm:m-4 md:absolute md:bg-opacity-80 md:backdrop-filter md:backdrop-blur-sm">
            <h2 class="text-3xl md:text-4xl font-montserrat font-bold text-center mb-6 md:mb-10 text-lilac tracking-tight">Create Account</h2>

            <form method="POST">
                <div class="relative mb-4">
                    <i class="fa fa-user-o absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="name" id="name" required placeholder="Name" 
                        class="p-2 px-4 pl-10 mt-1 block w-full h-12 border border-gray-300 bg-gray-200 rounded-lg">
                </div>

                <div class="relative mb-4">
                    <i class="fa fa-envelope-o absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" name="email" id="email" required placeholder="Email"
                        class="p-2 px-4 pl-10 mt-1 block w-full h-12 border border-gray-300 bg-gray-200 rounded-lg">
                </div>

                <div class="relative mb-6">
                    <i class="fa fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="password" id="password" required placeholder="Password"
                        class="p-2 px-4 pl-10 mt-1 block w-full h-12 border border-gray-300 bg-gray-200 rounded-lg">
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="w-2/6 md:w-2/6 bg-lilac text-white font-bold py-2 rounded-[40px] hover:bg-blue-800 transition duration-200">
                        Register
                    </button>
                </div>
            </form>
            <p class="mt-4 text-center text-sm font-mont font-semibold text-lg">Already have an account? <a href="index.php" class="text-blue-600 hover:underline">Log in here</a></p>
        </div>
    </div>

</body>

</html>

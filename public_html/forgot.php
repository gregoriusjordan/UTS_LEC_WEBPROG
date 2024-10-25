<?php
session_start();
require 'includes/db_connection.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(50));

        $stmt = $pdo->prepare("UPDATE users SET reset_token = :token, reset_expiry = :expiry WHERE email = :email");
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt->execute(['token' => $token, 'expiry' => $expiry, 'email' => $email]);

        $resetLink = "http://localhost/reset_password.php?token=" . $token;

        $subject = "[EVENTORY] Password Reset Request";
        $message = "Hi, \n\nYou requested a password reset. Click the link below to reset your password:\n\n" . $resetLink . "\n\nIf you did not request this, please ignore this email.";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp-relay.brevo.com';
            $mail->SMTPAuth = true;
            $mail->Username = '7e9c47002@smtp-brevo.com';
            $mail->Password = '10jFCQBAS9h72TdJ';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;


            $mail->setFrom('gregoriusjordan@gmail.com', 'Mailer');
            $mail->addAddress($email);


            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $message;


            $mail->send();
            $success = "Password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
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
            <img src="assets/images/logo.svg" alt="logo" class="h-[80px] md:h-[120px] lg:hidden">
        </div>
    </div>

    <div class="absolute inset-0 flex items-center justify-center md:static lg:relative lg:w-[500px] lg:flex lg:items-center lg:justify-center max-md:justify-center animate-slideIn mx-4">
        <div class="max-sm:bg-white max-w-md w-full md:w-3/4 p-6 max-md:rounded-lg max-md:shadow-lg max-sm:m-4 md:absolute md:bg-opacity-80 md:backdrop-filter md:backdrop-blur-sm">
            <h2 class="text-3xl md:text-4xl font-montserrat font-bold text-center mb-6 md:mb-10 text-lilac tracking-tight">Forgot Password</h2>

            <?php if (isset($success)): ?>
                <p class="text-green-500 text-center mb-4"><?= $success ?></p>
                <p class="text-center mb-4 font-semibold">You can now <a href="index.php" class="text-blue-600 hover:underline">go back to login</a>.</p>
            <?php elseif (isset($error)): ?>
                <p class="text-red-500 text-center mb-4"><?= $error ?></p>
                <p class="text-center mb-4">Please try again or <a href="index.php" class="text-blue-600 hover:underline">return to login</a>.</p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="relative mb-4">
                    <i class="fa fa-envelope-o absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="email" name="email" id="email" required placeholder="Enter your email"
                        class="p-2 px-4 pl-10 mt-1 block w-full h-12 border border-gray-300 bg-gray-200 rounded-lg">
                </div>

                <div class="flex justify-center">
                    <button type="submit" class="md:mt-6 w-4/6 md:w-3/6 bg-lilac text-white font-bold py-2 rounded-[40px] hover:bg-blue-800 transition duration-200">
                        Send Reset Link
                    </button>
                </div>
            </form>

            <p class="mt-4 text-center text-sm font-mont font-semibold text-lg">Remember your password? <a href="index.php" class="text-blue-600 hover:underline">Log in here</a></p>
        </div>
    </div>

</body>

</html>
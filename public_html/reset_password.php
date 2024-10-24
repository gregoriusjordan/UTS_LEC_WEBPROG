<?php
session_start();
require 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = htmlspecialchars($_POST['token']);
    $new_password = htmlspecialchars($_POST['new_password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_expiry > :current_time");
        $stmt->execute([
            'token' => $token,
            'current_time' => date('Y-m-d H:i:s')
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_expiry = NULL WHERE reset_token = :token");
            $stmt->execute([
                'password' => $hashed_password,
                'token' => $token
            ]);

            $success = "Your password has been reset successfully. You can now log in.";
        } else {
            $error = "Invalid or expired token.";
        }
    }
} else if (isset($_GET['token'])) {
    $token = htmlspecialchars($_GET['token']);
} else {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="bg-white shadow-lg rounded-lg p-8 max-w-md w-full">
        <h1 class="text-2xl font-bold text-center mb-6">Reset Password</h1>

        <?php if (isset($success)): ?>
            <p class="text-green-500 text-center mb-4"><?= $success ?></p>
            <p class="text-center mb-4"><a href="login.php" class="text-indigo-600">Go back to login</a>.</p>
        <?php elseif (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?= $error ?></p>
            <p class="text-center mb-4"><a href="login.php" class="text-indigo-600">Return to login</a>.</p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="token" value="<?= isset($token) ? $token : '' ?>">

            <div class="mb-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password:</label>
                <input type="password" name="new_password" id="new_password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <button type="submit" class="w-full py-2 px-4 bg-indigo-600 text-white font-semibold rounded-md shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Reset Password
            </button>
        </form>
    </div>

</body>

</html>
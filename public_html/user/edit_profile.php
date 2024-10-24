<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (!empty($password)) {
        if ($password !== $password_confirm) {
            $errors[] = 'Passwords do not match';
        }
    }

    if (count($errors) === 0) {
        $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $stmt->execute(['name' => $name, 'email' => $email, 'id' => $user_id]);

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute(['password' => $hashed_password, 'id' => $user_id]);
        }

        header("Location: view_profile.php");
        exit;
    }
}
?>

<?php include '../includes/navbar.php'; ?>

<div class="container w-3/4 mx-auto p-6 mt-10 bg-white rounded-lg shadow-lg">
    <h1 class="text-3xl font-bold text-center mb-6 text-indigo-600">Edit Your Profile</h1>

    <form action="edit_profile.php" method="POST" class="space-y-6">
        <div class="mb-4">
            <label for="name" class="block mb-2 font-semibold text-gray-700">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block mb-2 font-semibold text-gray-700">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200" required>
        </div>

        <div class="mb-4">
            <label for="password" class="block mb-2 font-semibold text-gray-700">New Password (optional):</label>
            <input type="password" id="password" name="password" class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200">
        </div>

        <div class="mb-4">
            <label for="password_confirm" class="block mb-2 font-semibold text-gray-700">Confirm New Password:</label>
            <input type="password" id="password_confirm" name="password_confirm" class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200">
        </div>

        <?php if (count($errors) > 0): ?>
            <div class="mb-4">
                <ul class="bg-red-100 border border-red-400 text-red-700 p-3 rounded">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md hover:bg-indigo-700 transition duration-200">Update Profile</button>
    </form>
</div>

<!-- Include Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

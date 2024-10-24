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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Eventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@700&family=Montserrat:wght@600&family=Rethink+Sans:wght@500&display=swap" rel="stylesheet">
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

        body {
            font-family: 'Rethink Sans', sans-serif;
            background-color: var(--light-purple);
            min-height: 100vh;
        }

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

        .main-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            color: var(--primary-purple);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }

        .profile-image-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin-bottom: 2rem;
        }

        .profile-image {
            width: 100%;
            height: 100%;
            border-radius: 10px;
            object-fit: cover;
            background-color: var(--gray-100);
        }

        .camera-icon {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.5);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .camera-icon svg {
            width: 20px;
            height: 20px;
            color: var(--white);
        }

        .hidden {
            display: none;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            color: var(--primary-purple);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .form-input {
            padding: 0.75rem;
            background-color: var(--gray-100);
            border: none;
            border-radius: 8px;
            font-family: 'Rethink Sans', sans-serif;
            font-size: 0.875rem;
            color: #1F2937;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: 2px solid var(--primary-purple);
            background-color: var(--white);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-save {
            background-color: var(--primary-purple);
            color: var(--white);
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-save:hover {
            background-color: #7C3AED;
        }

        .btn-cancel {
            background-color: var(--white);
            color: var(--primary-purple);
            padding: 0.75rem 2rem;
            border: 1px solid var(--primary-purple);
            border-radius: 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: var(--light-purple);
        }

        /* Error Messages */
        .error-container {
            background-color: #FEE2E2;
            border: 1px solid #EF4444;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #DC2626;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Eventory
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="registered_events.php">Registered Events</a>
            <span>Hello, <?= htmlspecialchars($user['name']) ?></span>
        </div>
    </nav>

    <div class="main-container">
        <div class="profile-card">
            <h1 class="profile-header">Edit Profile</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-container">
                    <?php foreach ($errors as $error): ?>
                        <p class="error-message"><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="edit_profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
                <div class="profile-image-container">
                    <img 
                        src="<?= !empty($user['profile_image']) 
                            ? '../uploads/profile_images/' . htmlspecialchars($user['profile_image']) 
                            : '../assets/images/default-avatar.png' ?>" 
                        alt="Profile" 
                        class="profile-image"
                    >
                    <label for="profile_image" class="camera-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden">
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label">Username:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Old Password:</label>
                    <input type="password" name="old_password" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">New Password:</label>
                    <input type="password" name="new_password" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password:</label>
                    <input type="password" name="confirm_password" class="form-input">
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-save">Save</button>
                    <a href="view_profile.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-image').src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html>
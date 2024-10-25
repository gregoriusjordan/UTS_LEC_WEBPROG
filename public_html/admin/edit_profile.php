<?php
session_start();
require '../includes/db_connection.php';

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
    $profile_picture = $_FILES['profile_image'];

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

    if (!empty($profile_picture['name'])) {
        $target_dir = "../assets/images/profile_pictures/";
        $target_file = $target_dir . basename($profile_picture['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($profile_picture['tmp_name']);
        if ($check === false) {
            $errors[] = "File is not an image.";
        }

        if ($profile_picture["size"] > 2000000) {
            $errors[] = "Sorry, your file is too large.";
        }

        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $errors[] = "Sorry, only JPG, JPEG & PNG files are allowed.";
        }

        if (count($errors) === 0) {
            if (!move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                $errors[] = "Sorry, there was an error uploading your file.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :id");
                $stmt->execute(['profile_picture' => basename($profile_picture['name']), 'id' => $user_id]);
            }
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

    if (count($errors) === 0) {
    $stmt = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id");
    $stmt->execute(['name' => $name, 'email' => $email, 'id' => $user_id]);

    
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute(['password' => $hashed_password, 'id' => $user_id]);
    }

    if (!empty($profile_picture['name'])) {
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = :profile_picture WHERE id = :id");
        $stmt->execute(['profile_picture' => basename($profile_picture['name']), 'id' => $user_id]);
     
        $_SESSION['user']['profile_picture'] = basename($profile_picture['name']);
    }

    header("Location: view_profile.php");
    exit;
}

    
}
?>

<?php include '../includes/navbar.php' ?>

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
            --primary-purple: #8B63DA;
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
            font-size: 1.1rem;
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
            background-color: #7A53C7;
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
                        src="<?= !empty($user['profile_picture'])
                                    ? '../assets/images/profile_pictures/' . htmlspecialchars($user['profile_picture'])
                                    : '../assets/images/profile_pictures/default.jpg' ?>"
                        alt="Profile"
                        class="profile-image" />

                    <label for="profile_image" class="camera-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
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
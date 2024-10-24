<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['event_id'])) {
    header("Location: admin/manage_events"); 
    exit;
}

$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: admin/manage_events"); 
    exit;
}

$uploadError = '';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'bmp', 'gif'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $max_participants = $_POST['max_participants'];
    $status = $_POST['status'];

    $imageName = $event['image']; 
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $image = $_FILES['event_image'];
        $image_ext = pathinfo($image['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($image_ext), $allowed_extensions)) {
            if (!empty($event['image']) && file_exists('../assets/images/' . $event['image'])) {
                unlink('../assets/images/' . $event['image']);
            }

            $imageName = uniqid() . '.' . $image_ext;
            $imagePath = '../assets/images/' . $imageName;
            move_uploaded_file($image['tmp_name'], $imagePath);
        } else {
            $uploadError = 'Invalid file type for event image. Only jpg, jpeg, png, svg, webp, bmp, gif files are allowed.';
        }
    }

    $bannerName = $event['banner'];
    if (isset($_FILES['event_banner']) && $_FILES['event_banner']['error'] == 0) {
        $banner = $_FILES['event_banner'];
        $banner_ext = pathinfo($banner['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($banner_ext), $allowed_extensions)) {
            if (!empty($event['banner']) && file_exists('../assets/images/' . $event['banner'])) {
                unlink('../assets/images/' . $event['banner']);
            }

            $bannerName = uniqid() . '_banner.' . $banner_ext;
            $bannerPath = '../assets/images/' . $bannerName;
            move_uploaded_file($banner['tmp_name'], $bannerPath);
        } else {
            $uploadError = 'Invalid file type for event banner. Only jpg, jpeg, png, svg, webp, bmp, gif files are allowed.';
        }
    }
    if (empty($uploadError)) {
        $stmt = $pdo->prepare("UPDATE events SET title = :title, description = :description, event_date = :event_date, event_time = :event_time, location = :location, max_participants = :max_participants, status = :status, image = :image, banner = :banner WHERE event_id = :event_id");
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'event_date' => $event_date,
            'event_time' => $event_time,
            'location' => $location,
            'max_participants' => $max_participants,
            'status' => $status,
            'image' => $imageName,
            'banner' => $bannerName,
            'event_id' => $event_id
        ]);
        header("Location: " . $baseURL . "manage_events");
        exit;
    }
}
?>
<?php if (!empty($uploadError)): ?>
    <p style="color: red;"><?= $uploadError ?></p>
<?php endif; ?>

<?php include '../includes/navbar.php'; ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-6 bg-white shadow-md rounded-md mt-10">
    <h1 class="text-2xl font-bold mb-6 text-center text-indigo-600">Edit Event</h1>

    <?php if (!empty($uploadError)): ?>
        <p class="text-red-600 mb-4"><?= htmlspecialchars($uploadError) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Event Title:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description:</label>
            <textarea name="description" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Event Date:</label>
                <input type="date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Event Time:</label>
                <input type="time" name="event_time" value="<?= htmlspecialchars($event['event_time']) ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Location:</label>
                <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Max Participants:</label>
                <input type="number" name="max_participants" value="<?= htmlspecialchars($event['max_participants']) ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Status:</label>
            <select name="status"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="open" <?= $event['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                <option value="closed" <?= $event['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                <option value="canceled" <?= $event['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Event Image (Optional):</label>
                <input type="file" name="event_image" accept=".jpg,.jpeg,.png,.svg,.webp,.bmp,.gif"
                    class="mt-1 block w-full text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-md cursor-pointer">
                <p class="mt-1 text-sm text-gray-500">Current Image: <?= htmlspecialchars($event['image']) ?></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Event Banner (Optional):</label>
                <input type="file" name="event_banner" accept=".jpg,.jpeg,.png,.svg,.webp,.bmp,.gif"
                    class="mt-1 block w-full text-sm text-gray-900 bg-gray-50 border border-gray-300 rounded-md cursor-pointer">
                <p class="mt-1 text-sm text-gray-500">Current Banner: <?= htmlspecialchars($event['banner']) ?></p>
            </div>
        </div>

        <button type="submit"
            class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Update Event
        </button>
    </form>
</div>

</body>
</html>

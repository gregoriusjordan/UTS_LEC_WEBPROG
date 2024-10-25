<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../index.php"); 
    exit; 
}

if (!isset($_GET['event_id'])) {
    header("Location: manage_events.php");
    exit;
}

$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
$stmt->execute(['event_id' => $event_id]);
$event = $stmt->fetch();

if (!$event) {
    header("Location: manage_events.php");
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
        $image_ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        if (in_array($image_ext, $allowed_extensions)) {
            if (!empty($event['image']) && file_exists('../assets/images/' . $event['image'])) {
                unlink('../assets/images/' . $event['image']);
            }

            $imageName = uniqid() . '.' . $image_ext;
            $imagePath = '../assets/images/' . $imageName;
            if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
                $uploadError = 'Failed to upload event image.';
            }
        } else {
            $uploadError = 'Invalid file type for event image.';
        }
    }

    $bannerName = $event['banner'];
    if (isset($_FILES['event_banner']) && $_FILES['event_banner']['error'] == 0) {
        $banner = $_FILES['event_banner'];
        $banner_ext = strtolower(pathinfo($banner['name'], PATHINFO_EXTENSION));

        if (in_array($banner_ext, $allowed_extensions)) {
            if (!empty($event['banner']) && file_exists('../assets/images/' . $event['banner'])) {
                unlink('../assets/images/' . $event['banner']);
            }

            $bannerName = uniqid() . '_banner.' . $banner_ext;
            $bannerPath = '../assets/images/' . $bannerName;
            if (!move_uploaded_file($banner['tmp_name'], $bannerPath)) {
                $uploadError = 'Failed to upload event banner.';
            }
        } else {
            $uploadError = 'Invalid file type for event banner.';
        }
    }

    if (empty($uploadError)) {
        $stmt = $pdo->prepare("UPDATE events SET title = :title, description = :description, event_date = :event_date, event_time = :event_time, location = :location, max_participants = :max_participants, status = :status, image = :image, banner = :banner WHERE event_id = :event_id");

        try {
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
            header("Location: " . $baseURL . "manage_events.php");
            exit;
        } catch (Exception $e) {
            $uploadError = 'Database update failed: ' . $e->getMessage();
        }
    }
}
?>

<?php include '../includes/navbar.php'?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bluey: '#3D41C1',
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
                        slideIn: 'slideIn 0.4s ease-out',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6 mt-10 mb-4">
        <h1 class="text-[28px] font-bold mb-6 text-center text-lilac">Edit Event</h1>

        <?php if (!empty($uploadError)): ?>
            <p class="text-red-600 mb-4"><?= htmlspecialchars($uploadError) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div>
                <label class="block text-md font-semibold text-lilac">Event Title:</label>
                <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-md font-semibold text-lilac">Description:</label>
                <textarea name="description" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"><?= htmlspecialchars($event['description']) ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-md font-semibold text-lilac">Event Date:</label>
                    <input type="date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-md font-semibold text-lilac">Event Time:</label>
                    <input type="time" name="event_time" value="<?= htmlspecialchars($event['event_time']) ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-md font-semibold text-lilac">Location:</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-md font-semibold text-lilac">Max Participants:</label>
                    <input type="number" name="max_participants" value="<?= htmlspecialchars($event['max_participants']) ?>" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-md font-semibold text-lilac">Status:</label>
                <select name="status"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-full shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="open" <?= $event['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="closed" <?= $event['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="canceled" <?= $event['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                </select>
            </div>

            <div class="mb-4 relative">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Image (Optional):</label>
                <input type="file" id="eventImageInput" name="event_image" accept=".jpg,.jpeg,.png,.svg,.webp,.bmp,.gif"
                    class="opacity-0 absolute inset-0 z-50 cursor-pointer w-full h-full "
                    onchange="updateFileName('eventImageInput', 'eventImagePlaceholder')">
                <input type="text" id="eventImagePlaceholder" placeholder="Choose File"
                    class="w-full text-gray-700 px-4 py-2 rounded-full bg-white cursor-pointer border-2 border-gray-300" readonly>
                <button type="button" class="absolute right-2 top-1/2 transform -translate-y bg-lilac text-white font-bold px-3 py-1 rounded-full">Browse</button>
            </div>

            <div class="mb-4 relative">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Banner (Optional):</label>
                <input type="file" id="eventBannerInput" name="event_banner" accept=".jpg,.jpeg,.png,.svg,.webp,.bmp,.gif"
                    class="opacity-0 absolute inset-0 z-50 cursor-pointer w-full h-full"
                    onchange="updateFileName('eventBannerInput', 'eventBannerPlaceholder')">
                <input type="text" id="eventBannerPlaceholder" placeholder="Choose File"
                    class="w-full text-gray-700 px-4 py-2 rounded-full bg-white cursor-pointer" readonly>
                <button type="button" class="absolute right-2 top-1/2 transform -translate-y bg-lilac text-white font-bold px-3 py-1 rounded-full">Browse</button>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="w-full md:w-auto bg-lilac hover:bg-dlilac text-white font-semibold py-2 px-4 rounded-[22px] shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Update Event
                </button>
            </div>
        </form>
    </div>
</body>
</html>

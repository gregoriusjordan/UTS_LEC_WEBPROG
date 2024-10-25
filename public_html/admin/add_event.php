<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') { 
    header("Location: ../index"); 
    exit; 
}

$uploadError = '';
$allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'bmp', 'gif'];
$showPopup = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $max_participants = $_POST['max_participants'];
    $status = 'open';

    $imageName = '';
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $image = $_FILES['event_image'];
        $image_ext = pathinfo($image['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($image_ext), $allowed_extensions)) {
            $imageName = uniqid() . '.' . $image_ext;
            $imagePath = '../assets/images/' . $imageName;
            move_uploaded_file($image['tmp_name'], $imagePath);
        } else {
            $uploadError = 'Invalid file type for event image. Only jpg, jpeg, png, svg, webp, bmp, gif files are allowed.';
        }
    }

    $bannerName = '';
    if (isset($_FILES['event_banner']) && $_FILES['event_banner']['error'] == 0) {
        $banner = $_FILES['event_banner'];
        $banner_ext = pathinfo($banner['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($banner_ext), $allowed_extensions)) {
            $bannerName = uniqid() . '_banner.' . $banner_ext;
            $bannerPath = '../assets/images/' . $bannerName;
            move_uploaded_file($banner['tmp_name'], $bannerPath);
        } else {
            $uploadError = 'Invalid file type for event banner. Only jpg, jpeg, png, svg, webp, bmp, gif files are allowed.';
        }
    }

    if (empty($uploadError)) {
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, event_time, location, max_participants, status, image, banner) VALUES (:title, :description, :event_date, :event_time, :location, :max_participants, :status, :image, :banner)");
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'event_date' => $event_date,
            'event_time' => $event_time,
            'location' => $location,
            'max_participants' => $max_participants,
            'status' => $status,
            'image' => $imageName,
            'banner' => $bannerName
        ]);
        $showPopup = true;
    }
}
?>

<?php include '../includes/navbar.php'?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .blur {
            filter: blur(2px);
        }
    </style>
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
    <script>
        function updateFileName(inputId, placeholderId) {
            const fileInput = document.getElementById(inputId);
            const filePlaceholder = document.getElementById(placeholderId);
            filePlaceholder.value = fileInput.files.length > 0 ? fileInput.files[0].name : 'Choose File';
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8 max-w-2xl <?= $showPopup ? 'blur' : '' ?>">
        <div class="mt-2 text-center">
            <img src="../assets/images/design/add.png" alt="Event Image" class="mt-2 rounded-md max-w-full h-auto w-[400px] mx-auto mb-4">
        </div>
        <h1 class="text-3xl font-bold mb-6 text-center text-lilac">Make a New Event</h1>

        <?php if (!empty($uploadError)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <span><?= $uploadError ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Name:</label>
                <input type="text" name="title" required placeholder="Enter the name of your event" class="w-full px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Description:</label>
                <input type="text" name="description" required placeholder="Enter the description of your event" class="w-full px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Date & Time:</label>
                <input type="date" name="event_date" required class="mt-4 mb-4 w-full px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <input type="time" name="event_time" required class="w-full px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Location:</label>
                <input type="text" name="location" required placeholder="Enter the location of your event" class="w-full px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Capacity:</label>
                <input type="number" name="max_participants" required placeholder="Enter the maximum participants for your event" class="w-full px-4 py-2 border rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="mb-4 relative">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Image:</label>
                <input type="file" id="eventImageInput" name="event_image" accept=".jpg,.jpeg,.png,.svg,.webp,.bmp,.gif"
                    class="opacity-0 absolute inset-0 z-50 cursor-pointer w-full h-full"
                    onchange="updateFileName('eventImageInput', 'eventImagePlaceholder')">

                <input type="text" id="eventImagePlaceholder" placeholder="Choose File"
                    class="w-full text-gray-700 px-4 py-2 rounded-full bg-white cursor-pointer" readonly>

                <button type="button" class="absolute right-2 top-1/2 transform -translate-y bg-lilac text-white font-bold px-3 py-1 rounded-full">Browse</button>
            </div>

            <div class="mb-4 relative">
                <label class="block text-gray-700 font-bold mb-2 text-lilac">Event Banner:</label>
                <input type="file" id="eventBannerInput" name="event_banner" accept=".jpg,.jpeg,.png,.svg,.webp,.bmp,.gif"
                    class="opacity-0 absolute inset-0 z-50 cursor-pointer w-full h-full"
                    onchange="updateFileName('eventBannerInput', 'eventBannerPlaceholder')">

                <input type="text" id="eventBannerPlaceholder" placeholder="Choose File"
                    class="w-full text-gray-700 px-4 py-2 rounded-full bg-white cursor-pointer" readonly>

                <button type="button" class="absolute right-2 top-1/2 transform -translate-y bg-lilac text-white font-bold px-3 py-1 rounded-full">Browse</button>
            </div>

            <div class="text-right">
                <button type="submit" class="mt-6 bg-lilac text-white font-bold px-4 py-2 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full">
                    Create Event
                </button>
            </div>
        </form>
    </div>

    <?php if ($showPopup): ?>
        <div class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg text-center">
                <h2 class="text-2xl text-lilac font-bold mb-4">You Just Created a New Event!</h2>
                <div class="mt-4 text-center">
                    <img src="../assets/images/design/cancel_conf.png" alt="Event Image" class="mt-2 rounded-md max-w-full h-auto w-60 h-16 mx-auto mb-4">
                </div>
                <a href="add_event.php" class="bg-pinky text-white font-bold px-4 py-2 rounded-full mb-4 inline-block">
                    Confirm
                </a>
                <br>
                <a href="dashboard.php" class="bg-dlilac text-white font-bold px-4 py-2 rounded-full inline-block">
                    Go to Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?> 
</body> 
 
</html>

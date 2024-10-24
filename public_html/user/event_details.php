<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_GET['event_id'])) {
    header("Location: manage_events.php"); // Redirect if no event_id is provided
    exit;
}

$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT title, description, event_date, event_time, location, banner, image FROM events WHERE event_id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event tidak ditemukan."; // Event not found message
    exit;
}
?>

<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-[#F2ECF7]">

    <div class="container mx-auto my-10 p-5 bg-white rounded shadow-lg max-w-2xl">
        <img src="../assets/images/<?= htmlspecialchars($event['banner']) ?>" alt="<?= htmlspecialchars($event['title']) ?> Banner" class="rounded-lg mb-4 w-full">
        <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars($event['title']) ?></h1>
        <p class="text-gray-500 mb-2">Date: <?= htmlspecialchars($event['event_date']) ?>, Time: <?= htmlspecialchars($event['event_time']) ?></p>
        <p class="text-gray-500 mb-4">Location: <?= htmlspecialchars($event['location']) ?></p>
        <p class="mb-4"><?= nl2br(htmlspecialchars($event['description'])) ?></p>

        <div class="flex space-x-4">
            <button class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600" onclick="window.history.back()">Back</button>
            <button class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600" onclick="registerEvent(<?= $event_id ?>)">Register</button> 
        </div>
    </div>

    <script>
        function registerEvent(eventId) {
            const userId = <?= $_SESSION['user_id'] ?>;

            fetch('register_event.php', { 
                    method: 'POST', 
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `event_id=${eventId}&user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('You have successfully registered for the event!');
                        // Optionally update UI for participants count
                    } else {
                        alert(`Registration failed: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error registering for event:', error);
                    alert('There was an error registering for the event. Please try again.');
                });
        }
    </script>
</body>
</html>

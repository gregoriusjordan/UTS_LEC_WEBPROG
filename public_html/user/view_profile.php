<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view_events.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT e.title, e.event_date, e.event_time, e.location 
    FROM registrations er 
    JOIN events e ON er.event_id = e.event_id 
    WHERE er.id = :user_id 
    ORDER BY e.event_date DESC
");
$stmt->execute(['user_id' => $user_id]);
$events = $stmt->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>

<div class="container mx-auto p-6 mt-10 bg-white rounded-lg shadow-lg">
    <h1 class="text-3xl font-bold text-center text-indigo-600 mb-6">Your Profile</h1>

    <div class="profile-info mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Personal Information</h2>
        <p class="text-gray-700"><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
        <p class="text-gray-700"><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <a href="edit_profile.php" class="inline-block mt-4 bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">Edit Profile</a>
    </div>

    <div class="event-history">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Your Event History</h2>
        <?php if (count($events) > 0): ?>
            <ul class="list-disc list-inside">
                <?php foreach ($events as $event): ?>
                    <li class="mb-2">
                        <strong class="text-indigo-600"><?= htmlspecialchars($event['title']) ?></strong> 
                        - <?= htmlspecialchars($event['event_date']) ?>, <?= htmlspecialchars($event['event_time']) ?> 
                        at <?= htmlspecialchars($event['location']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-700">No event registrations found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Include Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

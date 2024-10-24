<?php
session_start();
require '../includes/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view_events.php"); 
    exit; 
} 

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT e.event_id, e.title, e.event_date, e.event_time, e.location, e.banner
    FROM registrations er 
    JOIN events e ON er.event_id = e.event_id 
    WHERE er.id = :user_id
    ORDER BY e.event_date DESC
");
$stmt->execute(['user_id' => $user_id]);
$events = $stmt->fetchAll();

if (isset($_POST['cancel_event_id'])) {
    $cancel_event_id = $_POST['cancel_event_id'];
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = :user_id AND event_id = :event_id");
    $stmt->execute(['user_id' => $user_id, 'event_id' => $cancel_event_id]);
    exit(json_encode(['success' => true]));
}
?>

<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Events - Eventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@700&family=Montserrat:wght@600&family=Rethink+Sans:wght@500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --dark-purple: #1E1B4B;
            --medium-purple: #8B5CF6;
            --light-purple: #C4B5FD;
            --pink: #FDF2F8;
        }

        body {
            background-color: var(--pink);
            font-family: 'Rethink Sans', sans-serif;
        }


        .logo-text {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
        }

        .nav-link {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .page-title {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            color: var(--medium-purple);
        }

        .event-card {
            width: 300px;
            height: 400px;
            background: white;
            border-radius: 1rem;
            box-shadow: 5px 7px 4px rgba(203, 152, 237, 1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .event-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .btn-details, .btn-cancel {
            background-color: var(--medium-purple);
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            padding: 0.5rem 2rem;
            border-radius: 0.5rem;
            transition: background-color 0.3s;
        }

        .dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .dialog-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            max-width: 28rem;
            width: 90%;
            text-align: center;
            animation: slideIn 0.3s ease-out;
        }

        .dialog-active {
            display: flex;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <h1 class="page-title text-3xl text-center mb-8">Registered Events</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
            <div class="event-card">
                <img src="../assets/images/event_banners/<?= htmlspecialchars($event['banner']) ?>" alt="<?= htmlspecialchars($event['title']) ?> Banner" 
                     class="event-image mb-4 ">
                <h3 class="font-montserrat font-semibold text-xl mb-4"><?= htmlspecialchars($event['title']) ?></h3>
                <div class="flex flex-col space-y-3">
                    <a href="get_event_details.php?id=<?= htmlspecialchars($event['event_id']) ?>" 
                       class="btn-details text-center">Details</a>
                    <button onclick="showCancelConfirmation(<?= htmlspecialchars($event['event_id']) ?>)"
                            class="btn-cancel w-full">
                        Cancel Registration
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($events) === 0): ?>
            <div class="text-center text-gray-600 mt-8">
                <p>No registered events found.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cancel Confirmation Dialog -->
    <div id="cancelConfirmation" class="dialog-overlay">
        <div class="dialog-content">
            <h2 class="text-xl font-semibold text-[#8B5CF6] mb-4">Are You Sure You Want to Cancel Registration?</h2>
            <div class="confirmation-image">
                <!-- Add an image for confirmation -->
            </div>
            <div class="flex flex-col gap-3">
                <button id="confirmCancel" class="button-primary">Confirm</button>
                <button onclick="hideCancelDialog()" class="button-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Success Dialog -->
    <div id="successDialog" class="dialog-overlay">
        <div class="dialog-content">
            <h2 class="text-xl font-semibold text-[#8B5CF6] mb-4">Registration Has Been Canceled!</h2>
            <div class="success-image">
                <!-- Add an image for success -->
            </div>
            <div class="flex flex-col gap-3">
                <button onclick="hideSuccessDialog()" class="button-primary">Confirm</button>
                <button onclick="goToDashboard()" class="button-secondary">Go to Dashboard</button>
            </div>
        </div>
    </div>

    <script>
        let currentEventId = null;

        function showCancelConfirmation(eventId) {
            currentEventId = eventId;
            document.getElementById('cancelConfirmation').classList.add('dialog-active');
        }

        function hideCancelDialog() {
            document.getElementById('cancelConfirmation').classList.remove('dialog-active');
        }

        function hideSuccessDialog() {
            document.getElementById('successDialog').classList.remove('dialog-active');
            window.location.reload();
        }

        function showSuccessDialog() {
            document.getElementById('cancelConfirmation').classList.remove('dialog-active');
            document.getElementById('successDialog').classList.add('dialog-active');
        }

        function goToDashboard() {
            window.location.href = 'dashboard.php';
        }

        document.getElementById('confirmCancel').addEventListener('click', async function() {
            if (!currentEventId) return;

            try {
                const response = await fetch('registered_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cancel_event_id=${currentEventId}`
                });

                const result = await response.json();
                
                if (result.success) {
                    showSuccessDialog();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body> 
</html>

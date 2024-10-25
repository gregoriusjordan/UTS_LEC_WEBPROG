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
                        slideIn: 'slideIn 0.5s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --dark-purple: #1E1B4B;
            --medium-purple: #8B5CF6;
            --light-purple: #C4B5FD;
            --pink: #FDF2F8;
        }

        .btn-details,
        .btn-cancel {
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

<body class="font-inter">

    <div class="mx-auto px-6 py-8">
        <?php if (count($events) === 0): ?>
            <h1 class="text-[38px] font-bold text-center my-8 text-lilac font-inter hidden">Registered Events</h1>
        <?php else: ?>
            <h1 class="text-[38px] font-bold text-center my-8 text-lilac font-inter">Registered Events</h1>
        <?php endif; ?>


        <div class="flex justify-center">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10 px-4 mb-8 w-full max-w-6xl">
                <?php foreach ($events as $event): ?>
                    <div class="bg-[#FFFFFF] rounded-[20px] me-20 p-4 text-center w-full font-inter" style="box-shadow: 5px 6px 4px rgba(203, 152, 237, 1);">
                        <img src="../assets/images/<?= htmlspecialchars($event['banner']) ?>" alt="<?= htmlspecialchars($event['title']) ?> Banner"
                            class="rounded-[8px] w-full h-[130px] object-cover mb-1">
                        <h2 class="mt-2 text-[20px] font-inter text-lilac font-bold title"><?= htmlspecialchars($event['title']) ?></h2>
                        <div class="space-y-2 mt-4">
                            <button onclick="showDetails(<?= $event['event_id'] ?>)"
                                class="w-[130px] bg-lilac text-white py-2 px-4 rounded-[10px] hover:bg-dlilac font-mont font-semibold">Details</>
                                <button onclick="showCancelConfirmation(<?= htmlspecialchars($event['event_id']) ?>)"
                                    class="w-[130px] bg-[#171950] text-white py-2 px-4 rounded-[10px] hover:bg-pinky font-mont font-semibold">Cancel</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (count($events) === 0): ?>
            <div class="flex flex-col items-center content-center">
                <img class="max-w-[350px]" src="../assets/images/design/no_event.png">
                <div class="text-center font-inter font-bold text-[32px] text-lilac mt-8">
                    <p>No Events Registered.</p>
                </div>
                <div class="text-center font-mont font-semibold text-gray-500">
                    <p>Start registering now!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="event-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-black backdrop-blur-sm bg-opacity-60 hidden">
        <div class="bg-white p-6 rounded-[20px] shadow-lg max-w-md w-full text-center animate-slideIn mx-4" style="box-shadow: 5px 6px 4px rgba(203, 152, 237, 1);">
            <img id="popup-image" src="" alt="Event Image" class="w-full h-[200px] mx-auto rounded-[8px] mb-4 object-cover">
            <h3 id="popup-title" class="text-[20px] font-inter text-lilac font-bold mb-2"></h3>
            <p id="popup-description" class="text-gray-700 mb-4"></p>
            <p id="popup-date" class="text-sm text-gray-500 mb-1"></p>
            <p id="popup-time" class="text-sm text-gray-500 mb-1"></p>
            <p id="popup-location" class="text-sm text-gray-500 mb-4"></p>

            <div class="space-y-2 flex flex-col items-center">
                <button class="w-[180px] bg-lilac text-white py-2 px-4 rounded-[10px] hover:bg-dlilac font-inter font-semibold mb-2" onclick="closePopup()">Close</button>
            </div>
        </div>
    </div>


    <div id="cancelConfirmation" class="fixed inset-0 bg-black backdrop-blur-sm bg-opacity-50 flex items-center justify-center hidden font-montserrat">
        <div class="bg-white rounded-[20px] shadow-lg p-6 sm:p-8 text-center max-w-[90%] sm:max-w-md mx-auto animate-slideIn">
            <h2 class="text-xl sm:text-2xl font-bold text-lilac mb-4">Are You Sure You Want to Cancel Registration?</h2>
            <img src="../assets/images/design/cancel.png" alt="Cancel Confirmation" class="mx-auto mb-4 w-[150px] sm:w-[200px]" />
            <div class="flex flex-col space-y-2 sm:space-y-0 justify-center sm:space-x-4">
                <div class="flex flex-col items-center mb-2 mt-3">
                    <button id="confirmCancel" class="w-[150px] bg-lilac text-white py-2 px-4 rounded-[32px] hover:bg-dlilac font-bold mb-2">
                        Confirm
                    </button>
                    <button onclick="hideCancelDialog()" class="w-[150px] sm:w-[150px] text-white py-2 px-4 rounded-[32px] font-bold bg-[#171950] text-white hover:bg-pinky">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div id="successDialog" class="fixed inset-0 bg-black backdrop-blur-sm bg-opacity-50 flex items-center justify-center hidden font-montserrat">
        <div class="bg-white rounded-[20px] shadow-lg p-6 sm:p-8 text-center max-w-[90%] sm:max-w-md mx-auto animate-slideIn">
            <h2 class="text-xl sm:text-2xl font-bold text-lilac mb-4">Registration Has Been Canceled!</h2>
            <img src="../assets/images/design/cancel_conf.png" alt="Cancel Confirmation" class="mx-auto mb-4 w-[150px] sm:w-[200px]" />
            <div class="flex flex-col space-y-2 sm:space-y-0 justify-center sm:space-x-4">
                <div class="flex flex-col items-center mb-2 mt-3">
                    <button onclick="hideSuccessDialog()" class="w-[150px] bg-lilac text-white py-2 px-4 rounded-[32px] hover:bg-dlilac font-bold mb-2">
                        Confirm
                    </button>
                    <button onclick="goToDashboard()" class="w-[180px] max-sm:w-[150px] text-white py-2 px-4 rounded-[32px] font-bold  bg-[#171950] text-white hover:bg-pinky">
                        Go to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        let currentEventId = null;

        function showDetails(eventId) {
            currentEventId = eventId;

            fetch(`get_event_details.php?event_id=${eventId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(event => {
                    const data = event.split('|');
                    const timeParts = data[4].split(':');
                    const hours = timeParts[0];
                    const minutes = timeParts[1];

                    // Updating the popup fields
                    document.getElementById('popup-title').innerText = data[0]; // Title
                    document.getElementById('popup-image').src = '../assets/images/' + data[1] + '?v=' + new Date().getTime(); // Image
                    document.getElementById('popup-description').innerText = data[2]; // Description
                    document.getElementById('popup-date').innerText = `Date: ${data[3]}`; // Date
                    document.getElementById('popup-time').innerText = `Time: ${hours}:${minutes}`; // Time
                    document.getElementById('popup-location').innerText = `Location: ${data[5]}`; // Location

                    document.getElementById('event-popup').classList.remove('hidden'); // Show the popup
                })
                .catch(error => {
                    console.error('Error fetching event details:', error);
                });
        }

        // Function to close the details popup
        function closePopup() {
            document.getElementById('event-popup').classList.add('hidden');
        }

        // Function to cancel the event registration
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

        // Function to confirm event cancellation
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

        // Close popup function for the event details
        function closePopup() {
            document.getElementById('event-popup').classList.add('hidden');
        }
    </script>

</body>

</html>
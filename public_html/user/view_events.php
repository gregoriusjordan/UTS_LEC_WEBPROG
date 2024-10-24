<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM events");
$events = $stmt->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/f62928dd38.js" crossorigin="anonymous"></script>
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
                        slideIn: 'slideIn 1s ease-out',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-[#F2ECF7]">

    <h1 class="text-[38px] font-bold text-center my-8 text-lilac font-inter font-bold">Available Events</h1>

    <div class="flex justify-center">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10 px-4 mb-8 w-full max-w-5xl">
            <?php foreach ($events as $event): ?>
                <div class="bg-[#FFFFFF] rounded-[22px] p-4 text-center w-full h-[280px] shadow-lg" style="box-shadow: 5px 6px 4px rgba(203, 152, 237, 1);">
                    <img src="../assets/images/<?= htmlspecialchars($event['banner']) ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($event['title']) ?> Banner" class="rounded-[8px] w-[240px] h-[130px] object-cover mb-2">
                    <h2 class="text-[20px] font-inter text-lilac font-bold mt-8"><?= htmlspecialchars($event['title']) ?></h2>
                    <button onclick="showDetails(<?= $event['event_id'] ?>)" class="mt-2 font-mont font-semibold bg-lilac text-white py-2 px-4 rounded-[10px] hover:bg-dlilac">Details</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Popup -->
    <div id="event-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full text-center">
            <img id="popup-image" src="" alt="Event Image" class="w-3/4 mx-auto rounded-lg mb-4">
            <h3 id="popup-title" class="text-xl font-semibold mb-2"></h3>
            <p id="popup-description" class="text-gray-700 mb-4"></p>
            <p id="popup-date-time" class="text-sm text-gray-500 mb-2"></p>
            <p id="popup-location" class="text-sm text-gray-500 mb-4"></p>

            <div class="space-y-2">
                <button class="w-full bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600" onclick="closePopup()">Close</button>
                <button class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600" onclick="registerEvent()">Register</button>
            </div>
        </div>
    </div>

    <script>
        let currentEventId;

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
                    document.getElementById('popup-title').innerText = data[0];
                    document.getElementById('popup-image').src = '../assets/images/' + data[1] + '?v=' + new Date().getTime();
                    document.getElementById('popup-description').innerText = data[2];
                    document.getElementById('popup-date-time').innerText = `Date: ${data[3]}, Time: ${data[4]}`;
                    document.getElementById('popup-location').innerText = `Location: ${data[5]}`;
                    document.getElementById('event-popup').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching event details:', error);
                });
        }

        function closePopup() {
            document.getElementById('event-popup').classList.add('hidden');
        }

        function registerEvent() {
            const userId = <?= $_SESSION['user_id'] ?>;

            fetch('register_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `event_id=${currentEventId}&user_id=${userId}`
                })
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        alert('You have successfully registered for the event!');
                        closePopup();
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
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
    <title>Eventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/f62928dd38.js" crossorigin="anonymous"></script>
    <style>
        .title::first-letter {
            text-transform: uppercase;
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
</head>

<body class="bg-[#F2ECF7]">

    <h1 class="text-[38px] font-bold text-center my-8 text-lilac font-inter font-bold">Available Events</h1>

    <div class="flex justify-center">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10 px-4 mb-8 w-full max-w-6xl">
            <?php foreach ($events as $event): ?>
                <div class="bg-[#FFFFFF] rounded-[20px] me-20 p-4 text-center w-full h- font-inter" style="box-shadow: 5px 6px 4px rgba(203, 152, 237, 1);">
                    <img src="../assets/images/<?= htmlspecialchars($event['banner']) ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($event['title']) ?> Banner" class="rounded-[8px] w-full h-[130px] object-cover mb-1">
                    <h2 class="leading-none mt-2 text-[20px] font-inter text-lilac font-bold title"><?= htmlspecialchars($event['title']) ?></h2>
                    <p class="text-pinky text-sm mt-1"><?= htmlspecialchars($event['event_date']) ?></p>
                    <p id="participants-count-<?= $event['event_id'] ?>" class="text-gray-700 text-sm mb-4">Participants: <?= $event['registered_participants'] ?>/<?= $event['max_participants'] ?></p>
                    <button onclick="showDetails(<?= $event['event_id'] ?>)" class="mt-2 font-mont font-semibold bg-lilac text-white py-2 px-4 rounded-[10px] hover:bg-dlilac">Details</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <div id="event-popup" class="fixed inset-0 z-50 flex items-center justify-center bg-black backdrop-blur-sm bg-opacity-60 hidden">
        <div class="bg-[#FFFFFF] p-6 rounded-[20px] shadow-lg max-w-md w-full text-center animate-slideIn mx-4" style="box-shadow: 5px 6px 4px rgba(203, 152, 237, 1);">
            <img id="popup-image" src="" alt="Event Image" class="w-full h-[180px] mx-auto rounded-[8px] mb-4 object-cover">
            <h2 id="popup-title" class="text-[24px] font-inter text-lilac font-bold mb-2 leading-tight"></h2>
            <p id="popup-description" class="text-gray-700 mb-4"></p>
            <p id="popup-date" class="text-sm text-gray-500 mb-1"></p>
            <p id="popup-time" class="text-sm text-gray-500 mb-1"></p>
            <p id="popup-location" class="text-sm text-gray-500 mb-4"></p>

            <div class="space-y-1 flex flex-col items-center">
                <button class="w-[180px] bg-pinky text-white py-2 px-4 rounded-[10px] font-inter font-semibold mb-1" onclick="registerEvent()">Register</button>
                <button class="w-[180px] bg-lilac text-white py-2 px-4 rounded-[10px] hover:bg-dlilac font-inter font-semibold mb-2" onclick="closePopup()">Close</button>
            </div>
        </div>
    </div>


    <div id="success-popup" class="fixed inset-0 bg-black backdrop-blur-sm bg-opacity-50 flex items-center justify-center hidden font-montserrat">
        <div class="bg-white rounded-[20px] shadow-lg p-6 sm:p-8 text-center max-w-[90%] sm:max-w-md mx-auto animate-slideIn">
            <h2 class="text-xl sm:text-2xl font-bold text-lilac mb-4">Registration Successful!</h2>
            <img src="../assets/images/design/success.png" alt="Registration Success" class="mx-auto mb-4 w-[150px] sm:w-[200px]" />
            <div class="flex flex-col space-y-2 sm:space-y-0 justify-center sm:space-x-4">
                <div class="flex flex-col items-center mb-2 mt-3">
                    <button onclick="hideSuccessPopup()" class="w-[150px] bg-lilac text-white py-2 px-4 rounded-[32px] hover:bg-dlilac font-bold mb-2 max-sm:mb-0">
                        Confirm
                    </button>
                </div>
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
                    const timeParts = data[4].split(':');
                    const hours = timeParts[0];
                    const minutes = timeParts[1];

                    document.getElementById('popup-title').innerText = data[0];
                    document.getElementById('popup-image').src = '../assets/images/' + data[1] + '?v=' + new Date().getTime();
                    document.getElementById('popup-description').innerText = data[2];
                    document.getElementById('popup-date').innerText = `Date: ${data[3]}`;
                    document.getElementById('popup-time').innerText = `Time: ${hours}:${minutes}`;
                    document.getElementById('popup-location').innerText = `Location: ${data[5]}`;

                    const registrationStatus = data[6];
                    const registerButton = document.querySelector("#event-popup button[onclick^='registerEvent']");

                    if (registrationStatus === 'registered') {
                        registerButton.innerText = 'Registered';
                        registerButton.classList.add('bg-gray-400', 'cursor-not-allowed');
                        registerButton.disabled = true;
                    } else {
                        registerButton.innerText = 'Register';
                        registerButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        registerButton.disabled = false;
                    }

                    document.getElementById('event-popup').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching event details:', error);
                });
        }


        function closePopup() {
            document.getElementById('event-popup').classList.add('hidden');
        }


        function showSuccessPopup() {
            document.getElementById('success-popup').classList.remove('hidden');
        }


        function hideSuccessPopup() {
            document.getElementById('success-popup').classList.add('hidden');
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


                        const countElement = document.getElementById(`participants-count-${currentEventId}`);
                        const currentCount = parseInt(countElement.innerText.split('/')[0].replace('Participants: ', ''));
                        const maxCount = parseInt(countElement.innerText.split('/')[1]);
                        countElement.innerText = `Participants: ${currentCount + 1}/${maxCount}`;
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
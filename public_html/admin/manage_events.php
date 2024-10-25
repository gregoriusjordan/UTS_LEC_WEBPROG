<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php"); 
    exit; 
} 

$stmt = $pdo->query("SELECT e.*, (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id) AS total_registrations FROM events e");
$events = $stmt->fetchAll();
?>

<?php include '../includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Eventory</title>
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
                        slideIn: 'slideIn 0.4s ease-out',
                    }
                }
            }
        }
    </script>
    <style>
        .title::first-letter {
            text-transform: uppercase;
        }
    </style>
</head>

<body class="bg-[#F2ECF7]">

    <h1 class="text-[38px] font-bold text-center my-8 text-lilac font-inter font-bold">Manage Events</h1>

    <div class="flex justify-center">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10 px-4 mb-8 w-full max-w-6xl">
            <?php foreach ($events as $event): ?>
                <div class="bg-[#FFFFFF] rounded-[20px] me-20 p-4 text-center w-full h- font-inter" style="box-shadow: 5px 6px 4px rgba(203, 152, 237, 1);">
                    <img src="../assets/images/<?= htmlspecialchars($event['banner']) ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($event['title']) ?> Banner" class="rounded-[8px] w-full h-[130px] object-cover mb-1">
                    <h2 class="leading-none mt-3 text-[20px] font-inter text-lilac font-bold title"><?= htmlspecialchars($event['title']) ?></h2>
                    <div class="space-x-4 mt-1 flex justify-center items-center">
                        <button onclick="editEvent(<?= $event['event_id'] ?>)" class="mt-2 font-mont font-semibold bg-lilac text-white py-2 px-4 rounded-[10px] hover:bg-dlilac">Edit Event</button>
                        <button onclick="showDeletePopup(<?= $event['event_id'] ?>)"><i class="fa-solid fa-trash text-lilac text-[28px] mt-2"></i></button>
                    </div>
                    <div class="flex justify-center">
                        <button onclick="viewRegistrations(<?= $event['event_id'] ?>)" class="w-full mx-6 mt-2 font-mont font-semibold bg-pinky text-white py-2 px-4 rounded-[10px] hover:bg-lilac">View List</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="delete-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-10 rounded-lg text-center w-[450px]">
            <h2 class="text-[24px] font-inter font-bold text-lilac mb-6">
                Are You Sure You Want to<br>
                Delete This Event?
            </h2>
            <div class="flex justify-center mb-4">
                <img src="../assets/images/design/cancel.png" alt="Popup Image" class="w-48 h-auto mb-4" />
            </div>
            <div class="flex flex-col items-center">
                <button id="confirm-delete-btn" class="bg-[#8B63DA] text-white px-10 py-2 font-bold rounded-full mb-2 font-montserrat" onclick="confirmDeleteEvent()">
                    Confirm
                </button>
                <button class="bg-[#171950] text-white px-11 py-2 rounded-full font-montserrat font-bold" onclick="closeDeletePopup()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="success-popup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-10 rounded-lg text-center w-[450px]">
            <h2 class="text-[24px] font-inter font-bold text-lilac mb-6">
                This Event Has Been Successfully Deleted!
            </h2>
            <div class="flex justify-center mb-4">
                <img src="../assets/images/design/cancel_conf.png" alt="Cancel" class="w-48 h-auto mb-4" />
            </div>
            <button class="bg-[#8B63DA] text-white px-10 py-2 rounded-full mb-2 font-montserrat font-bold" onclick="closeSuccessPopup()">Confirm</button>
        </div>
    </div>

    <script>
        let eventIdToDelete;

        function showDeletePopup(eventId) {
            eventIdToDelete = eventId;
            document.getElementById('delete-popup').classList.remove('hidden');
        }

        function closeDeletePopup() {
            document.getElementById('delete-popup').classList.add('hidden');
        }

        function closeSuccessPopup() {
            document.getElementById('success-popup').classList.add('hidden');
            location.reload();
        }

        function confirmDeleteEvent() {
            if (!eventIdToDelete) return alert("No event selected for deletion.");
            
            fetch(`delete_event.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `event_id=${eventIdToDelete}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeletePopup();
                    document.getElementById('success-popup').classList.remove('hidden');
                } else {
                    alert('Failed to delete event. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error deleting event:', error);
                alert('An error occurred while trying to delete the event.');
            });
        }
    </script>

</body>
</html>

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

<div class="container mx-auto p-6 mt-10 bg-white rounded-md">
    <h1 class="text-2xl font-bold text-center mb-6 text-indigo-600">Manage Events</h1>

    <div class="flex flex-wrap justify-center gap-4">
        <?php foreach ($events as $event): ?>
            <div class="event-banner border border-gray-300 rounded-lg shadow-md p-4 w-48 flex flex-col items-center">
                <img src="../assets/images/<?= $event['banner'] ?>?v=<?= time() ?>" alt="<?= $event['title'] ?> Banner" class="w-full h-32 object-cover rounded-lg mb-2">
                <h2 class="font-semibold text-lg text-center"><?= htmlspecialchars($event['title']) ?></h2>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($event['total_registrations']) ?> registrants</p>
                <button onclick="showDetails(<?= $event['event_id'] ?>)" class="mt-2 bg-blue-600 text-white py-1 px-3 rounded-md hover:bg-blue-700 transition duration-200">
                    Details
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="event-popup" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-75 hidden">
    <div class="popup-content bg-white p-5 rounded-lg items-center shadow-lg max-w-sm w-full text-center">
        <img id="popup-image" src="" alt="Event Image" class="items-center popup-image h-auto rounded-lg mb-4">
        <h3 id="popup-title" class="text-xl font-semibold mb-2"></h3>
        <p id="popup-description" class="mb-2"></p>
        <p id="popup-date-time" class="mb-2"></p>
        <p id="popup-location" class="mb-2"></p>
        <p id="popup-registrations" class="mb-4"></p>

        <div class="popup-buttons flex flex-col space-y-2">
            <button onclick="editEvent()" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">Edit Event</button>
            <button onclick="confirmDeleteEvent()" class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200">Delete Event</button>
            <button class="close-btn bg-gray-400 text-white py-2 px-4 rounded-md hover:bg-gray-500 transition duration-200" onclick="closePopup()">Close</button>
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
                document.getElementById('popup-registrations').innerText = `${data[6]} people registered`;
                document.getElementById('event-popup').classList.remove('hidden'); // Show popup
            })
            .catch(error => {
                console.error('Error fetching event details:', error);
            });
    }

    function closePopup() {
        document.getElementById('event-popup').classList.add('hidden'); // Hide popup
    }

    function editEvent() {
        window.location.href = `edit_event.php?event_id=${currentEventId}`;
    }

    function confirmDeleteEvent() {
        const confirmDelete = confirm('Are you sure you want to delete this event? This action cannot be undone.');
        if (confirmDelete) {
            deleteEvent();
        }
    }

    function deleteEvent() {
        fetch(`delete_event.php?event_id=${currentEventId}`, {
            method: 'POST',
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to delete event');
            }
            return response.text();
        })
        .then(result => {
            alert('Event deleted successfully!');
            location.reload();  
        })
        .catch(error => {
            console.error('Error deleting event:', error);
            alert('An error occurred while trying to delete the event.');
        });
    }
</script>

<!-- Include Tailwind CSS CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

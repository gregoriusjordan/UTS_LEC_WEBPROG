<?php
session_start();
require '../includes/db_connection.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch all users
$stmt = $pdo->query("SELECT id, name FROM users");
$users = $stmt->fetchAll();

?>

<?php include '../includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>User Management</title>
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@700&family=Montserrat:wght@600&display=swap');

        @font-face {
            font-family: 'RethinkSans';
            src: url('path/to/rethink-sans-medium.woff2') format('woff2');
            font-weight: 500;
            font-style: normal;
        }

        .font-inter {
            font-family: 'Inter', sans-serif;
        }

        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .font-rethink {
            font-family: 'RethinkSans', sans-serif;
        }
    </style>
</head>

<body class="bg-lilac min-h-screen">
    <div class="w-full flex flex-col items-center justify-center mb-8">
        <h1 class="text-center text-[28px] font-inter font-bold text-[#8B63DA] mb-8 mt-10">User Management</h1>
        <div class="w-full max-w-4xl px-10"> 
            <table class="table-auto p-6 w-full bg-white rounded-[22px] overflow-hidden shadow-md mx-auto"> 
                <thead class=""> 
                    <tr class="bg-[#8B63DA]"> 
                        <th class="ps-10 py-3 text-left text-white font-rethink">Username</th>
                        <th class="ps-16 py-3 text-left text-white font-rethink">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b border-gray-300 p-4 ">
                            <td class="ps-10 py-3 text-black font-rethink">
                                <p class="font-inter">
                                    <?= htmlspecialchars($user['name']) ?>
                                </p>
                            </td>

                            <td class="px-4 py-3">
                                <button class="ms-14" onclick="showPopup(<?= $user['id'] ?>)">
                                    <i class="fa-solid fa-trash text-lilac text-[28px] mt-2"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-center text-gray-500">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table> 
        </div>
    </div>
</body>


    <!-- Popup delete confirmation -->
    <div id="popup" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-10 rounded-lg text-center w-[450px]">
            <h2 class="text-[24px] font-inter font-bold text-lilac mb-6">
                Are You Sure You Want to<br>
                Delete This User's Account?
            </h2>
            <div class="flex justify-center mb-4">
                <img src="../assets/images/design/cancel.png"" alt="Popup Image" class="w-48 h-auto mb-4" />
            </div>
            <div class="flex flex-col items-center">
                <button id="confirm-delete-btn" class="bg-[#8B63DA] text-white px-10 py-2 rounded-full mb-2 font-montserrat" onclick="confirmDelete()">
                    Confirm
                </button>
                <button class="bg-[#171950] text-white px-11 py-2 rounded-full font-montserrat" onclick="hidePopup()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Popup delete succes -->
    <div id="popup2" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-10 rounded-lg text-center w-[450px]">
            <h2 class="text-xl font-inter text-purple-600 mb-6">
                This User Has Been Successfully Deleted!
            </h2>
            <div class="flex justify-center mb-4">
                <img src="../assets/images/design/cancel_conf.png" alt="Cancel" class="w-48 h-auto mb-4" />
            </div>
            <div class="flex flex-col items-center">
                <button class="bg-[#8B63DA] text-white px-10 py-2 rounded-full mb-2 font-montserrat" onclick="closePopup2()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let userIdToDelete = null;

        function showPopup(id) {
            userIdToDelete = id; // Store user ID for deletion
            document.getElementById('popup').classList.remove('hidden');
        }

        function hidePopup() {
            document.getElementById('popup').classList.add('hidden');
        }

        function confirmDelete() {
            if (userIdToDelete) {
                fetch(`delete_user.php?id=${userIdToDelete}`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            hidePopup();
                            document.getElementById('popup2').classList.remove('hidden');
                        }
                    });
            }
        }

        function closePopup2() {
            document.getElementById('popup2').classList.add('hidden');
            window.location.reload();
        }
    </script>
</body>

</html>
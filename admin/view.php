<?php
require('../login/connect.php');

$id = $_GET['id']; // Catch the ID from URL
$sql = "SELECT * FROM media_hub_user WHERE id = $id";
$result = $dbconnection->query($sql);
$user = $result->fetch_assoc();
?>
<script src="https://cdn.tailwindcss.com"></script>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-4">User Profile</h2>
        <div class="space-y-4 text-gray-700">
            <p><strong>Name:</strong> <?php echo $user['username']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            <p><strong>Joined:</strong> <?php echo $user['created_at']; ?></p>
        </div>
        <a href="admin.php" class="block mt-6 text-center bg-indigo-600 text-white py-2 rounded-lg">Back to Dashboard</a>
    </div>
</body>
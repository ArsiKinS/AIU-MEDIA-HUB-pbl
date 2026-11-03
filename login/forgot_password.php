<?php
session_start();
require('connect.php'); // Ensure this points to your DB connection file

$message = "";
$status_color = "text-red-500";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // 1. Fetch user from your table (media_hub_user)
    $stmt = $dbconnection->prepare("SELECT * FROM media_hub_user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        // 2. Verify if the "Old Password" provided matches the database
        if (password_verify($old_password, $user['password'])) {
            
            // 3. Similarity Check: Compare New Password with Email
            similar_text($new_password, $email, $percent_email);
            $match_email = (strlen($new_password) * $percent_email) / 100;

            // 4. Similarity Check: Compare New Password with Old Password
            similar_text($new_password, $old_password, $percent_old);
            $match_old = (strlen($new_password) * $percent_old) / 100;

            if ($match_email >= 5) {
                $message = "Error: New password is too similar to your email (5+ chars match).";
            } elseif ($match_old >= 5) {
                $message = "Error: New password is too similar to your old password.";
            } else {
                // SUCCESS: Hash the new password and update
                $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $dbconnection->prepare("UPDATE media_hub_user SET password = ? WHERE email = ?");
                $update->bind_param("ss", $hashed_new, $email);
                
                if ($update->execute()) {
                    $message = "Success! Password updated. You can now login.";
                    $status_color = "text-green-600";
                }
            }
        } else {
            $message = "Error: Current password does not match.";
        }
    } else {
        $message = "Error: Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Reset Password - AIU Media Hub</title>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center font-sans">
    <div class="fixed inset-0 -z-10 bg-[url('bg.jpg')] bg-cover bg-center blur-sm scale-105"></div>

    <div class="w-[400px] bg-white p-8 rounded-2xl shadow-xl">
        <h2 class="text-3xl font-bold text-[#0d1849] mb-2 text-center">Update Password</h2>
        <p class="text-sm text-gray-500 mb-6 text-center text-balance">Verify your identity to set a new password.</p>

        <?php if ($message): ?>
            <p class="text-xs font-bold text-center mb-4 <?php echo $status_color; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="Enter your email"
                       class="w-full px-4 py-3 border rounded-lg outline-none focus:ring-2 focus:ring-[#0c9e95]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Current Password</label>
                <input type="password" name="old_password" required placeholder="Enter old password"
                       class="w-full px-4 py-3 border rounded-lg outline-none focus:ring-2 focus:ring-[#0c9e95]">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">New Password</label>
                <input type="password" name="new_password" required placeholder="Enter new password"
                       class="w-full px-4 py-3 border rounded-lg outline-none focus:ring-2 focus:ring-[#0c9e95]">
                <p class="text-[10px] text-gray-400 mt-1 italic">Note: Cannot share 5+ characters with email or old password.</p>
            </div>

            <button class="w-full bg-[#0c9e95] hover:bg-[#0c615c] text-white font-bold py-3 rounded-xl transition-all">
                Change Password
            </button>

            <div class="text-center mt-4">
                <a href="login2.php" class="text-sm text-gray-400 hover:text-[#0c9e95] hover:underline">Back to Login</a>
            </div>
        </form>
    </div>

</body>
</html>
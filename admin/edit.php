<?php
require('../login/connect.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get user details
$user = null;
if ($id > 0) {
    $sql = "SELECT * FROM media_hub_user WHERE id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if (!$user) {
    die("User not found!");
}

// Handle form update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($dbconnection, $_POST['username']);
    $email = mysqli_real_escape_string($dbconnection, $_POST['email']);
    $user_type = $user['user_type']; // Keep original user type
    $location = mysqli_real_escape_string($dbconnection, $_POST['location']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists (excluding current user)
        $check_sql = "SELECT * FROM media_hub_user WHERE email = ? AND id != ?";
        $check_stmt = $dbconnection->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Email already exists for another user!";
        } else {
            // Update user
            $update_sql = "UPDATE media_hub_user SET 
                          username = ?, 
                          email = ?, 
                          location = ? 
                          WHERE id = ?";
            
            $stmt = $dbconnection->prepare($update_sql);
            $stmt->bind_param("sssi", $username, $email, $location, $id);
            
            if ($stmt->execute()) {
                header("Location: admin.php?success=User updated successfully!");
                exit();
            } else {
                $error = "Error updating user: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-2xl mx-auto">
        <!-- Back Button -->
        <a href="admin.php" class="inline-block mb-6 text-blue-600 hover:text-blue-800">
            ← Back to Dashboard
        </a>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- Edit Form -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-bold mb-6 text-gray-800">
                Edit User #<?php echo $user['id']; ?>
            </h2>
            
            <form method="POST" class="space-y-4">
                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" 
                           name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- User Type (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                    <div class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50 text-gray-700">
                        <?php echo htmlspecialchars($user['user_type']); ?>
                    </div>
                    <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user['user_type']); ?>">
                    <p class="mt-1 text-xs text-gray-500">User type cannot be changed</p>
                </div>
                
                <!-- Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" 
                           name="location" 
                           value="<?php echo htmlspecialchars($user['location']); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter location">
                </div>
                
                <!-- Account Info (Read-only) -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Account Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">User ID</p>
                            <p class="font-medium">#<?php echo $user['id']; ?></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Created</p>
                            <p class="font-medium"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-between items-center pt-6 border-t">
                    <a href="admin.php" 
                       class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Danger Zone -->
        <div class="bg-white p-6 rounded-xl shadow mt-6 border border-red-200">
            <h3 class="text-lg font-bold text-red-700 mb-4">Danger Zone</h3>
            <a href="admin.php?delete_id=<?php echo $id; ?>" 
               onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
               class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-medium inline-block">
                Delete User
            </a>
            <p class="text-sm text-gray-500 mt-2">Once deleted, this user cannot be recovered.</p>
        </div>
    </div>
</body>
</html>
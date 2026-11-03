<?php
require('../login/connect.php');

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $name = mysqli_real_escape_string($dbconnection, $_POST['name']);
    $email = mysqli_real_escape_string($dbconnection, $_POST['email']);
    $department = mysqli_real_escape_string($dbconnection, $_POST['department']);
    $position = mysqli_real_escape_string($dbconnection, $_POST['position']);
    $country = mysqli_real_escape_string($dbconnection, $_POST['country']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM staff WHERE email = ?";
        $check_stmt = $dbconnection->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Email already exists!";
        } else {
            try {
                // Insert new staff
                $sql = "INSERT INTO staff (name, email, department, position, country) 
                        VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $dbconnection->prepare($sql);
                $stmt->bind_param("sssss", $name, $email, $department, $position, $country);
                
                if ($stmt->execute()) {
                    $new_staff_id = $stmt->insert_id;
                    $success_message = "Staff member added successfully! Staff ID: #$new_staff_id";
                    
                    // Clear form
                    $_POST = array();
                } else {
                    $error_message = "Error adding staff: " . $stmt->error;
                }
                
            } catch (mysqli_sql_exception $e) {
                $error_message = "Database error: " . $e->getMessage();
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
    <title>Add Staff | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#0c9e95]  text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <p class="text-indigo-200 text-sm">Staff Management</p>
            </div>
            
            <nav class="p-4 space-y-2">
                <a href="admin.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                </a>
                <a href="view_content.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-newspaper mr-3"></i>Content
                </a>
                <a href="staff_activity.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-users mr-3"></i>Staff Activity
                </a>
                <a href="user_info_view.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-chart-bar mr-3"></i>User Info
                </a>
                <a href="add_staff.php" class="flex items-center p-3 rounded bg-white text-[#64b12b]">
                    <i class="fas fa-user-plus mr-3"></i>Add Staff
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-cog mr-3"></i>Settings
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            <div class="max-w-4xl mx-auto">
                
                <!-- Header -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-user-plus mr-2"></i>Add New Staff
                            </h2>
                            <p class="text-gray-600">Add new staff members to the system</p>
                        </div>
                        <a href="staff_activity.php" 
                           class="px-6 py-2 bg-gray-600 text-white rounded-lg font-bold hover:bg-gray-700">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Staff
                        </a>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if ($success_message): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-green-700 font-semibold"><?php echo $success_message; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if ($error_message): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-red-700 font-semibold"><?php echo $error_message; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Add Staff Form -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-user-circle mr-2"></i>Staff Information
                        </h3>
                    </div>
                    
                    <form method="POST" action="add_staff.php" class="p-6">
                        <div class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2"></i>Full Name
                                </label>
                                <input type="text" 
                                       name="name" 
                                       required
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="Enter full name">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2"></i>Email Address
                                </label>
                                <input type="email" 
                                       name="email" 
                                       required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="Enter email address">
                                <p class="mt-1 text-sm text-gray-500">Use a valid email address</p>
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building mr-2"></i>Department
                                </label>
                                <select name="department" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select Department</option>
                                    <option value="Administration" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                                    <option value="IT" <?php echo (isset($_POST['department']) && $_POST['department'] == 'IT') ? 'selected' : ''; ?>>IT Department</option>
                                    <option value="HR" <?php echo (isset($_POST['department']) && $_POST['department'] == 'HR') ? 'selected' : ''; ?>>Human Resources</option>
                                    <option value="Finance" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                                    <option value="Marketing" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                    <option value="Academics" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Academics') ? 'selected' : ''; ?>>Academics</option>
                                    <option value="Student Affairs" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Student Affairs') ? 'selected' : ''; ?>>Student Affairs</option>
                                    <option value="Library" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Library') ? 'selected' : ''; ?>>Library</option>
                                    <option value="Maintenance" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Other" <?php echo (isset($_POST['department']) && $_POST['department'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <!-- Position -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-briefcase mr-2"></i>Position
                                </label>
                                <select name="position" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select Position</option>
                                    <option value="Manager" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                    <option value="Supervisor" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Supervisor') ? 'selected' : ''; ?>>Supervisor</option>
                                    <option value="Coordinator" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Coordinator') ? 'selected' : ''; ?>>Coordinator</option>
                                    <option value="Officer" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Officer') ? 'selected' : ''; ?>>Officer</option>
                                    <option value="Assistant" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Assistant') ? 'selected' : ''; ?>>Assistant</option>
                                    <option value="Specialist" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Specialist') ? 'selected' : ''; ?>>Specialist</option>
                                    <option value="Analyst" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Analyst') ? 'selected' : ''; ?>>Analyst</option>
                                    <option value="Technician" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Technician') ? 'selected' : ''; ?>>Technician</option>
                                    <option value="Lecturer" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Lecturer') ? 'selected' : ''; ?>>Lecturer</option>
                                    <option value="Other" <?php echo (isset($_POST['position']) && $_POST['position'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <!-- Country -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-globe mr-2"></i>Country
                                </label>
                                <select name="country" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <option value="">Select Country</option>
                                    <option value="Ethiopia" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Ethiopia') ? 'selected' : ''; ?>>Ethiopia</option>
                                    <option value="Kenya" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                                    <option value="Uganda" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Uganda') ? 'selected' : ''; ?>>Uganda</option>
                                    <option value="Tanzania" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Tanzania') ? 'selected' : ''; ?>>Tanzania</option>
                                    <option value="Nigeria" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Nigeria') ? 'selected' : ''; ?>>Nigeria</option>
                                    <option value="Ghana" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Ghana') ? 'selected' : ''; ?>>Ghana</option>
                                    <option value="South Africa" <?php echo (isset($_POST['country']) && $_POST['country'] == 'South Africa') ? 'selected' : ''; ?>>South Africa</option>
                                    <option value="Egypt" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Egypt') ? 'selected' : ''; ?>>Egypt</option>
                                    <option value="Other" <?php echo (isset($_POST['country']) && $_POST['country'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <!-- Form Actions -->
                            <div class="pt-6 border-t">
                                <div class="flex justify-end space-x-4">
                                    <a href="staff_activity.php" 
                                       class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </a>
                                    <button type="submit" 
                                            class="px-6 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition">
                                        <i class="fas fa-save mr-2"></i>Add Staff Member
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Removed: Recent Staff Members section -->

            </div>
        </main>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                    
                    // Add error message if not exists
                    if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('error-message')) {
                        const errorMsg = document.createElement('p');
                        errorMsg.className = 'error-message text-red-500 text-sm mt-1';
                        errorMsg.textContent = 'This field is required';
                        input.parentNode.appendChild(errorMsg);
                    }
                } else {
                    input.classList.remove('border-red-500');
                    
                    // Remove error message if exists
                    const errorMsg = input.parentNode.querySelector('.error-message');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Clear error styles on input
        document.querySelectorAll('input, select').forEach(element => {
            element.addEventListener('input', function() {
                this.classList.remove('border-red-500');
                const errorMsg = this.parentNode.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });
        });
    </script>

</body>
</html>
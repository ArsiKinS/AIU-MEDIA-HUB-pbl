<?php
require('../login/connect.php');

$success_message = '';
$error_message = '';

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Check if staff exists
    $check_sql = "SELECT * FROM staff WHERE staff_id = ?";
    $check_stmt = $dbconnection->prepare($check_sql);
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $staff_data = $check_result->fetch_assoc();
        $staff_name = $staff_data['name'];
        
        // Delete staff
        $delete_sql = "DELETE FROM staff WHERE staff_id = ?";
        $delete_stmt = $dbconnection->prepare($delete_sql);
        $delete_stmt->bind_param("i", $delete_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Staff member '$staff_name' deleted successfully!";
        } else {
            $error_message = "Error deleting staff: " . $delete_stmt->error;
        }
    } else {
        $error_message = "Staff member not found!";
    }
}

// Get all staff
$sql = "SELECT * FROM staff ORDER BY staff_id DESC";
$result = $dbconnection->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#0c9e95] text-white">
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
                <a href="add_staff.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-user-plus mr-3"></i>Add Staff
                </a>
                <a href="edit_staff.php" class="flex items-center p-3 rounded bg-white text-[#64b12b]">
                    <i class="fas fa-user-edit mr-3"></i>Edit Staff
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    <i class="fas fa-cog mr-3"></i>Settings
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                
                <!-- Header -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-user-edit mr-2"></i>Edit Staff Members
                            </h2>
                            <p class="text-gray-600">View, edit, or delete staff members</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="staff_activity.php" 
                               class="px-4 py-2 bg-gray-600 text-white rounded-lg font-bold hover:bg-gray-700">
                                <i class="fas fa-arrow-left mr-2"></i>Back
                            </a>
                            <a href="add_staff.php" 
                               class="px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                                <i class="fas fa-user-plus mr-2"></i>Add New
                            </a>
                        </div>
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

                <!-- Staff Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-users mr-2"></i>All Staff Members
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Total: <?php echo $result->num_rows; ?> staff members</p>
                    </div>
                    
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Info</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($staff = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                #<?php echo $staff['staff_id']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <i class="fas fa-user text-indigo-600"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($staff['name']); ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        <?php echo htmlspecialchars($staff['email']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                $dept_colors = [
                                                    'IT' => 'bg-blue-100 text-blue-800',
                                                    'HR' => 'bg-purple-100 text-purple-800',
                                                    'Finance' => 'bg-green-100 text-green-800',
                                                    'Marketing' => 'bg-pink-100 text-pink-800',
                                                    'Administration' => 'bg-yellow-100 text-yellow-800',
                                                    'Academics' => 'bg-indigo-100 text-indigo-800',
                                                    'Student Affairs' => 'bg-red-100 text-red-800',
                                                    'Library' => 'bg-teal-100 text-teal-800',
                                                    'Maintenance' => 'bg-gray-100 text-gray-800'
                                                ];
                                                echo $dept_colors[$staff['department']] ?? 'bg-gray-100 text-gray-800';
                                                ?>">
                                                <?php echo htmlspecialchars($staff['department']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo htmlspecialchars($staff['position']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                <?php echo htmlspecialchars($staff['country']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <!-- Edit Button -->
                                                <button onclick="editStaff(<?php echo $staff['staff_id']; ?>, '<?php echo htmlspecialchars(addslashes($staff['name'])); ?>', '<?php echo htmlspecialchars(addslashes($staff['email'])); ?>', '<?php echo htmlspecialchars(addslashes($staff['department'])); ?>', '<?php echo htmlspecialchars(addslashes($staff['position'])); ?>', '<?php echo htmlspecialchars(addslashes($staff['country'])); ?>')"
                                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </button>
                                                
                                                <!-- Delete Button -->
                                                <a href="edit_staff.php?delete_id=<?php echo $staff['staff_id']; ?>" 
                                                   onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars(addslashes($staff['name'])); ?>? This action cannot be undone.')"
                                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Summary -->
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-gray-600">
                                    Showing <?php echo $result->num_rows; ?> staff members
                                </p>
                                <a href="add_staff.php" 
                                   class="text-sm bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    <i class="fas fa-plus mr-1"></i> Add New Staff
                                </a>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <div class="p-8 text-center">
                            <div class="text-gray-400 text-6xl mb-4">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Staff Members Found</h3>
                            <p class="text-gray-500 mb-6">You haven't added any staff members yet.</p>
                            <a href="add_staff.php" 
                               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                                <i class="fas fa-user-plus mr-2"></i> Add Your First Staff
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Edit Modal (Hidden by default) -->
                <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                        <div class="flex justify-between items-center pb-3 border-b">
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="fas fa-user-edit mr-2"></i>Edit Staff Member
                            </h3>
                            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-2xl"></i>
                            </button>
                        </div>
                        
                        <form id="editForm" method="POST" action="edit_staff_save.php" class="mt-4 space-y-4">
                            <input type="hidden" id="edit_staff_id" name="staff_id">
                            
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="edit_name" name="name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="edit_email" name="email" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <!-- Department & Position -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                    <select id="edit_department" name="department" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Department</option>
                                        <option value="Administration">Administration</option>
                                        <option value="IT">IT Department</option>
                                        <option value="HR">Human Resources</option>
                                        <option value="Finance">Finance</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Academics">Academics</option>
                                        <option value="Student Affairs">Student Affairs</option>
                                        <option value="Library">Library</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                                    <select id="edit_position" name="position" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Position</option>
                                        <option value="Manager">Manager</option>
                                        <option value="Supervisor">Supervisor</option>
                                        <option value="Coordinator">Coordinator</option>
                                        <option value="Officer">Officer</option>
                                        <option value="Assistant">Assistant</option>
                                        <option value="Specialist">Specialist</option>
                                        <option value="Analyst">Analyst</option>
                                        <option value="Technician">Technician</option>
                                        <option value="Lecturer">Lecturer</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Country -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <select id="edit_country" name="country" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Country</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Tanzania">Tanzania</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            
                            <!-- Modal Actions -->
                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" onclick="closeModal()"
                                        class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Modal functions
        function editStaff(id, name, email, department, position, country) {
            document.getElementById('edit_staff_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_department').value = department;
            document.getElementById('edit_position').value = position;
            document.getElementById('edit_country').value = country;
            
            document.getElementById('editModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Form validation for modal
        document.getElementById('editForm').addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>

</body>
</html>
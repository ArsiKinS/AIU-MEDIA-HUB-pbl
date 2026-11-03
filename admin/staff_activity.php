<?php
require('../login/connect.php');

// Get all staff members
$staff_sql = "SELECT staff_id, name, department, position FROM staff ORDER BY name";
$staff_result = $dbconnection->query($staff_sql);

// Get selected staff ID from URL
$selected_staff_id = isset($_GET['staff_id']) ? $_GET['staff_id'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Activity | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <aside class="w-full   md:w-64 bg-[#0c9e95]  text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-indigo-500/50 text-center">
                AIU MEDIA <span class="text-indigo-300"> HUB</span>
            </div>
            
            <nav class="flex-grow p-4 space-y-2 mt-4 hidden md:block" 
                 x-data="{ generalOpen: <?= ($current_page == 'user_info_view.php' || $current_page == 'admin.php') ? 'true' : 'false' ?> }">
                
                <div class="space-y-1">
                    <a href="admin.php">
                    <button @click="generalOpen = !generalOpen" 
                            class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-white hover:text-[#64b12b] transition focus:outline-none"
                            :class="generalOpen ?  shadow-md text-white' : 'text-indigo-100'">
                        <div class="flex items-center">
                            <span class="mr-3">📊</span> General
                        </div>
                        <span class="text-[10px] transition-transform duration-200" :class="generalOpen ? 'rotate-180' : ''">▼</span>
                    </button>
                    </a>

                    <div x-show="generalOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="pl-10 space-y-1">
                        
                        <a href="user_info_view.php " 
                           class="block p-2 text-sm transition <?= ($current_page == 'user_info_view.php') ? 'text-white font-bold' : 'text-indigo-200 hover:text-white' ?>">
                            • User Activity
                        </a>

                        <a href="add_user.php" 
                           class="block p-2 text-sm transition <?= ($current_page == 'add_user.php') ? 'text-white font-bold' : 'text-indigo-200 hover:text-white' ?>">
                            • Add New User
                        </a>
                        
                    </div>
                </div>
    <a href="create_content.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">🕒</span> Create Content
    </a>

    <a href="edit_content.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Content History
    </a>



        <a href="staff_activity.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Staff Activity
    </a>
            <a href="interaction_search.php" class="flex items-center p-3 rounded-lg hover:bg-[white] transition text-indigo-100 hover:bg-white hover:text-[#64b12b]">
        <span class="mr-3">⚙️</span> Interaction Tracking
    </a>
            </nav>
        </aside>


        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                
                <!-- Header -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">👥 Staff Activity Tracker</h2>
                            <p class="text-gray-600">View all actions and activities by staff members</p>
                        </div>
                        <a href="add_staff.php" 
                           class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                            + Add Staff
                        </a>
                         <a href="edit_staff.php" 
                           class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                            Edit Staff
                        </a>
                    </div>
                </div>

                <!-- Staff List -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Select Staff Member</h3>
                    </div>
                    
                    <?php if ($staff_result && $staff_result->num_rows > 0): ?>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php while($staff = $staff_result->fetch_assoc()): 
                                    $is_selected = $selected_staff_id == $staff['staff_id'];
                                ?>
                                <a href="staff_activity.php?staff_id=<?php echo $staff['staff_id']; ?>" 
                                   class="block p-4 border rounded-lg hover:bg-gray-50 <?php echo $is_selected ? 'bg-blue-50 border-blue-300' : ''; ?>">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mr-4">
                                            <span class="text-xl">👤</span>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($staff['name']); ?></h4>
                                            <p class="text-sm text-gray-600">ID: <?php echo $staff['staff_id']; ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($staff['department']); ?> • <?php echo htmlspecialchars($staff['position']); ?></p>
                                        </div>
                                    </div>
                                </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500">
                            No staff members found
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($selected_staff_id): 
                    // Get staff details
                    $staff_detail_sql = "SELECT * FROM staff WHERE staff_id = ?";
                    $staff_detail_stmt = $dbconnection->prepare($staff_detail_sql);
                    $staff_detail_stmt->bind_param("i", $selected_staff_id);
                    $staff_detail_stmt->execute();
                    $staff_detail_result = $staff_detail_stmt->get_result();
                    $staff_details = $staff_detail_result->fetch_assoc();
                    
                    if ($staff_details):
                ?>
                    <!-- Staff Details Header -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <div class="flex items-center">
                            <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mr-6">
                                <span class="text-3xl">👤</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($staff_details['name']); ?></h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
                                    <div>
                                        <p class="text-sm text-gray-500">Staff ID</p>
                                        <p class="font-medium">#<?php echo $staff_details['staff_id']; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Department</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($staff_details['department']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Position</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($staff_details['position']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Email</p>
                                        <p class="font-medium"><?php echo htmlspecialchars($staff_details['email']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add this after the staff details header -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Submit New Report</h3>
            <p class="text-gray-600">Staff can submit reports for review</p>
        </div>
        <a href="submit_report.php" 
           class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Submit Report
        </a>
    </div>
</div>
                    <!-- Content Created by Staff -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">📝 Content Created</h3>
                            <span class="text-sm text-gray-500">Total content created by this staff</span>
                        </div>
                        
                        <?php
                        // Get content created by this staff
                        $content_sql = "SELECT * FROM content WHERE staff_id = ? ORDER BY created_on DESC";
                        $content_stmt = $dbconnection->prepare($content_sql);
                        $content_stmt->bind_param("i", $selected_staff_id);
                        $content_stmt->execute();
                        $content_result = $content_stmt->get_result();
                        ?>
                        
                        <?php if ($content_result->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created On</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while($content = $content_result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                #<?php echo $content['content_id']; ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($content['title']); ?></div>
                                                <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($content['description']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $type_badges = [
                                                    'NEWS' => 'bg-blue-100 text-blue-800',
                                                    'ANNOUNCEMENT' => 'bg-green-100 text-green-800',
                                                    'ASSOCIATION_UPDATE' => 'bg-purple-100 text-purple-800'
                                                ];
                                                $badge_class = $type_badges[$content['content_type']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $badge_class; ?>">
                                                    <?php echo $content['content_type']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $status_badges = [
                                                    'PUBLISHED' => 'bg-green-100 text-green-800',
                                                    'DRAFT' => 'bg-yellow-100 text-yellow-800'
                                                ];
                                                $status_class = $status_badges[$content['status']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_class; ?>">
                                                    <?php echo $content['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y', strtotime($content['created_on'])); ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-gray-500">
                                No content created by this staff member
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content History Actions -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">📚 Content History Actions</h3>
                            <span class="text-sm text-gray-500">Content management activities</span>
                        </div>
                        
                        <?php
                        // Get content history by this staff
                        $history_sql = "SELECT * FROM content_history WHERE STAFF_ID = ? ORDER BY TIMESTAMP DESC";
                        $history_stmt = $dbconnection->prepare($history_sql);
                        $history_stmt->bind_param("i", $selected_staff_id);
                        $history_stmt->execute();
                        $history_result = $history_stmt->get_result();
                        ?>
                        
                        <?php if ($history_result->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while($history = $history_result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $action_badges = [
                                                    'CREATED' => 'bg-green-100 text-green-800',
                                                    'EDITED' => 'bg-blue-100 text-blue-800',
                                                    'REVIEWED' => 'bg-yellow-100 text-yellow-800',
                                                    'APPROVED' => 'bg-purple-100 text-purple-800',
                                                    'DELETED' => 'bg-red-100 text-red-800'
                                                ];
                                                $action_class = $action_badges[$history['ACTION']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $action_class; ?>">
                                                    <?php echo $history['ACTION']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                #<?php echo $history['CONTENT_ID']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <?php echo $history['CONTENT_TYPE']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('Y-m-d H:i:s', strtotime($history['TIMESTAMP'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <?php echo htmlspecialchars($history['NOTES']); ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-gray-500">
                                No content history actions found for this staff member
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Staff Reports -->
                    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">📋 Staff Reports & Issues</h3>
                            <span class="text-sm text-gray-500">Issues submitted by this staff</span>
                        </div>
                        
                        <?php
                        // Get staff reports by this staff
                        $reports_sql = "SELECT * FROM staff_report WHERE STAFF_ID = ? ORDER BY SUBMITTED_ON DESC";
                        $reports_stmt = $dbconnection->prepare($reports_sql);
                        $reports_stmt->bind_param("i", $selected_staff_id);
                        $reports_stmt->execute();
                        $reports_result = $reports_stmt->get_result();
                        ?>
                        
                        <?php if ($reports_result->num_rows > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while($report = $reports_result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                #<?php echo $report['STAFF_REPORT_ID']; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                <?php echo $report['ISSUE_TYPE']; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <?php echo htmlspecialchars($report['DESCRIPTION']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $status_badges = [
                                                    'RESOLVED' => 'bg-green-100 text-green-800',
                                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                                    'IN_PROGRESS' => 'bg-blue-100 text-blue-800',
                                                    'UNDER_REVIEW' => 'bg-purple-100 text-purple-800',
                                                    'APPROVED' => 'bg-indigo-100 text-indigo-800'
                                                ];
                                                $status_class = $status_badges[$report['STATUS']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_class; ?>">
                                                    <?php echo $report['STATUS']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y', strtotime($report['SUBMITTED_ON'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo $report['RESOLVED_DATE'] ? date('M j, Y', strtotime($report['RESOLVED_DATE'])) : 'Not resolved'; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center text-gray-500">
                                No reports submitted by this staff member
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Activity Summary -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">📊 Activity Summary</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm text-blue-600">Content Created</p>
                                    <p class="text-2xl font-bold text-gray-800">
                                        <?php 
                                        $count_content_sql = "SELECT COUNT(*) as count FROM content WHERE staff_id = ?";
                                        $count_content_stmt = $dbconnection->prepare($count_content_sql);
                                        $count_content_stmt->bind_param("i", $selected_staff_id);
                                        $count_content_stmt->execute();
                                        $count_content_result = $count_content_stmt->get_result();
                                        $content_count = $count_content_result->fetch_assoc()['count'];
                                        echo $content_count;
                                        ?>
                                    </p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-green-600">History Actions</p>
                                    <p class="text-2xl font-bold text-gray-800">
                                        <?php 
                                        $count_history_sql = "SELECT COUNT(*) as count FROM content_history WHERE STAFF_ID = ?";
                                        $count_history_stmt = $dbconnection->prepare($count_history_sql);
                                        $count_history_stmt->bind_param("i", $selected_staff_id);
                                        $count_history_stmt->execute();
                                        $count_history_result = $count_history_stmt->get_result();
                                        $history_count = $count_history_result->fetch_assoc()['count'];
                                        echo $history_count;
                                        ?>
                                    </p>
                                </div>
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <p class="text-sm text-yellow-600">Reports Submitted</p>
                                    <p class="text-2xl font-bold text-gray-800">
                                        <?php 
                                        $count_reports_sql = "SELECT COUNT(*) as count FROM staff_report WHERE STAFF_ID = ?";
                                        $count_reports_stmt = $dbconnection->prepare($count_reports_sql);
                                        $count_reports_stmt->bind_param("i", $selected_staff_id);
                                        $count_reports_stmt->execute();
                                        $count_reports_result = $count_reports_stmt->get_result();
                                        $reports_count = $count_reports_result->fetch_assoc()['count'];
                                        echo $reports_count;
                                        ?>
                                    </p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm text-purple-600">Member Since</p>
                                    <p class="text-2xl font-bold text-gray-800">
                                        <?php 
                                        // Assuming staff table has created_at column, if not use current date
                                        if (isset($staff_details['created_at'])) {
                                            echo date('Y', strtotime($staff_details['created_at']));
                                        } else {
                                            echo "2024";
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-8 text-center">
                        <div class="text-gray-400 text-6xl mb-4">👤</div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Staff Not Found</h3>
                        <p class="text-gray-500 mb-4">The selected staff member does not exist.</p>
                    </div>
                <?php endif; ?>

                <?php endif; ?>

            </div>
        </main>
    </div>

</body>
</html>
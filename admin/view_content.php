<?php
require('../login/connect.php');

// Handle actual deletion (bypass foreign key temporarily)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    try {
        // First, get the content details before deleting
        $select_sql = "SELECT title, content_type, staff_id FROM content WHERE content_id = ?";
        $select_stmt = $dbconnection->prepare($select_sql);
        $select_stmt->bind_param("i", $delete_id);
        $select_stmt->execute();
        $select_result = $select_stmt->get_result();
        
        if ($select_result->num_rows > 0) {
            $content_data = $select_result->fetch_assoc();
            $content_title = $content_data['title'];
            $content_type = $content_data['content_type'];
            $staff_id = $content_data['staff_id'];
            
            // Disable foreign key checks temporarily
            $dbconnection->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Delete the content
            $delete_sql = "DELETE FROM content WHERE content_id = ?";
            $delete_stmt = $dbconnection->prepare($delete_sql);
            $delete_stmt->bind_param("i", $delete_id);
            
            if ($delete_stmt->execute()) {
                // Add deletion record to content_history
                $history_sql = "INSERT INTO content_history (CONTENT_ID, CONTENT_TYPE, ACTION, TIMESTAMP, STAFF_ID, NOTES) 
                               VALUES (?, ?, 'DELETED', NOW(), ?, ?)";
                $history_stmt = $dbconnection->prepare($history_sql);
                $notes = "Content deleted: " . $content_title;
                $history_stmt->bind_param("isis", $delete_id, $content_type, $staff_id, $notes);
                $history_stmt->execute();
                
                $delete_message = "Content deleted successfully! Deletion logged in history.";
            } else {
                $delete_error = "Error deleting content: " . $delete_stmt->error;
            }
            
            // Re-enable foreign key checks
            $dbconnection->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        
    } catch (mysqli_sql_exception $e) {
        // Make sure foreign key checks are re-enabled on error
        $dbconnection->query("SET FOREIGN_KEY_CHECKS = 1");
        $delete_error = "Error: " . $e->getMessage();
    }
}

// Simple query to get content
$sql = "SELECT * FROM content ORDER BY created_on DESC";
$result = $dbconnection->query($sql);

// Get content history - using TIMESTAMP column
$history_sql = "SELECT * FROM content_history ORDER BY TIMESTAMP DESC";
$history_result = $dbconnection->query($history_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Content | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <aside class="w-64  text-white bg-[#0c9e95] ">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <p class="text-indigo-200 text-sm">Content Management</p>
            </div>
            
            <nav class="p-4 space-y-2">
                <a href="admin.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    📊 Dashboard
                </a>
                <a href="create_content.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    📝 Create Content
                </a>
                <a href="view_content.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    🕒 All Content
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded hover:bg-white hover:text-[#64b12b]">
                    ⚙️ Settings
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                
                <!-- Header -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">📋 All Content</h2>
                            <p class="text-gray-600">Manage your news, announcements, and updates</p>
                        </div>
                        <a href="create_content.php" 
                           class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                            + Create New
                        </a>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if (isset($delete_message)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                        <p class="text-green-700 font-semibold">✓ <?php echo $delete_message; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (isset($delete_error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <p class="text-red-700 font-semibold">✗ <?php echo $delete_error; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Content Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $row['content_id']; ?></td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['title']); ?></div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($row['description']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $type_badges = [
                                                'NEWS' => 'bg-blue-100 text-blue-800',
                                                'ANNOUNCEMENT' => 'bg-green-100 text-green-800',
                                                'ASSOCIATION_UPDATE' => 'bg-purple-100 text-purple-800'
                                            ];
                                            $badge_class = $type_badges[$row['content_type']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $badge_class; ?>">
                                                <?php echo $row['content_type']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_badges = [
                                                'PUBLISHED' => 'bg-green-100 text-green-800',
                                                'DRAFT' => 'bg-yellow-100 text-yellow-800'
                                            ];
                                            $status_class = $status_badges[$row['status']] ?? 'bg-gray-100 text-gray-800';
                                            ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo $status_class; ?>">
                                                <?php echo $row['status']; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y', strtotime($row['created_on'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="edit_content.php?id=<?php echo $row['content_id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50">
                                                    ✏️ Edit
                                                </a>
                                                <a href="view_content.php?delete_id=<?php echo $row['content_id']; ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this content?')"
                                                   class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50">
                                                    🗑️ Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center">
                            <div class="text-gray-400 text-6xl mb-4">📄</div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">No Content Found</h3>
                            <p class="text-gray-500 mb-4">You haven't created any content yet.</p>
                            <a href="create_content.php" 
                               class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                                Create Your First Content
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Content History Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">📚 Content History Log</h3>
                    </div>
                    
                    <?php if ($history_result && $history_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">History ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($history = $history_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo $history['CONTENT_HISTORY_ID']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            #<?php echo $history['CONTENT_ID']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <?php echo $history['CONTENT_TYPE']; ?>
                                        </td>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('Y-m-d H:i:s', strtotime($history['TIMESTAMP'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            Staff #<?php echo $history['STAFF_ID']; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?php echo htmlspecialchars($history['NOTES']); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t text-sm text-gray-500">
                            Total: <?php echo $history_result->num_rows; ?> history records
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center">
                            <div class="text-gray-400 text-6xl mb-4">📚</div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">No History Records</h3>
                            <p class="text-gray-500">No content history records found.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
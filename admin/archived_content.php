<?php
require('../login/connect.php');

// Handle restore from archive
if (isset($_GET['restore_id'])) {
    $restore_id = $_GET['restore_id'];
    
    $sql = "UPDATE content SET deleted_at = NULL WHERE content_id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("i", $restore_id);
    
    if ($stmt->execute()) {
        $restore_message = "Content restored successfully!";
    } else {
        $restore_error = "Error: " . $stmt->error;
    }
}

// Show only archived content
$sql = "SELECT * FROM content WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC";
$result = $dbconnection->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Content | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-indigo-800 text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <p class="text-indigo-200 text-sm">Content Archive</p>
            </div>
            
            <nav class="p-4 space-y-2">
                <a href="admin.php" class="flex items-center p-3 rounded hover:bg-indigo-700">
                    📊 Dashboard
                </a>
                <a href="create_content.php" class="flex items-center p-3 rounded hover:bg-indigo-700">
                    📝 Create Content
                </a>
                <a href="view_content.php" class="flex items-center p-3 rounded hover:bg-indigo-700">
                    🕒 Active Content
                </a>
                <a href="archived_content.php" class="flex items-center p-3 rounded bg-indigo-900">
                    📦 Archived Content
                </a>
                <a href="settings.php" class="flex items-center p-3 rounded hover:bg-indigo-700">
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
                            <h2 class="text-3xl font-bold text-gray-800 mb-2">📦 Archived Content</h2>
                            <p class="text-gray-600">Content that has been archived but not deleted</p>
                        </div>
                        <a href="view_content.php" 
                           class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                            ← Back to Active
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (isset($restore_message)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                        <p class="text-green-700 font-semibold">✓ <?php echo $restore_message; ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($restore_error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <p class="text-red-700 font-semibold">✗ <?php echo $restore_error; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Archived Content Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archived On</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 bg-gray-50">
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y g:i A', strtotime($row['deleted_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="archived_content.php?restore_id=<?php echo $row['content_id']; ?>" 
                                                   onclick="return confirmRestore('<?php echo htmlspecialchars(addslashes($row['title'])); ?>')"
                                                   class="text-green-600 hover:text-green-900 px-2 py-1 rounded hover:bg-green-50">
                                                    🔄 Restore
                                                </a>
                                                <a href="edit_content.php?id=<?php echo $row['content_id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50">
                                                    👁️ View
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
                            <div class="text-gray-400 text-6xl mb-4">📦</div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">Archive is Empty</h3>
                            <p class="text-gray-500 mb-4">No content has been archived yet.</p>
                            <a href="view_content.php" 
                               class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                                View Active Content
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function confirmRestore(title) {
            return confirm(`Restore "${title}" to active content?\n\n✅ Content will appear in active list\n✅ Reading history preserved\n✅ Can be archived again if needed\n\nClick OK to restore.`);
        }
    </script>

</body>
</html>
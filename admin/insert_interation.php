<?php
require_once 'config.php';

$message = '';
$message_type = '';
$sql_statement = '';

// Get existing users and content from database
$conn = getConnection();

// Get existing users
$users = [];
$result = $conn->query("SELECT DISTINCT USER_ID FROM interaction_log ORDER BY USER_ID");
while ($row = $result->fetch_assoc()) {
    $users[] = $row['USER_ID'];
}

// Get existing content
$content = [];
$result = $conn->query("SELECT DISTINCT CONTENT_ID FROM interaction_log ORDER BY CONTENT_ID");
while ($row = $result->fetch_assoc()) {
    $content[] = $row['CONTENT_ID'];
}

// Get next interaction ID
$next_id_result = $conn->query("SELECT MAX(INTERACTION_ID) as max_id FROM interaction_log");
$next_id_row = $next_id_result->fetch_assoc();
$next_id = $next_id_row['max_id'] ? $next_id_row['max_id'] + 1 : 1010;

$conn->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'interaction_id' => $_POST['interaction_id'],
        'user_id' => $_POST['user_id'],
        'content_id' => $_POST['content_id'],
        'interaction_type' => $_POST['interaction_type'],
        'interaction_text' => $_POST['interaction_text'],
        'timestamp' => $_POST['timestamp'] ?: date('Y-m-d H:i:s')
    ];
    
    // Validate
    if (empty($data['interaction_text'])) {
        $message = 'Interaction text is required!';
        $message_type = 'error';
    } else {
        // Insert into database
        if (insertInteraction($data)) {
            $message = "Success! Interaction #{$data['interaction_id']} inserted.";
            $message_type = 'success';
            $next_id = $data['interaction_id'] + 1;
            
            // Update users and content lists
            if (!in_array($data['user_id'], $users)) {
                $users[] = $data['user_id'];
                sort($users);
            }
            
            if (!in_array($data['content_id'], $content)) {
                $content[] = $data['content_id'];
                sort($content);
            }
            
            // Generate SQL statement
            $sql_statement = "INSERT INTO interaction_log (INTERACTION_ID, TIMESTAMP, INTERACTION_TYPE, INTERACTION_TEXT, CONTENT_ID, USER_ID)\n";
            $sql_statement .= "VALUES ({$data['interaction_id']}, '{$data['timestamp']}', '{$data['interaction_type']}', '{$data['interaction_text']}', {$data['content_id']}, {$data['user_id']});";
        } else {
            $message = 'Error inserting data. Please try again.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Interaction - Existing Users & Content</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen p-4">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-plus-circle text-blue-600 mr-2"></i>
                        Insert New Interaction
                    </h1>
                    <p class="text-gray-600 mt-1">Select existing User ID and Content ID</p>
                </div>
                <a href="search_page.php" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Search
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-lg mr-3">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Existing Users</p>
                        <p class="text-xl font-bold text-gray-800"><?php echo count($users); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-lg mr-3">
                        <i class="fas fa-file-alt text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Existing Content</p>
                        <p class="text-xl font-bold text-gray-800"><?php echo count($content); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <form method="POST" action="" class="space-y-6">
                <!-- ID Section -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <label for="interaction_id" class="block text-sm font-semibold text-blue-800 mb-2">
                        <i class="fas fa-id-card mr-2"></i>
                        INTERACTION_ID
                    </label>
                    <input type="number" 
                           id="interaction_id" 
                           name="interaction_id"
                           value="<?php echo $next_id; ?>"
                           required
                           readonly
                           class="w-full px-4 py-3 bg-white border-2 border-blue-200 rounded-lg font-bold text-blue-700 text-center text-lg">
                    <p class="text-sm text-blue-600 mt-2 text-center">Auto-generated from database</p>
                </div>

                <!-- User & Content Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Existing Users -->
                    <div>
                        <label for="user_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-check mr-2"></i>
                            Select Existing USER_ID
                        </label>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <select id="user_id" 
                                    name="user_id"
                                    required
                                    class="w-full px-4 py-3 border-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose existing user...</option>
                                <?php foreach ($users as $user_id): ?>
                                <option value="<?php echo $user_id; ?>">User <?php echo $user_id; ?></option>
                                <?php endforeach; ?>
                                <option value="new" class="font-semibold text-green-600">+ Add New User ID</option>
                            </select>
                        </div>
                        <div id="newUserField" class="mt-3 hidden">
                            <input type="number" 
                                   id="new_user_id" 
                                   name="new_user_id"
                                   placeholder="Enter new User ID"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>

                    <!-- Existing Content -->
                    <div>
                        <label for="content_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-medical mr-2"></i>
                            Select Existing CONTENT_ID
                        </label>
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <select id="content_id" 
                                    name="content_id"
                                    required
                                    class="w-full px-4 py-3 border-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose existing content...</option>
                                <?php foreach ($content as $content_id): ?>
                                <option value="<?php echo $content_id; ?>">Content <?php echo $content_id; ?></option>
                                <?php endforeach; ?>
                                <option value="new" class="font-semibold text-green-600">+ Add New Content ID</option>
                            </select>
                        </div>
                        <div id="newContentField" class="mt-3 hidden">
                            <input type="number" 
                                   id="new_content_id" 
                                   name="new_content_id"
                                   placeholder="Enter new Content ID"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Interaction Details -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <!-- Interaction Type -->
                        <div>
                            <label for="interaction_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-mouse-pointer mr-2"></i>
                                INTERACTION_TYPE
                            </label>
                            <select id="interaction_type" 
                                    name="interaction_type"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select type...</option>
                                <option value="SEARCH">SEARCH</option>
                                <option value="CLICK">CLICK</option>
                                <option value="VIEW" selected>VIEW</option>
                                <option value="SHARE">SHARE</option>
                                <option value="LIKE">LIKE</option>
                                <option value="BOOKMARK">BOOKMARK</option>
                                <option value="DOWNLOAD">DOWNLOAD</option>
                                <option value="COMMENT">COMMENT</option>
                            </select>
                        </div>

                        <!-- Timestamp -->
                        <div>
                            <label for="timestamp" class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-clock mr-2"></i>
                                TIMESTAMP
                            </label>
                            <input type="datetime-local" 
                                   id="timestamp" 
                                   name="timestamp"
                                   value="<?php echo date('Y-m-d\TH:i'); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Interaction Text -->
                    <div>
                        <label for="interaction_text" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-comment-dots mr-2"></i>
                            INTERACTION_TEXT
                        </label>
                        <textarea id="interaction_text" 
                                  name="interaction_text"
                                  rows="3"
                                  required
                                  placeholder="Describe what the user did..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-4 rounded-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    INSERT INTO interaction_log
                </button>
            </form>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="mb-6 animate-fade-in">
                <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border-green-400 text-green-800' : 'bg-red-100 border-red-400 text-red-800'; ?> border-l-4 p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle text-green-500' : 'exclamation-circle text-red-500'; ?> text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="font-medium"><?php echo $message; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- SQL Preview -->
        <?php if ($sql_statement): ?>
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-database text-blue-600 mr-2"></i>
                        SQL Generated
                    </h3>
                    <button onclick="copySQL()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-copy mr-1"></i>Copy SQL
                    </button>
                </div>
                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                    <pre class="text-sm font-mono leading-relaxed"><?php echo htmlspecialchars($sql_statement); ?></pre>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-bolt mr-2"></i>
                Quick Actions
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <button onclick="fillSampleData()" 
                        class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-3 rounded-lg font-medium transition">
                    <i class="fas fa-vial mr-2"></i>Fill Sample
                </button>
                <button onclick="clearForm()" 
                        class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-4 py-3 rounded-lg font-medium transition">
                    <i class="fas fa-eraser mr-2"></i>Clear Form
                </button>
                <a href="insert_interaction.php" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-medium transition text-center">
                    <i class="fas fa-redo mr-2"></i>Refresh
                </a>
            </div>
        </div>

        <!-- Existing Data Preview -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Existing Users -->
            <div class="bg-white rounded-lg shadow p-4">
                <h4 class="font-semibold text-gray-700 mb-3">
                    <i class="fas fa-user-friends mr-2"></i>Existing Users (<?php echo count($users); ?>)
                </h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($users as $user_id): ?>
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">User <?php echo $user_id; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Existing Content -->
            <div class="bg-white rounded-lg shadow p-4">
                <h4 class="font-semibold text-gray-700 mb-3">
                    <i class="fas fa-folder-open mr-2"></i>Existing Content (<?php echo count($content); ?>)
                </h4>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($content as $content_id): ?>
                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">Content <?php echo $content_id; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle new user selection
        document.getElementById('user_id').addEventListener('change', function() {
            const newUserField = document.getElementById('newUserField');
            if (this.value === 'new') {
                newUserField.classList.remove('hidden');
                document.getElementById('new_user_id').required = true;
            } else {
                newUserField.classList.add('hidden');
                document.getElementById('new_user_id').required = false;
            }
        });

        // Handle new content selection
        document.getElementById('content_id').addEventListener('change', function() {
            const newContentField = document.getElementById('newContentField');
            if (this.value === 'new') {
                newContentField.classList.remove('hidden');
                document.getElementById('new_content_id').required = true;
            } else {
                newContentField.classList.add('hidden');
                document.getElementById('new_content_id').required = false;
            }
        });

        // Before form submission, handle new user/content
        document.querySelector('form').addEventListener('submit', function(e) {
            const userSelect = document.getElementById('user_id');
            const contentSelect = document.getElementById('content_id');
            
            if (userSelect.value === 'new') {
                const newUserId = document.getElementById('new_user_id').value;
                if (newUserId) {
                    // Create a hidden input with the new user ID
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'user_id';
                    hiddenInput.value = newUserId;
                    this.appendChild(hiddenInput);
                    userSelect.disabled = true;
                }
            }
            
            if (contentSelect.value === 'new') {
                const newContentId = document.getElementById('new_content_id').value;
                if (newContentId) {
                    // Create a hidden input with the new content ID
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'content_id';
                    hiddenInput.value = newContentId;
                    this.appendChild(hiddenInput);
                    contentSelect.disabled = true;
                }
            }
        });

        // Copy SQL to clipboard
        function copySQL() {
            const sqlText = document.querySelector('pre')?.textContent;
            if (sqlText) {
                navigator.clipboard.writeText(sqlText).then(() => {
                    alert('SQL copied to clipboard!');
                });
            }
        }

        // Clear form
        function clearForm() {
            if (confirm('Clear all form fields?')) {
                document.querySelector('form').reset();
                document.getElementById('interaction_id').value = '<?php echo $next_id; ?>';
                document.getElementById('timestamp').value = new Date().toISOString().slice(0, 16);
                document.getElementById('newUserField').classList.add('hidden');
                document.getElementById('newContentField').classList.add('hidden');
            }
        }

        // Fill sample data
        function fillSampleData() {
            // Get first existing user and content
            const existingUsers = <?php echo json_encode($users); ?>;
            const existingContent = <?php echo json_encode($content); ?>;
            
            if (existingUsers.length > 0) {
                document.getElementById('user_id').value = existingUsers[0];
            }
            
            if (existingContent.length > 0) {
                document.getElementById('content_id').value = existingContent[0];
            }
            
            document.getElementById('interaction_type').value = 'VIEW';
            document.getElementById('interaction_text').value = 'Viewed the dashboard for analytics';
            document.getElementById('timestamp').value = new Date().toISOString().slice(0, 16);
            
            alert('Sample data loaded using existing User ID and Content ID.');
        }

        // Auto-focus on interaction text
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('interaction_text').focus();
        });
    </script>
    
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</body>
</html>
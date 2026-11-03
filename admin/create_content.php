<?php
require('../login/connect.php');
session_start(); // ADD THIS

// Fetch staff members for dropdown
$staff_query = "SELECT staff_id, name, email FROM staff ORDER BY name";
$staff_result = $dbconnection->query($staff_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $body_content = $_POST['body_content'];
    $description = $_POST['description'];
    $content_type = $_POST['content_type'];
    $status = $_POST['status'];
    $staff_id = $_POST['staff_id'];
    
    // Validate staff exists
    if (!empty($staff_id)) {
        $validate_staff = $dbconnection->prepare("SELECT staff_id FROM staff WHERE staff_id = ?");
        $validate_staff->bind_param("i", $staff_id);
        $validate_staff->execute();
        $validate_staff->store_result();
        
        if ($validate_staff->num_rows == 0) {
            $_SESSION['error'] = "Invalid staff selected.";
        }
        $validate_staff->close();
    } else {
        $_SESSION['error'] = "Please select an admin.";
    }
    
    // Handle image upload
    $media_path = null;
    if (!isset($_SESSION['error']) && isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['media']['name'];
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_size = $_FILES['media']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check file extension
        if (in_array($file_ext, $allowed_extensions)) {
            // Check file size (max 5MB)
            if ($file_size <= 5242880) {
                // Generate unique filename
                $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
                $upload_dir = '../img/content/';
                
                // Create directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $upload_path = $upload_dir . $new_filename;
                $relative_path = 'img/content/' . $new_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $media_path = $relative_path;
                } else {
                    $_SESSION['error'] = "Failed to upload image.";
                }
            } else {
                $_SESSION['error'] = "File size too large. Maximum size is 5MB.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Allowed: JPG, JPEG, PNG, GIF, WEBP.";
        }
    }
    
    // Insert into content table
    if (!isset($_SESSION['error'])) {
        // Get current date
        $current_date = date('Y-m-d');
        
        // Start transaction
        $dbconnection->begin_transaction();
        
        try {
            // Step 1: Insert into content table (WITHOUT media_id column)
            // Your content table has: content_id, title, body_content, description, created_on, content_type, status, staff_id, media
            $content_sql = "INSERT INTO content (title, body_content, description, content_type, status, staff_id, created_on) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $content_stmt = $dbconnection->prepare($content_sql);
            $content_stmt->bind_param("sssssis", $title, $body_content, $description, $content_type, $status, $staff_id, $current_date);
            
            if (!$content_stmt->execute()) {
                throw new Exception("Error inserting content: " . $content_stmt->error);
            }
            
            $content_id = $content_stmt->insert_id;
            $content_stmt->close();
            
            // Step 2: Insert into media table (if image was uploaded)
            if ($media_path) {
                $media_sql = "INSERT INTO media (MEDIA_TYPE, MEDIA_PATH, CAPTION, CONTENT_ID) 
                             VALUES (?, ?, ?, ?)";
                
                $media_type = 'IMAGE';
                $caption = $title;
                
                $media_stmt = $dbconnection->prepare($media_sql);
                $media_stmt->bind_param("sssi", $media_type, $media_path, $caption, $content_id);
                
                if (!$media_stmt->execute()) {
                    throw new Exception("Error inserting media: " . $media_stmt->error);
                }
                
                $media_stmt->close();
            }
            
            // Commit transaction
            $dbconnection->commit();
            
            $_SESSION['success'] = "Content created successfully! Content ID: " . $content_id;
            
            // Redirect to avoid form resubmission
            header("Location: create_content.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback on error
            $dbconnection->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    }
    
    // Redirect if there were errors during processing
    header("Location: create_content.php");
    exit();
}

// Get messages from session
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : null;

// Clear messages after displaying
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Content | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">

    <!-- Navigation Sidebar -->
    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#0c9e95] text-white">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <p class="text-indigo-200 text-sm">Content Management</p>
            </div>
            
            <nav class="flex-grow p-4 space-y-2 mt-4">
                
                <div class="space-y-1" x-data="{ generalOpen: false }">
                    <a href="admin.php">
                        <button @click="generalOpen = !generalOpen" 
                                class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-white hover:text-[#64b12b] transition focus:outline-none"
                                :class="generalOpen ? 'bg-indigo-800 shadow-md text-white' : 'text-indigo-100'">
                            <div class="flex items-center">
                                <span class="mr-3">📊</span> General
                            </div>
                            <span class="text-[10px] transition-transform duration-200" :class="generalOpen ? 'rotate-180' : ''">▼</span>
                        </button>
                    </a>

                    <div x-show="generalOpen" x-transition class="pl-10 space-y-1 hover:text-[#64b12b]">
                        <a href="user_info_view.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                             • User Activity
                        </a>
                        <a href="add_user.php" class="block p-2 text-sm text-indigo-200 hover:text-white transition italic">
                            • Add New User
                        </a>
                    </div>
                </div>

                <a href="create_content.php" class="flex items-center p-3 rounded-lg bg-white text-[#64b12b] transition">
                    <span class="mr-3">📝</span> Create Content
                </a>

                <a href="view_content.php" class="flex items-center p-3 rounded-lg hover:bg-white hover:text-[#64b12b] transition text-indigo-100">
                    <span class="mr-3">🕒</span> Content History
                </a>

            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1">
            <div class="max-w-4xl mx-auto p-6">
                
                <div class="bg-white rounded-lg shadow p-6 mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Create New Content</h2>
                    <p class="text-gray-600 text-sm">Post news, announcements, or association updates</p>
                </div>

                <!-- Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded">
                        <p class="text-green-700 font-semibold">✓ <?php echo $success_message; ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded">
                        <p class="text-red-700 font-semibold">✗ <?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
                    
                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Title *</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter title">
                    </div>

                    <!-- Staff Selection -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Select Admin *</label>
                        <select name="staff_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Admin</option>
                            <?php 
                            if ($staff_result && $staff_result->num_rows > 0):
                                $staff_result->data_seek(0);
                                while($staff = $staff_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $staff['staff_id']; ?>">
                                    <?php echo htmlspecialchars($staff['name'] . ' (' . $staff['email'] . ')'); ?>
                                </option>
                            <?php 
                                endwhile;
                            else: 
                            ?>
                                <option value="">No staff members found</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Content Type -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Content Type *</label>
                        <select name="content_type" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="NEWS">📰 News</option>
                            <option value="ANNOUNCEMENT">📢 Announcement</option>
                            <option value="ASSOCIATION_UPDATE">🎯 Association Update</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Description (Short Summary)</label>
                        <input type="text" name="description"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                               placeholder="Brief description (optional)">
                    </div>

                    <!-- Body Content -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Body Content *</label>
                        <textarea name="body_content" required rows="6"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Write your content here..."></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Featured Image</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-500 transition">
                            <div class="mb-2">
                                <span class="text-4xl">🖼️</span>
                            </div>
                            <p class="text-gray-600 mb-2">Click to upload or drag and drop</p>
                            <p class="text-sm text-gray-500 mb-3">JPG, PNG, GIF, WEBP (Max 5MB)</p>
                            <input type="file" name="media" accept="image/*" 
                                   class="w-full cursor-pointer" 
                                   id="imageUpload">
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 hidden">
                                <div class="relative inline-block">
                                    <img id="previewImage" class="w-48 h-32 object-cover rounded-lg">
                                    <button type="button" onclick="removeImage()" 
                                            class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Status *</label>
                        <select name="status" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="PUBLISHED">✓ Published</option>
                            <option value="DRAFT">📝 Draft</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700">
                            Publish Content
                        </button>
                        <a href="view_content.php" 
                           class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold hover:bg-gray-300">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </main>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        function removeImage() {
            document.getElementById('imageUpload').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
        }

        // Drag and drop functionality
        const dropArea = document.querySelector('.border-dashed');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropArea.classList.add('border-blue-500', 'bg-blue-50');
        }

        function unhighlight() {
            dropArea.classList.remove('border-blue-500', 'bg-blue-50');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('imageUpload').files = files;
            
            // Trigger change event for preview
            const event = new Event('change');
            document.getElementById('imageUpload').dispatchEvent(event);
        }
    </script>

</body>
</html>
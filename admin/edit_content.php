<?php
require('../login/connect.php');

// Get content ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo "<script>alert('No content ID provided'); window.location='view_content.php';</script>";
    exit();
}

// 1. FETCH CONTENT + MEDIA (Using JOIN to fix Undefined Key error)
$sql = "SELECT c.*, m.MEDIA_PATH, m.MEDIA_ID 
        FROM content c 
        LEFT JOIN media m ON c.content_id = m.CONTENT_ID 
        WHERE c.content_id = ?";

$stmt = $dbconnection->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Content not found'); window.location='view_content.php';</script>";
    exit();
}

$content = $result->fetch_assoc();

// Map variables (Fixes 'description' or 'description 1' issues)
$current_image = $content['MEDIA_PATH'] ?? '';
$media_id = $content['MEDIA_ID'] ?? null;
$db_description = $content['description'] ?? ($content['description 1'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $body_content = $_POST['body_content'];
    $description = $_POST['description'];
    $content_type = $_POST['content_type'];
    $status = $_POST['status'];
    $staff_id = 201;
    
    $media_path = $current_image;
    
    // Check if remove image checkbox is checked
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if (!empty($current_image) && file_exists('../' . $current_image)) {
            unlink('../' . $current_image);
        }
        $media_path = ''; 
    }
    
    // Handle new image upload
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $file = $_FILES['media'];
        if ($file['size'] <= 5242880) {
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed)) {
                $upload_dir = '../img/content/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
                    if (!empty($current_image) && file_exists('../' . $current_image)) {
                        unlink('../' . $current_image);
                    }
                    $media_path = 'img/content/' . $new_filename;
                }
            }
        }
    }
    
    // 2. TRANSACTIONAL UPDATE (Updates both tables safely)
    $dbconnection->begin_transaction();
    try {
        // Update Content Table
        $sql_c = "UPDATE content SET title=?, body_content=?, description=?, content_type=?, status=?, staff_id=? WHERE content_id=?";
        $stmt_c = $dbconnection->prepare($sql_c);
        $stmt_c->bind_param("sssssii", $title, $body_content, $description, $content_type, $status, $staff_id, $id);
        $stmt_c->execute();

        // Update/Insert Media Table
        if ($media_path !== $current_image) {
            if ($media_id) {
                $sql_m = "UPDATE media SET MEDIA_PATH = ? WHERE CONTENT_ID = ?";
                $stmt_m = $dbconnection->prepare($sql_m);
                $stmt_m->bind_param("si", $media_path, $id);
            } else {
                $sql_m = "INSERT INTO media (MEDIA_PATH, MEDIA_TYPE, CONTENT_ID) VALUES (?, 'IMAGE', ?)";
                $stmt_m = $dbconnection->prepare($sql_m);
                $stmt_m->bind_param("si", $media_path, $id);
            }
            $stmt_m->execute();
        }

        $dbconnection->commit();
        $success = "Content updated successfully!";
        $current_image = $media_path; // Refresh local view
    } catch (Exception $e) {
        $dbconnection->rollback();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Content | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <div class="flex">
        <aside class="w-64 bg-indigo-800 text-white min-h-screen">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Admin Panel</h1>
                <p class="text-indigo-200 text-sm">Content Management</p>
            </div>
            <nav class="p-4 space-y-2">
                <a href="admin.php" class="flex items-center p-3 rounded hover:bg-indigo-700">📊 Dashboard</a>
                <a href="create_content.php" class="flex items-center p-3 rounded hover:bg-indigo-700">📝 Create Content</a>
                <a href="view_content.php" class="flex items-center p-3 rounded bg-indigo-900">🕒 Content History</a>
                <a href="settings.php" class="flex items-center p-3 rounded hover:bg-indigo-700">⚙️ Settings</a>
            </nav>
        </aside>

        <main class="flex-1 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">✏️ Edit Content</h2>
                    <p class="text-gray-600">Editing: <?php echo htmlspecialchars($content['title']); ?></p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Title *</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($content['title']); ?>" required
                               class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Content Type *</label>
                        <select name="content_type" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="NEWS" <?php echo $content['content_type'] == 'NEWS' ? 'selected' : ''; ?>>News</option>
                            <option value="ANNOUNCEMENT" <?php echo $content['content_type'] == 'ANNOUNCEMENT' ? 'selected' : ''; ?>>Announcement</option>
                            <option value="ASSOCIATION_UPDATE" <?php echo $content['content_type'] == 'ASSOCIATION_UPDATE' ? 'selected' : ''; ?>>Association Update</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Description</label>
                        <input type="text" name="description" value="<?php echo htmlspecialchars($db_description); ?>"
                               class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Content *</label>
                        <textarea name="body_content" required rows="6"
                                  class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($content['body_content']); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Image</label>
                        <?php if (!empty($current_image)): ?>
                        <div class="mb-3 p-3 bg-gray-50 rounded">
                            <p class="text-sm text-gray-600 mb-2">Current Image:</p>
                            <img src="../<?php echo htmlspecialchars($current_image); ?>" class="w-48 h-32 object-cover rounded border">
                            <div class="mt-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="remove_image" value="1" class="mr-2">
                                    <span class="text-sm text-red-600">Remove current image</span>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="border-2 border-dashed border-gray-300 rounded p-4">
                            <p class="text-sm text-gray-600 mb-2">Upload new image:</p>
                            <input type="file" name="media" accept="image/*" class="w-full px-3 py-2 border rounded" onchange="previewImage(this)">
                            <div id="imagePreview" class="mt-3 hidden">
                                <img id="preview" class="w-48 h-32 object-cover rounded border">
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-gray-700 font-bold mb-2">Status *</label>
                        <select name="status" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="PUBLISHED" <?php echo $content['status'] == 'PUBLISHED' ? 'selected' : ''; ?>>Published</option>
                            <option value="DRAFT" <?php echo $content['status'] == 'DRAFT' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded font-bold hover:bg-blue-700">Update Content</button>
                        <a href="view_content.php" class="px-6 py-2 bg-gray-300 text-gray-700 rounded font-bold hover:bg-gray-400">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const previewDiv = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    previewDiv.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
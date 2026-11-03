<?php
require('../login/connect.php');

// Enable strict error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_POST["add_user_btn"])) {
    try {
        $email = $_POST["email"];
        $username = $_POST["username"];
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $role = $_POST["user_category"]; // From the dropdown
        $location = $_POST["location"] ?? 'Not Specified';

        // 1. INSERT INTO media_hub_user
        // Note: I removed 'subtype' and used 'user_type' which exists in your table
        $sql = "INSERT INTO media_hub_user (email, username, password, user_type, location) VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("sssss", $email, $username, $password, $role, $location);
        $stmt->execute();

        $user_id = $dbconnection->insert_id;

        // 2. INSERT INTO CHILD TABLES (Student/Staff/Visitor)
        if ($role === 'Student') {
            $student_id = $_POST['student_id'] ?? '';
            $batch = $_POST['batch'] ?? '';
            $major = $_POST['major'] ?? '';
            $stmt_sub = $dbconnection->prepare("INSERT INTO student (user_id, student_card_id, batch, major) VALUES (?, ?, ?, ?)");
            $stmt_sub->bind_param("isss", $user_id, $student_id, $batch, $major);
            $stmt_sub->execute();
        } 
        elseif ($role === 'Staff') {
            $staff_id = $_POST['staff_id'] ?? '';
            $position = $_POST['position'] ?? '';
            $stmt_sub = $dbconnection->prepare("INSERT INTO aiu_staff (user_id, staff_card_id, position) VALUES (?, ?, ?)");
            $stmt_sub->bind_param("iss", $user_id, $staff_id, $position);
            $stmt_sub->execute();
        }
        elseif ($role === 'Visitor') {
            $phone = $_POST['phone'] ?? '';
            $occupation = $_POST['occupation'] ?? '';
            $stmt_sub = $dbconnection->prepare("INSERT INTO visitor (user_id, phone, occupation) VALUES (?, ?, ?)");
            $stmt_sub->bind_param("iss", $user_id, $phone, $occupation);
            $stmt_sub->execute();
        }

        // 3. INSERT INTO registration
        $stmt_reg = $dbconnection->prepare("INSERT INTO registration (user_id, registration_date) VALUES (?, NOW())");
        $stmt_reg->bind_param("i", $user_id);
        $stmt_reg->execute();

        header("Location: admin.php?status=success");
        exit();

    } catch (mysqli_sql_exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User | Media Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function toggleFields() {
            const val = document.getElementById('user_category').value;
            document.getElementById('s_fields').classList.toggle('hidden', val !== 'Student');
            document.getElementById('t_fields').classList.toggle('hidden', val !== 'Staff');
            document.getElementById('v_fields').classList.toggle('hidden', val !== 'Visitor');
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="bg-indigo-700 p-6 text-white text-center text-xl font-bold">Add New User Account</div>
        
        <form method="POST" class="p-8 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input type="text" name="username" placeholder="Username" required class="p-3 border rounded-xl">
                <input type="email" name="email" placeholder="Email" required class="p-3 border rounded-xl">
            </div>
            <input type="password" name="password" placeholder="Password" required class="w-full p-3 border rounded-xl">
            <input type="text" name="location" placeholder="Location (e.g. Malaysia)" class="w-full p-3 border rounded-xl">
            
            <select name="user_category" id="user_category" onchange="toggleFields()" required class="w-full p-3 border rounded-xl bg-white">
                <option value="">-- Select Category --</option>
                <option value="Student">Student</option>
                <option value="Staff">Staff</option>
                <option value="Visitor">Visitor</option>
            </select>

            <div id="s_fields" class="hidden space-y-2 bg-blue-50 p-4 rounded-xl">
                <input type="text" name="student_id" placeholder="Student ID" class="w-full p-2 border rounded-lg">
                <input type="text" name="batch" placeholder="Batch" class="w-full p-2 border rounded-lg">
                <input type="text" name="major" placeholder="Major" class="w-full p-2 border rounded-lg">
            </div>

            <div id="t_fields" class="hidden space-y-2 bg-purple-50 p-4 rounded-xl">
                <input type="text" name="staff_id" placeholder="Staff ID" class="w-full p-2 border rounded-lg">
                <input type="text" name="position" placeholder="Position" class="w-full p-2 border rounded-lg">
            </div>

            <div id="v_fields" class="hidden space-y-2 bg-orange-50 p-4 rounded-xl">
                <input type="text" name="phone" placeholder="Phone Number" class="w-full p-2 border rounded-lg">
                <input type="text" name="occupation" placeholder="Occupation" class="w-full p-2 border rounded-lg">
            </div>

            <button type="submit" name="add_user_btn" class="w-full py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-800">Save User</button>
            <a href="admin.php" class="block text-center text-gray-400">Cancel</a>
        </form>
    </div>
</body>
</html>
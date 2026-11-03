<?php
require('../login/connect.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $name = mysqli_real_escape_string($dbconnection, $_POST['name']);
    $email = mysqli_real_escape_string($dbconnection, $_POST['email']);
    $department = mysqli_real_escape_string($dbconnection, $_POST['department']);
    $position = mysqli_real_escape_string($dbconnection, $_POST['position']);
    $country = mysqli_real_escape_string($dbconnection, $_POST['country']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // Check if email already exists (excluding current staff)
        $check_sql = "SELECT * FROM staff WHERE email = ? AND staff_id != ?";
        $check_stmt = $dbconnection->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $staff_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Email already exists for another staff member!";
        } else {
            try {
                // Update staff
                $sql = "UPDATE staff SET 
                        name = ?, 
                        email = ?, 
                        department = ?, 
                        position = ?, 
                        country = ? 
                        WHERE staff_id = ?";
                
                $stmt = $dbconnection->prepare($sql);
                $stmt->bind_param("sssssi", $name, $email, $department, $position, $country, $staff_id);
                
                if ($stmt->execute()) {
                    $success_message = "Staff member updated successfully!";
                } else {
                    $error_message = "Error updating staff: " . $stmt->error;
                }
                
            } catch (mysqli_sql_exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Redirect back to edit page with message
$redirect_url = "edit_staff.php";
if ($success_message) {
    $redirect_url .= "?success=" . urlencode($success_message);
} elseif ($error_message) {
    $redirect_url .= "?error=" . urlencode($error_message);
}

header("Location: $redirect_url");
exit();
?>
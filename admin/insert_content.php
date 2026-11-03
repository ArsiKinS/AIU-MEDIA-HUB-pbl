<?php
require('../login/connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $title        = $_POST['title'];
    $body_content = $_POST['body_content'];
    $description  = $_POST['description'];
    $content_type = $_POST['content_type'];
    $status       = $_POST['status'];
    $staff_id     = $_POST['staff_id'];

    // Handle image upload (optional)
    $mediaName = NULL;

    if (!empty($_FILES['media']['name'])) {
        $mediaName = time() . "_" . $_FILES['media']['name'];
        $uploadPath = "../img/news/" . $mediaName;

        move_uploaded_file($_FILES['media']['tmp_name'], $uploadPath);
    }

    // Insert into database
    $sql = "INSERT INTO content 
            (title, body_content, description, content_type, status, staff_id, media, created_on)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param(
        "sssssis",
        $title,
        $body_content,
        $description,
        $content_type,
        $status,
        $staff_id,
        $mediaName
    );

    if ($stmt->execute()) {
        $success_message = "Content created successfully!";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
}
?>

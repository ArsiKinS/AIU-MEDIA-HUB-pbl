<?php
require('../login/connect.php');

$query = "SELECT 
    c.content_id,
    c.title,
    c.body_content,
    c.description,
    c.created_on,
    c.content_type,
    c.status,
    c.staff_id,
    m.MEDIA_PATH,
    m.CAPTION
FROM content c
LEFT JOIN media m ON c.content_id = m.CONTENT_ID
ORDER BY c.created_on DESC";

$result = $dbconnection->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);
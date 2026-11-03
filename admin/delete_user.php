<?php
require('../login/connect.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // --- STEP 1: DELETE LEVEL 3 (Dependents of Interaction Log) ---
    // These must go first because they point to INTERACTION_ID
    $dbconnection->query("DELETE FROM feedback WHERE USER_ID = $id");
    $dbconnection->query("DELETE FROM problem_report WHERE USER_ID = $id");

    // --- STEP 2: DELETE LEVEL 2 (The Interaction Log) ---
    // This is now safe to delete because Feedback and Reports are gone
    $dbconnection->query("DELETE FROM interaction_log WHERE USER_ID = $id");

    // --- STEP 3: DELETE LEVEL 1 (Direct User Dependents) ---
    $dbconnection->query("DELETE FROM reading_history WHERE user_id = $id");

    // --- STEP 4: DELETE THE ACTUAL USER ---
    $sql_user = "DELETE FROM media_hub_user WHERE id = $id";
    
    if ($dbconnection->query($sql_user)) {
        header("Location: admin.php?status=deleted");
        exit();
    } else {
        echo "Error deleting user: " . $dbconnection->error;
    }
} else {
    header("Location: admin.php");
    exit();
}
?>
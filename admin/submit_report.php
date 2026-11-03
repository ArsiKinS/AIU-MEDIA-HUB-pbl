<?php
// submit_report_valid_ids.php
require('../login/connect.php');

$success = $error = '';
$staff_id = $issue_type = $description = $resolution_notes = '';
$status = 'PENDING';

// Get all valid staff IDs
$staff_result = $dbconnection->query("SELECT staff_id, name, department FROM staff ORDER BY staff_id");
$staff_list = [];
while($row = $staff_result->fetch_assoc()) {
    $staff_list[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'] ?? '';
    $issue_type = $_POST['issue_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'PENDING';
    $resolution_notes = $_POST['resolution_notes'] ?? '';
    
    // Validate staff ID exists
    $valid_staff = false;
    foreach($staff_list as $staff) {
        if ($staff['staff_id'] == $staff_id) {
            $valid_staff = true;
            $staff_name = $staff['name'];
            break;
        }
    }
    
    if (!$valid_staff) {
        $error = "Invalid Staff ID! Please select from the list.";
    } elseif (empty($issue_type) || empty($description)) {
        $error = "Please fill all required fields!";
    } else {
        try {
            // Get next report ID
            $result = $dbconnection->query("SELECT MAX(STAFF_REPORT_ID) as max_id FROM staff_report");
            $row = $result->fetch_assoc();
            $next_id = ($row['max_id'] ? $row['max_id'] : 0) + 1;
            
            // Set resolved date
            if ($status === 'RESOLVED' || $status === 'APPROVED') {
                $resolved_date = "NOW()";
            } else {
                $resolved_date = "'0000-00-00 00:00:00'";
            }
            
            // Insert report
            $sql = "INSERT INTO staff_report 
                    (STAFF_REPORT_ID, STAFF_ID, ISSUE_TYPE, DESCRIPTION, STATUS, SUBMITTED_ON, RESOLVED_DATE, RESOLUTION_NOTES) 
                    VALUES ('$next_id', '$staff_id', '$issue_type', '$description', '$status', NOW(), $resolved_date, '$resolution_notes')";
            
            if ($dbconnection->query($sql)) {
                $success = "✅ Report #$next_id submitted for $staff_name (ID: $staff_id)!";
                $issue_type = $description = $resolution_notes = '';
                $status = 'PENDING';
            } else {
                $error = "Database error: " . $dbconnection->error;
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Report - Valid Staff IDs Only</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .message { padding: 12px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        textarea { resize: vertical; }
        button { padding: 12px 24px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-right: 10px; }
        button:hover { background: #45a049; }
        .btn-back { background: #6c757d; }
        .btn-back:hover { background: #5a6268; }
        .staff-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 13px; }
        .staff-item { padding: 5px 0; border-bottom: 1px solid #eee; }
        .staff-id { font-weight: bold; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Submit Staff Report (Valid IDs Only)</h2>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <!-- Staff Selection -->
            <div class="form-group">
                <label>Select Staff Member *</label>
                <select name="staff_id" required>
                    <option value="">-- Choose a staff member --</option>
                    <?php foreach($staff_list as $staff): ?>
                        <option value="<?php echo $staff['staff_id']; ?>"
                            <?php echo ($staff_id == $staff['staff_id']) ? 'selected' : ''; ?>>
                            ID: <?php echo $staff['staff_id']; ?> - <?php echo htmlspecialchars($staff['name']); ?> 
                            (<?php echo htmlspecialchars($staff['department']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size: 12px; color: #666; margin-top: 5px;">
                    Only valid staff IDs from database are shown
                </p>
            </div>
            
            <!-- Issue Type -->
            <div class="form-group">
                <label>Issue Type *</label>
                <select name="issue_type" required>
                    <option value="">-- Select Issue Type --</option>
                    <option value="TECHNICAL" <?php echo $issue_type=='TECHNICAL'?'selected':''; ?>>Technical Issue</option>
                    <option value="IT" <?php echo $issue_type=='IT'?'selected':''; ?>>IT Issue</option>
                    <option value="HR" <?php echo $issue_type=='HR'?'selected':''; ?>>HR Related</option>
                    <option value="SYSTEM" <?php echo $issue_type=='SYSTEM'?'selected':''; ?>>System Issue</option>
                    <option value="CONTENT" <?php echo $issue_type=='CONTENT'?'selected':''; ?>>Content Related</option>
                    <option value="OTHER" <?php echo $issue_type=='OTHER'?'selected':''; ?>>Other</option>
                </select>
            </div>
            
            <!-- Status -->
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="PENDING" <?php echo $status=='PENDING'?'selected':''; ?>>PENDING</option>
                    <option value="IN_PROGRESS" <?php echo $status=='IN_PROGRESS'?'selected':''; ?>>IN PROGRESS</option>
                    <option value="UNDER_REVIEW" <?php echo $status=='UNDER_REVIEW'?'selected':''; ?>>UNDER REVIEW</option>
                    <option value="RESOLVED" <?php echo $status=='RESOLVED'?'selected':''; ?>>RESOLVED</option>
                    <option value="APPROVED" <?php echo $status=='APPROVED'?'selected':''; ?>>APPROVED</option>
                </select>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="4" required placeholder="Describe the issue..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            
            <!-- Resolution Notes -->
            <div class="form-group">
                <label>Resolution Notes</label>
                <textarea name="resolution_notes" rows="3" placeholder="Add resolution details if resolved..."><?php echo htmlspecialchars($resolution_notes); ?></textarea>
                <p style="font-size: 12px; color: #666;">
                    Required only for RESOLVED or APPROVED status
                </p>
            </div>
            
            <!-- Buttons -->
            <div style="margin-top: 25px;">
                <button type="submit">Submit Report</button>
                <button type="button" class="btn-back" onclick="location.href='staff_activity.php'">Back to Staff</button>
            </div>
        </form>
        
        <!-- Staff List Preview -->
        <div class="staff-list">
            <h3>📋 Available Staff Members</h3>
            <p>Total: <?php echo count($staff_list); ?> staff members</p>
            <div style="max-height: 200px; overflow-y: auto; margin-top: 10px;">
                <?php foreach($staff_list as $staff): ?>
                    <div class="staff-item">
                        <span class="staff-id">ID: <?php echo $staff['staff_id']; ?></span> - 
                        <?php echo htmlspecialchars($staff['name']); ?> - 
                        <?php echo htmlspecialchars($staff['department']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 5px; font-size: 13px;">
            <p><strong>ℹ How it works:</strong></p>
            <ul style="margin: 10px 0 0 20px;">
                <li>Staff ID must be selected from valid database entries</li>
                <li>Resolved date auto-sets based on status</li>
                <li>Submitted date is always current date/time</li>
                <li>Report ID auto-increments</li>
            </ul>
        </div>
    </div>
</body>
</html>
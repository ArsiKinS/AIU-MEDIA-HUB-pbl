<?php
require('../login/connect.php');

$userData = null;
$userInteractions = null;
$userFeedbacks = null;
$userProblemReports = null;

// Handle insert new interaction
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_interaction'])) {
    $user_id = $_POST['user_id'];
    $interaction_type = $_POST['interaction_type'];
    $interaction_text = $_POST['interaction_text'];
    $content_id = $_POST['content_id'];
    
    // Get next interaction ID
    $sql = "SELECT MAX(INTERACTION_ID) as max_id FROM interaction_log";
    $result = $dbconnection->query($sql);
    $row = $result->fetch_assoc();
    $next_id = ($row['max_id'] ? $row['max_id'] + 1 : 1000);
    
    // Insert new interaction
    $sql = "INSERT INTO interaction_log (INTERACTION_ID, TIMESTAMP, INTERACTION_TYPE, INTERACTION_TEXT, CONTENT_ID, USER_ID) 
            VALUES (?, NOW(), ?, ?, ?, ?)";
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("issii", $next_id, $interaction_type, $interaction_text, $content_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: ?user_id=" . $user_id . "&success=interaction_added");
        exit();
    }
}

// Handle insert new feedback
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_feedback'])) {
    $user_id = $_POST['user_id'];
    $rating = $_POST['rating'];
    $feedback_text = $_POST['feedback_text'];
    $content_id = $_POST['content_id'];
    
    // Start transaction
    $dbconnection->begin_transaction();
    
    try {
        // First, create an interaction record for the feedback
        $sql = "SELECT MAX(INTERACTION_ID) as max_id FROM interaction_log";
        $result = $dbconnection->query($sql);
        $row = $result->fetch_assoc();
        $interaction_id = ($row['max_id'] ? $row['max_id'] + 1 : 1000);
        
        // Insert interaction for feedback
        $interaction_text = "Submitted feedback: " . substr($feedback_text, 0, 100) . "...";
        $sql = "INSERT INTO interaction_log (INTERACTION_ID, TIMESTAMP, INTERACTION_TYPE, INTERACTION_TEXT, CONTENT_ID, USER_ID) 
                VALUES (?, NOW(), 'FEEDBACK', ?, ?, ?)";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("isii", $interaction_id, $interaction_text, $content_id, $user_id);
        $stmt->execute();
        
        // Get next feedback ID
        $sql = "SELECT MAX(FEEDBACK_ID) as max_id FROM feedback";
        $result = $dbconnection->query($sql);
        $row = $result->fetch_assoc();
        $feedback_id = ($row['max_id'] ? $row['max_id'] + 1 : 1);
        
        // Insert feedback
        $sql = "INSERT INTO feedback (FEEDBACK_ID, RATING, FEEDBACK_TEXT, INTERACTION_ID, USER_ID) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("iisii", $feedback_id, $rating, $feedback_text, $interaction_id, $user_id);
        $stmt->execute();
        
        $dbconnection->commit();
        header("Location: ?user_id=" . $user_id . "&success=feedback_added");
        exit();
        
    } catch (Exception $e) {
        $dbconnection->rollback();
        $error = "Failed to add feedback: " . $e->getMessage();
    }
}

// Handle insert new problem report
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_problem'])) {
    $user_id = $_POST['user_id'];
    $issue_description = $_POST['issue_description'];
    $urgency_level = $_POST['urgency_level'];
    $content_id = $_POST['content_id'];
    
    // Start transaction
    $dbconnection->begin_transaction();
    
    try {
        // First, create an interaction record for the problem report
        $sql = "SELECT MAX(INTERACTION_ID) as max_id FROM interaction_log";
        $result = $dbconnection->query($sql);
        $row = $result->fetch_assoc();
        $interaction_id = ($row['max_id'] ? $row['max_id'] + 1 : 1000);
        
        // Insert interaction for problem report
        $interaction_text = "Submitted problem report: " . substr($issue_description, 0, 100) . "...";
        $sql = "INSERT INTO interaction_log (INTERACTION_ID, TIMESTAMP, INTERACTION_TYPE, INTERACTION_TEXT, CONTENT_ID, USER_ID) 
                VALUES (?, NOW(), 'REPORT', ?, ?, ?)";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("isii", $interaction_id, $interaction_text, $content_id, $user_id);
        $stmt->execute();
        
        // Get next problem report ID
        $sql = "SELECT MAX(PROBLEM_REPORT_ID) as max_id FROM problem_report";
        $result = $dbconnection->query($sql);
        $row = $result->fetch_assoc();
        $problem_id = ($row['max_id'] ? $row['max_id'] + 1 : 1);
        
        // Insert problem report
        $sql = "INSERT INTO problem_report (PROBLEM_REPORT_ID, ISSUE_DESCRIPTION, URGENCY_LEVEL, STATUS, INTERACTION_ID, USER_ID) 
                VALUES (?, ?, ?, 'OPEN', ?, ?)";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("issii", $problem_id, $issue_description, $urgency_level, $interaction_id, $user_id);
        $stmt->execute();
        
        $dbconnection->commit();
        header("Location: ?user_id=" . $user_id . "&success=problem_added");
        exit();
        
    } catch (Exception $e) {
        $dbconnection->rollback();
        $error = "Failed to add problem report: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    
    // Get user info
    $sql = "SELECT * FROM media_hub_user WHERE id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    if ($userData) {
        // Get interactions
        $sql = "SELECT * FROM interaction_log WHERE USER_ID = ? ORDER BY TIMESTAMP DESC";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userInteractions = [];
        while($row = $result->fetch_assoc()) {
            $userInteractions[] = $row;
        }
        
        // Get feedbacks
        $sql = "SELECT f.* FROM feedback f
                WHERE f.USER_ID = ? 
                ORDER BY f.FEEDBACK_ID DESC";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userFeedbacks = [];
        while($row = $result->fetch_assoc()) {
            $userFeedbacks[] = $row;
        }
        
        // Get problem reports
        $sql = "SELECT p.* FROM problem_report p
                WHERE p.USER_ID = ? 
                ORDER BY p.PROBLEM_REPORT_ID DESC";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userProblemReports = [];
        while($row = $result->fetch_assoc()) {
            $userProblemReports[] = $row;
        }
    }
}

// Handle delete requests
if (isset($_GET['delete'])) {
    $table = $_GET['table'];
    $id = $_GET['id'];
    $user_id = $_GET['user_id'];
    
    if ($table == 'interaction') {
        $sql = "DELETE FROM interaction_log WHERE INTERACTION_ID = ?";
    } elseif ($table == 'feedback') {
        // First get the interaction_id
        $sql = "SELECT INTERACTION_ID FROM feedback WHERE FEEDBACK_ID = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $interaction_id = $row['INTERACTION_ID'];
        
        // Delete feedback
        $sql = "DELETE FROM feedback WHERE FEEDBACK_ID = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Also delete the associated interaction
        $sql = "DELETE FROM interaction_log WHERE INTERACTION_ID = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $interaction_id);
        $stmt->execute();
        
        header("Location: ?user_id=" . $user_id);
        exit();
    } elseif ($table == 'problem') {
        // First get the interaction_id
        $sql = "SELECT INTERACTION_ID FROM problem_report WHERE PROBLEM_REPORT_ID = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $interaction_id = $row['INTERACTION_ID'];
        
        // Delete problem report
        $sql = "DELETE FROM problem_report WHERE PROBLEM_REPORT_ID = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Also delete the associated interaction
        $sql = "DELETE FROM interaction_log WHERE INTERACTION_ID = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $interaction_id);
        $stmt->execute();
        
        header("Location: ?user_id=" . $user_id);
        exit();
    }
    
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: ?user_id=" . $user_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        
        .search-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        input[type="number"], input[type="text"], select, textarea {
            padding: 8px;
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin: 5px;
        }
        
        textarea {
            width: 300px;
            height: 60px;
            resize: vertical;
        }
        
        button {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 5px;
        }
        
        button:hover {
            background: #0056b3;
        }
        
        .add-btn {
            background: #28a745;
        }
        
        .add-btn:hover {
            background: #218838;
        }
        
        .feedback-btn {
            background: #ffc107;
            color: #333;
        }
        
        .feedback-btn:hover {
            background: #e0a800;
        }
        
        .problem-btn {
            background: #dc3545;
        }
        
        .problem-btn:hover {
            background: #c82333;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 3px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 3px;
            margin: 10px 0;
            border: 1px solid #c3e6cb;
        }
        
        .user-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 3px;
            margin-bottom: 20px;
        }
        
        .section {
            margin: 20px 0;
        }
        
        .section h3 {
            background: #6c757d;
            color: white;
            padding: 10px;
            margin: 0;
            border-radius: 3px 3px 0 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background: #495057;
            color: white;
            padding: 10px;
            text-align: left;
        }
        
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .delete-btn:hover {
            background: #c82333;
        }
        
        .stats {
            display: flex;
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-box {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
        }
        
        .add-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }
        
        .add-form h4 {
            margin-top: 0;
            color: #333;
        }
        
        .form-row {
            margin: 10px 0;
        }
        
        .form-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        
        .tab-button {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .tab-button.active {
            background: #007bff;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .star-rating {
            display: inline-block;
            font-size: 24px;
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Activity Report</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                <?php 
                if ($_GET['success'] == 'interaction_added') echo 'New interaction added successfully!';
                elseif ($_GET['success'] == 'feedback_added') echo 'Feedback added successfully! (Interaction automatically created)';
                elseif ($_GET['success'] == 'problem_added') echo 'Problem report added successfully! (Interaction automatically created)';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="search-box">
            <form method="POST">
                <input type="number" name="user_id" placeholder="Enter User ID" required 
                       value="<?php echo isset($_POST['user_id']) ? $_POST['user_id'] : ''; ?>">
                <button type="submit">Search User</button>
            </form>
            <p style="margin-top: 10px; color: #666; font-size: 14px;">
                Try: 22, 23, 24 (Users with interaction data)
            </p>
        </div>
        
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user_id'])): ?>
            <?php if (!$userData): ?>
                <div class="error">
                    User ID "<?php echo $_POST['user_id']; ?>" not found!
                </div>
            <?php else: ?>
                
                <!-- User Information -->
                <div class="user-info">
                    <h2 style="margin: 0;"><?php echo $userData['username']; ?></h2>
                    <p style="margin: 5px 0;">Email: <?php echo $userData['email']; ?></p>
                    <p style="margin: 5px 0;">User Type: <?php echo $userData['user_type']; ?></p>
                    <p style="margin: 5px 0;">User ID: <?php echo $userData['id']; ?></p>
                </div>
                
                <!-- Statistics -->
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($userInteractions); ?></div>
                        <div class="stat-label">Interactions</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($userFeedbacks); ?></div>
                        <div class="stat-label">Feedback</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo count($userProblemReports); ?></div>
                        <div class="stat-label">Problem Reports</div>
                    </div>
                </div>
                
                <!-- Tab Buttons -->
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="showTab('addInteraction')">Add Interaction</button>
                    <button class="tab-button" onclick="showTab('addFeedback')">Add Feedback</button>
                    <button class="tab-button" onclick="showTab('addProblem')">Report Problem</button>
                </div>
                
                <!-- Add New Interaction Form -->
                <div id="addInteraction" class="tab-content active add-form">
                    <h4>➕ Add New Interaction for User <?php echo $userId; ?></h4>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                        <div class="form-row">
                            <span class="form-label">Interaction Type:</span>
                            <select name="interaction_type" required>
                                <option value="">Select Type</option>
                                <option value="SEARCH">SEARCH</option>
                                <option value="CLICK">CLICK</option>
                                <option value="VIEW">VIEW</option>
                                <option value="SHARE">SHARE</option>
                                <option value="LIKE">LIKE</option>
                                <option value="BOOKMARK">BOOKMARK</option>
                                <option value="DOWNLOAD">DOWNLOAD</option>
                                <option value="COMMENT">COMMENT</option>
                                <option value="RATE">RATE</option>
                                <option value="REPORT">REPORT</option>
                                <option value="FEEDBACK">FEEDBACK</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <span class="form-label">Interaction Text:</span>
                            <input type="text" name="interaction_text" placeholder="E.g., Searched for pricing information" required style="width: 300px;">
                        </div>
                        
                        <div class="form-row">
                            <span class="form-label">Content ID:</span>
                            <input type="number" name="content_id" placeholder="Content ID" min="1" required>
                        </div>
                        
                        <div class="form-row">
                            <button type="submit" name="add_interaction" class="add-btn">Add Interaction</button>
                        </div>
                    </form>
                </div>
                
                <!-- Add Feedback Form -->
                <div id="addFeedback" class="tab-content add-form">
                    <h4>⭐ Add Feedback for User <?php echo $userId; ?></h4>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                        <div class="form-row">
                            <span class="form-label">Rating:</span>
                            <select name="rating" required>
                                <option value="">Select Rating</option>
                                <option value="5">★★★★★ (5 - Excellent)</option>
                                <option value="4">★★★★☆ (4 - Good)</option>
                                <option value="3">★★★☆☆ (3 - Average)</option>
                                <option value="2">★★☆☆☆ (2 - Poor)</option>
                                <option value="1">★☆☆☆☆ (1 - Very Poor)</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <span class="form-label">Feedback Text:</span>
                            <textarea name="feedback_text" placeholder="Enter your feedback here..." required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <span class="form-label">Content ID:</span>
                            <input type="number" name="content_id" placeholder="Content ID for this feedback" min="1" required>
                        </div>
                        
                        <div class="form-row">
                            <button type="submit" name="add_feedback" class="feedback-btn">Submit Feedback</button>
                            <small style="color: #666;">Note: An interaction record will be automatically created</small>
                        </div>
                    </form>
                </div>
                
                <!-- Add Problem Report Form -->
                <div id="addProblem" class="tab-content add-form">
                    <h4>⚠️ Report Problem for User <?php echo $userId; ?></h4>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                        
                        <div class="form-row">
                            <span class="form-label">Issue Description:</span>
                            <textarea name="issue_description" placeholder="Describe the issue you're experiencing..." required></textarea>
                        </div>
                        
                        <div class="form-row">
                            <span class="form-label">Urgency Level:</span>
                            <select name="urgency_level" required>
                                <option value="">Select Urgency</option>
                                <option value="LOW">Low</option>
                                <option value="MEDIUM">Medium</option>
                                <option value="HIGH">High</option>
                                <option value="CRITICAL">Critical</option>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <span class="form-label">Content ID:</span>
                            <input type="number" name="content_id" placeholder="Content ID related to the problem" min="1" required>
                        </div>
                        
                        <div class="form-row">
                            <button type="submit" name="add_problem" class="problem-btn">Submit Problem Report</button>
                            <small style="color: #666;">Note: An interaction record will be automatically created</small>
                        </div>
                    </form>
                </div>
                
                <!-- 1. Interaction Log -->
                <div class="section">
                    <h3>Interaction Log (<?php echo count($userInteractions); ?>)</h3>
                    <?php if (!empty($userInteractions)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Content ID</th>
                                    <th>Timestamp</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userInteractions as $interaction): ?>
                                <tr>
                                    <td><?php echo $interaction['INTERACTION_ID']; ?></td>
                                    <td><?php echo $interaction['INTERACTION_TYPE']; ?></td>
                                    <td><?php echo $interaction['INTERACTION_TEXT']; ?></td>
                                    <td><?php echo $interaction['CONTENT_ID']; ?></td>
                                    <td><?php echo $interaction['TIMESTAMP']; ?></td>
                                    <td>
                                        <button class="delete-btn" 
                                                onclick="if(confirm('Delete this interaction?')) 
                                                window.location='?delete=1&table=interaction&id=<?php echo $interaction['INTERACTION_ID']; ?>&user_id=<?php echo $userId; ?>'">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">No interaction history found</div>
                    <?php endif; ?>
                </div>
                
                <!-- 2. Feedback -->
                <div class="section">
                    <h3>Feedback (<?php echo count($userFeedbacks); ?>)</h3>
                    <?php if (!empty($userFeedbacks)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Rating</th>
                                    <th>Feedback Text</th>
                                    <th>Interaction ID</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userFeedbacks as $feedback): ?>
                                <tr>
                                    <td><?php echo $feedback['FEEDBACK_ID']; ?></td>
                                    <td>
                                        <?php 
                                        echo str_repeat('★', $feedback['RATING']) . str_repeat('☆', 5 - $feedback['RATING']);
                                        echo " ({$feedback['RATING']}/5)";
                                        ?>
                                    </td>
                                    <td><?php echo $feedback['FEEDBACK_TEXT']; ?></td>
                                    <td><?php echo $feedback['INTERACTION_ID']; ?></td>
                                    <td>
                                        <button class="delete-btn" 
                                                onclick="if(confirm('Delete this feedback? (Associated interaction will also be deleted)')) 
                                                window.location='?delete=1&table=feedback&id=<?php echo $feedback['FEEDBACK_ID']; ?>&user_id=<?php echo $userId; ?>'">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">No feedback found</div>
                    <?php endif; ?>
                </div>
                
                <!-- 3. Problem Reports -->
                <div class="section">
                    <h3>Problem Reports (<?php echo count($userProblemReports); ?>)</h3>
                    <?php if (!empty($userProblemReports)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Issue Description</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>Interaction ID</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userProblemReports as $report): ?>
                                <tr>
                                    <td><?php echo $report['PROBLEM_REPORT_ID']; ?></td>
                                    <td><?php echo $report['ISSUE_DESCRIPTION']; ?></td>
                                    <td>
                                        <?php 
                                        $urgency_color = '';
                                        if ($report['URGENCY_LEVEL'] == 'HIGH' || $report['URGENCY_LEVEL'] == 'CRITICAL') {
                                            $urgency_color = 'color: #dc3545; font-weight: bold;';
                                        } elseif ($report['URGENCY_LEVEL'] == 'MEDIUM') {
                                            $urgency_color = 'color: #ffc107;';
                                        }
                                        ?>
                                        <span style="<?php echo $urgency_color; ?>">
                                            <?php echo $report['URGENCY_LEVEL']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $report['STATUS']; ?></td>
                                    <td><?php echo $report['INTERACTION_ID']; ?></td>
                                    <td>
                                        <button class="delete-btn" 
                                                onclick="if(confirm('Delete this problem report? (Associated interaction will also be deleted)')) 
                                                window.location='?delete=1&table=problem&id=<?php echo $report['PROBLEM_REPORT_ID']; ?>&user_id=<?php echo $userId; ?>'">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">No problem reports found</div>
                    <?php endif; ?>
                </div>
                
            <?php endif; ?>
        <?php endif; ?>

        <a href="admin.php">Admin</a>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
<?php
require('../login/connect.php');

$userData = null;
$userInfo = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    
    // 1. Get basic user info
    $sql = "SELECT * FROM media_hub_user WHERE id = ?";
    $stmt = $dbconnection->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    if ($userData) {
        $userType = $userData['user_type'];
        
        // 2. Get additional info based on user type
        if ($userType == 'Staff' || $userType == 'staff') {
            // Get staff info
            $sql = "SELECT * FROM aiu_staff WHERE user_id = ?";
            $stmt = $dbconnection->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userInfo = $result->fetch_assoc();
            $infoType = 'staff';
        } else {
            // Get student info
            $sql = "SELECT * FROM student WHERE user_id = ?";
            $stmt = $dbconnection->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $userInfo = $result->fetch_assoc();
            $infoType = 'student';
        }
        
        // 3. Get registration info
        $sql = "SELECT * FROM registration WHERE user_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $registrationInfo = $result->fetch_assoc();
        
        // 4. Get login statistics
        $sql = "SELECT 
                COUNT(*) as login_count,
                MAX(login_time) as last_login_time 
                FROM login WHERE user_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $loginStats = $result->fetch_assoc();
        
        // 5. Get last login details
        $sql = "SELECT * FROM login WHERE user_id = ? ORDER BY login_time DESC LIMIT 1";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $lastLogin = $result->fetch_assoc();
        
        // 6. Get reading history with content title
        $sql = "SELECT rh.*, c.title as content_title 
                FROM reading_history rh 
                LEFT JOIN content c ON rh.content_id = c.content_id 
                WHERE rh.user_id = ? 
                ORDER BY rh.action_time DESC 
                LIMIT 10";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $readingHistory = [];
        while($row = $result->fetch_assoc()) {
            $readingHistory[] = $row;
        }
        
        // 7. Get reading statistics
        $sql = "SELECT 
                COUNT(*) as total_readings,
                SUM(CASE WHEN action = 'VIEWED' THEN 1 ELSE 0 END) as total_views,
                SUM(CASE WHEN action = 'COMPLETED' THEN 1 ELSE 0 END) as total_completed
                FROM reading_history WHERE user_id = ?";
        $stmt = $dbconnection->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $readingStats = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        
        .search-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        input[type="number"] {
            padding: 10px;
            width: 200px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            padding: 10px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background: #0056b3;
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            border-left: 4px solid #c62828;
        }
        
        .user-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px 0;
            border: 1px solid #e0e0e0;
        }
        
        .user-header {
            padding: 20px;
            background: #007bff;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-id {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .user-type {
            background: #28a745;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .user-body {
            padding: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            width: 150px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
        }
        
        .section-title {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            margin: 30px 0 15px 0;
            border-radius: 5px;
        }
        
        .hint {
            text-align: center;
            color: #666;
            margin-top: 10px;
            font-size: 14px;
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
            border-radius: 5px;
            text-align: center;
            border-top: 4px solid;
        }
        
        .stat-box.login {
            border-color: #007bff;
        }
        
        .stat-box.reading {
            border-color: #28a745;
        }
        
        .stat-box.completed {
            border-color: #ffc107;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .reading-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .reading-table th {
            background: #6c757d;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .reading-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .reading-table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        
        .badge-viewed {
            background: #28a745;
        }
        
        .badge-completed {
            background: #007bff;
        }
        
        .badge-other {
            background: #6c757d;
        }
        
        .login-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #007bff;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .content-info {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .content-id {
            font-weight: bold;
            color: #333;
            min-width: 70px;
        }
        
        .content-title {
            color: #555;
            font-size: 14px;
        }
        
        .content-title a {
            color: #007bff;
            text-decoration: none;
        }
        
        .content-title a:hover {
            text-decoration: underline;
        }
        
        .no-title {
            color: #999;
            font-style: italic;
            font-size: 14px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        
        .back-link:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 User Profile Search</h1>
        
        <div class="search-box">
            <form method="POST" class="search-form">
                <input type="number" name="user_id" placeholder="Enter User ID" required 
                       value="<?php echo isset($_POST['user_id']) ? $_POST['user_id'] : ''; ?>">
                <button type="submit">Search</button>
            </form>
            <div class="hint">Try: 15 (Student), 17 (Student), 19 (Student), 23 (Staff), 24 (Staff)</div>
        </div>
        
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <?php if (!$userData): ?>
                <div class="error">
                    ❌ User ID "<?php echo $_POST['user_id']; ?>" not found!
                </div>
            <?php else: ?>
                
                <!-- User Card -->
                <div class="user-card">
                    <div class="user-header">
                        <div>
                            <h2 style="margin: 0;"><?php echo $userData['username']; ?></h2>
                            <p style="margin: 5px 0 0 0; opacity: 0.9;"><?php echo $userData['email']; ?></p>
                        </div>
                        <div style="text-align: right;">
                            <div class="user-id">ID: <?php echo $userData['id']; ?></div>
                            <div class="user-type" style="margin-top: 5px;">
                                <?php echo $userData['user_type']; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="user-body">
                        <!-- Statistics -->
                        <div class="stats">
                            <div class="stat-box login">
                                <div class="stat-number"><?php echo $loginStats['login_count'] ?? 0; ?></div>
                                <div class="stat-label">Total Logins</div>
                            </div>
                            <div class="stat-box reading">
                                <div class="stat-number"><?php echo $readingStats['total_readings'] ?? 0; ?></div>
                                <div class="stat-label">Total Readings</div>
                            </div>
                            <div class="stat-box completed">
                                <div class="stat-number"><?php echo $readingStats['total_completed'] ?? 0; ?></div>
                                <div class="stat-label">Completed</div>
                            </div>
                        </div>
                        
                        <!-- Basic Info -->
                        <div class="info-row">
                            <div class="info-label">Username:</div>
                            <div class="info-value"><?php echo $userData['username']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?php echo $userData['email']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Location:</div>
                            <div class="info-value"><?php echo $userData['location']; ?></div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Account Created:</div>
                            <div class="info-value"><?php echo $userData['created_at']; ?></div>
                        </div>
                        
                        <!-- Login Info -->
                        <div class="section-title">🔐 Login Information</div>
                        
                        <div class="info-row">
                            <div class="info-label">Total Logins:</div>
                            <div class="info-value">
                                <span class="login-badge"><?php echo $loginStats['login_count'] ?? 0; ?> times</span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="info-label">Last Login:</div>
                            <div class="info-value">
                                <?php 
                                if (!empty($loginStats['last_login_time'])) {
                                    echo $loginStats['last_login_time'];
                                } else {
                                    echo '<span style="color: #666; font-style: italic;">Never logged in</span>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- User Type Specific Info -->
                        <?php if ($userInfo && $infoType == 'staff'): ?>
                            <div class="section-title">👨‍💼 Staff Information</div>
                            
                            <div class="info-row">
                                <div class="info-label">Staff Card ID:</div>
                                <div class="info-value"><?php echo $userInfo['staff_card_id']; ?></div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Position:</div>
                                <div class="info-value">
                                    <?php 
                                    $position = $userInfo['position'];
                                    $color = ($position == 'lecture') ? '#007bff' : 
                                            (($position == 'staff') ? '#28a745' : '#6c757d');
                                    ?>
                                    <span style="background: <?php echo $color; ?>; 
                                          color: white; padding: 3px 10px; border-radius: 15px;">
                                        <?php echo ucfirst($position); ?>
                                    </span>
                                </div>
                            </div>
                            
                        <?php elseif ($userInfo && $infoType == 'student'): ?>
                            <div class="section-title">🎓 Student Information</div>
                            
                            <?php if (isset($userInfo['phone'])): ?>
                            <div class="info-row">
                                <div class="info-label">Phone:</div>
                                <div class="info-value"><?php echo $userInfo['phone']; ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($userInfo['occupation'])): ?>
                            <div class="info-row">
                                <div class="info-label">Occupation:</div>
                                <div class="info-value">
                                    <span style="background: #17a2b8; color: white; padding: 3px 10px; border-radius: 15px;">
                                        <?php echo $userInfo['occupation']; ?>
                                    </span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Show other student fields -->
                            <?php 
                            foreach ($userInfo as $key => $value) {
                                if (!in_array($key, ['user_id', 'phone', 'occupation'])) {
                                    echo '<div class="info-row">';
                                    echo '<div class="info-label">' . ucfirst(str_replace('_', ' ', $key)) . ':</div>';
                                    echo '<div class="info-value">' . $value . '</div>';
                                    echo '</div>';
                                }
                            }
                            ?>
                            
                        <?php else: ?>
                            <div class="section-title">
                                <?php echo $userData['user_type']; ?> Information
                            </div>
                            <div class="info-row">
                                <div class="info-label">Details:</div>
                                <div class="info-value" style="color: #666;">
                                    No additional information found in database
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Registration Info -->
                        <?php if ($registrationInfo): ?>
                            <div class="section-title">📝 Registration Information</div>
                            
                            <div class="info-row">
                                <div class="info-label">Registration Date:</div>
                                <div class="info-value"><?php echo $registrationInfo['registration_date']; ?></div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Status:</div>
                                <div class="info-value">
                                    <?php if ($registrationInfo['is_active'] == 1): ?>
                                        <span style="color: green;">● Active</span>
                                    <?php else: ?>
                                        <span style="color: red;">● Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Registration Type:</div>
                                <div class="info-value"><?php echo $registrationInfo['registration_type']; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Reading History -->
                        <?php if (!empty($readingHistory) && isset($readingStats)): ?>
                            <div class="section-title">📚 Reading History (Last 10)</div>
                            
                            <!-- Reading History Table -->
                            <table class="reading-table">
                                <thead>
                                    <tr>
                                        <th width="40%">Content</th>
                                        <th width="20%">Action</th>
                                        <th width="40%">Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($readingHistory as $reading): ?>
                                    <tr>
                                        <td>
                                            <div class="content-info">
                                                <div class="content-id">
                                                    #<?php echo $reading['content_id']; ?>
                                                </div>
                                                <div class="content-title">
                                                    <?php if (!empty($reading['content_title'])): ?>
                                                        <a href="view_content.php?id=<?php echo $reading['content_id']; ?>" 
                                                           title="View content details">
                                                            <?php echo htmlspecialchars($reading['content_title']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="no-title">Title not available</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $action = $reading['action'];
                                            $badgeClass = '';
                                            if ($action == 'VIEWED') {
                                                $badgeClass = 'badge-viewed';
                                            } elseif ($action == 'COMPLETED') {
                                                $badgeClass = 'badge-completed';
                                            } else {
                                                $badgeClass = 'badge-other';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo $reading['action']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $reading['action_time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <?php if (count($readingHistory) == 10): ?>
                            <div style="text-align: center; margin-top: 10px; color: #666; font-size: 14px;">
                                Showing last 10 readings. More available in database.
                            </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="section-title">📚 Reading History</div>
                            <div class="info-row">
                                <div class="info-label">Reading Activity:</div>
                                <div class="info-value" style="color: #666;">
                                    No reading history found for this user.
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
            <?php endif; ?>
        <?php endif; ?>
        
        <a href="admin.php" class="back-link">← Back to Admin Dashboard</a>
    </div>
</body>
</html>
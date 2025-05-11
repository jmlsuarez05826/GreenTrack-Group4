<!DOCTYPE html>
<html>
<head>
    <title>GreenTrack - Admin Account</title>
    <link rel="stylesheet" type="text/css" href="Feedback.css?v=<?php echo time(); ?>">
</head>
<body>

<?php 
session_start();
require_once 'procedure.php';

// Get selected message type
$typeFilter = $_GET['type'] ?? 'all';

// Get messages using the Crud class
$messages = $crud->getAllMessages($typeFilter);
?>

<header>
        <div class="logo-container">
        <img src="LOGO.png" class="logo" />
        <h1><span>GREEN</span>TRACK</h1>
        </div>
         <div class="nav-links">
             <button>Admin Panel</button>
         </div>
</header>

<div class="container">
    <div class="sidebar">
    <ul>
             <li><a  href="Dashboard.php"> 📊Dashboard</a></li>
 </ul>
             <h3>Manage Account</h3>
             <ul>
             <li><a href="Admin_Page.php">👤 Admin Account</a></li>
             <li><a href="Manage.php">👥 Manage Account</a></li>
 </ul>
 
 <h3>View & Approval</h3>
             <ul>
             <li><a href="Tree_submission.php">🌳 Tree submission</a></li>
             <li><a href="Total_trees.php">🌲 Total trees planted</a></li>
 </ul>
        <h3>Feedback</h3>
        <ul>
            <li><a class="active" href="#">💬 View Feedbacks</a></li>
        </ul>
        <div class="sidebar1">
            <ul><li><a class="logout" href="Homepage.php">Logout</a></li></ul>
        </div>
    </div>

    <div class="form-wrapper">
        <div class="form-header">Messages</div>

        <!-- Tabs for Filtering -->
        <div class="tab-buttons">
            <a href="?type=all" class="tab-btn <?= $typeFilter == 'all' ? 'active-tab' : '' ?>">All</a>
            <a href="?type=General Inquiry" class="tab-btn <?= $typeFilter == 'General Inquiry' ? 'active-tab' : '' ?>">General</a>
            <a href="?type=Support" class="tab-btn <?= $typeFilter == 'Support' ? 'active-tab' : '' ?>">Support</a>
            <a href="?type=Feedback" class="tab-btn <?= $typeFilter == 'Feedback' ? 'active-tab' : '' ?>">Feedback</a>
        </div>

        <div class="form-container">
            <div class="message-list">
                <?php 
                if (!empty($messages)): 
                    foreach($messages as $row):
                ?>
                    <div class="message-card">
                        <strong>Name:</strong> <?= htmlspecialchars($row['name']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($row['email']) ?><br>
                        <strong>Type:</strong> <?= ucfirst($row['message_type']) ?><br>
                        <strong>Date:</strong> <?= $row['date_sent'] ?><br>
                        <strong>Message:</strong>
                        <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                    </div>
                <?php endforeach; else: ?>
                    <p class="no-message">No messages found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>

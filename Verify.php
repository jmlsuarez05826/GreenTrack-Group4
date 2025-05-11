<!DOCTYPE html>
<html>
<head>
    <title>GreenTrack - Admin Account</title>
    <link rel="stylesheet" type="text/css" href="Verify.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="header">
        <div class="logo">GREENTRACK</div>
        <div class="nav-links">
            <button>Admin Panel</button>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
        <ul>
             <li><a  href="Dashboard.php"> 📊Dashboard</a></li>
 </ul>
            <h3>Manage Account</h3>
            <ul>
            <li><a  href="Admin_Page.php">👤 Admin Account</a></li>
            <li><a href="Manage.php">👥 Manage Account</a></li>
</ul>

            <h3>View</h3>
            <ul>
            <li><a href="Tree_submission.php">🌳 Tree submission</a></li>
            <li><a href="Total_trees.php">🌲 Total trees planted</a></li>
</ul>
            <h3>Approval</h3>
            <ul>
            <li><a class="active" href="" >✔ Verify submission</a></li>
            </ul>
            <h3>Feedback</h3>
            <ul>
            <li><a href="Feedback.php">💬 View Feedbacks</a></li>
            </ul>
            <div class="sidebar1">
             <ul>
             <li><a class ="logout" href="Homepage.php">Logout</a></li>
             </ul>
</div>
        </div>
        <div class="main-content">
    <div class="account-box">
        <h2 class="myAccount">Verify submission</h2>
        <div class="account-box-1">
        
        </div>
    </div>
</div>

</div>

</body>
</html>
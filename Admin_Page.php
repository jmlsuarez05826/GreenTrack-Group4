<!DOCTYPE html>
<html>
<head>
    <title>GreenTrack - Admin Account</title>
    <link rel="stylesheet" type="text/css" href="Admin.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="header">
        <div class="logo">GREENTRACK</div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">About</a>
            <a href="#">How It Works</a>
            <a href="#">Contact</a>
            <button>Admin Panel</button>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <h3>Manage Account</h3>
            <ul>
            <li><a  class="active" href="Admin_Page.php">👤 Admin Account</a></li>
            <li><a href="#">👥 Volunteer Account</a></li>
</ul>

            <h3>View</h3>
            <ul>
            <li><a href="Tree_submission.php">🌳 Tree submission</a></li>
            <li><a href="Total_trees.php">🌲 Total trees planted</a></li>
            <li><a href="Co2.php">🌍 CO₂ reduced</a></li>
            <li><a href="Top.php">⭐ Top contributors</a></li>
</ul>
            <h3>Approval</h3>
            <ul>
            <li><a href="Verify.php">✔ Verify submission</a></li>
            </ul>
            <h3>Feedback</h3>
            <ul>
            <li><a href="Feedback.php">💬 View Feedbacks</a></li>
            </ul>
        </div>
        <div class="main-content">
    <div class="account-box">
        <h2 class="myAccount">My Account</h2>
        <div class="account-box-1">
            <div class="account-info">
                <img src="#" alt="Profile">
                <div>
                    <p><b>Username</b>: adminaccount </p>
                    <p><b>Email</b>: adminaccount123@gmail.com</p>
                    <p><b>Role</b>: Admin</p>
                    <p><b>Password</b>: password123</p>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

</body>
</html>

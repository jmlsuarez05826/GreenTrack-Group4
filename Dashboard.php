<!DOCTYPE html>
 <html>
 <head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>GreenTrack - Admin Account</title>
     <link rel="stylesheet" type="text/css" href="Dashboard.css?v=<?php echo time(); ?>">
 </head>
 <body>

 <?php
require_once 'procedure.php';
session_start();

// Get total trees and CO2
$totalStats = $crud->getTotalStats();
$totalTrees = $totalStats['TotTree'] ?? 0;
$totalCO2 = $totalStats['TotCo2'] ?? 0;

// Get leaderboard data
$leaderboardData = $crud->getLeaderboard();
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
            <li><a class="active" href="Dashboard.php"> 📊Dashboard</a></li>
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
             <li><a href="Feedback.php">💬 View Feedbacks</a></li>
             </ul>
             <div class="sidebar1">
             <ul>
                <li><a class="logout" href="Homepage.php">Logout</a></li>
             </ul>
</div>
         </div>

         <div class="tree-dashboard">
    <div class="box-1">
        <h2>Total Trees Planted</h2>
        <p><?php echo $totalTrees; ?></p>
    </div>
    <div class="box-2">
        <h2>Total CO₂ Offset (kg)</h2>
        <p><?php echo $totalCO2; ?></p>
    </div>
    <div class="box-3">
            <h2>Active Volunteers</h2>
            <p><?php echo count($leaderboardData); ?></p>
    </div>
        
    <div class="leaderboard">
            <h2>Volunteer Leaderboard</h2>
<ul class="leaderboard-list">
                <?php 
                $rank = 1;
                foreach ($leaderboardData as $user): 
                    $medal = '';
                    if ($rank == 1) $medal = '🥇';
                    elseif ($rank == 2) $medal = '🥈';
                    elseif ($rank == 3) $medal = '🥉';
                ?>
                <li>
                    <span class="rank"><?php echo $medal . $rank; ?></span>
                    <div class="user-info">
                        <span class="name"><?php echo htmlspecialchars($user['username']); ?></span>
                        <span class="email"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="user-stats">
                        <span class="trees">🌳 <?php echo $user['total_trees']; ?> trees</span>
                        <span class="co2">🌱 <?php echo $user['total_co2']; ?> kg CO₂</span>
                    </div>
  </li>
                <?php 
                $rank++;
                endforeach; 
                ?>
</ul>
        </div>
    </div>
</div>
 
 </body>
 </html>

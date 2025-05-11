<!DOCTYPE html>
<html>
<head>
    <title>GreenTrack - Admin Account</title>
    <link rel="stylesheet" type="text/css" href="Total.css?v=<?php echo time(); ?>">
</head>
<body>

<?php 
session_start();
require_once 'procedure.php';

// Get total trees for each location
$locations = [
    'LIPA' => 0,
    'SAN JUAN' => 0,
    'BATANGAS CITY' => 0,
    'TAAL' => 0,
    'NASUGBU' => 0,
    'OTHER' => 0
];

// Get total trees for each location using the Crud class
$treeLocations = $crud->getAllTreePlantingLocations();
foreach ($treeLocations as $row) {
    $location = strtoupper($row['location']);
    // Use total_trees_planted from the stored procedure result
    $number = $row['total_trees_planted'];
    if (isset($locations[$location])) {
        $locations[$location] += $number;
    } else {
        $locations['OTHER'] += $number;
    }
}

// Get total CO2
$totalStats = $crud->getTotalStats();
$totalCO2 = $totalStats['total_co2'] ?? 0;
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
            <li><a href="Dashboard.php"> 📊Dashboard</a></li>
        </ul>
        <h3>Manage Account</h3>
        <ul>
            <li><a href="Admin_Page.php">👤 Admin Account</a></li>
            <li><a href="Manage.php">👥 Manage Account</a></li>
        </ul>
        <h3>View & Approval</h3>
        <ul>
            <li><a href="Tree_submission.php">🌳 Tree submission</a></li>
            <li><a class="active" href="Total_trees.php">🌲 Total trees planted</a></li>
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

    <div class="main-content">
        <div class="total-trees-container">
            <div class="total-trees-header">
                Total Trees Planted
            </div>
            <div class="location-grid">
                <!-- LIPA -->
                <div class="location-box lipa">
                    <div class="location-name">LIPA</div>
                    <div class="tree-count"><?php echo $locations['LIPA']; ?></div>
                    <div class="location-description">Trees planted in LIPA</div>
                </div>

                <!-- SAN JUAN -->
                <div class="location-box san-juan">
                    <div class="location-name">SAN JUAN</div>
                    <div class="tree-count"><?php echo $locations['SAN JUAN']; ?></div>
                    <div class="location-description">Trees planted in SAN JUAN</div>
                </div>

                <!-- BATANGAS CITY -->
                <div class="location-box batangas-city">
                    <div class="location-name">BATANGAS CITY</div>
                    <div class="tree-count"><?php echo $locations['BATANGAS CITY']; ?></div>
                    <div class="location-description">Trees planted in BATANGAS CITY</div>
                </div>

                <!-- TAAL -->
                <div class="location-box taal">
                    <div class="location-name">TAAL</div>
                    <div class="tree-count"><?php echo $locations['TAAL']; ?></div>
                    <div class="location-description">Trees planted in TAAL</div>
                </div>

                <!-- NASUGBU -->
                <div class="location-box nasugbu">
                    <div class="location-name">NASUGBU</div>
                    <div class="tree-count"><?php echo $locations['NASUGBU']; ?></div>
                    <div class="location-description">Trees planted in NASUGBU</div>
                </div>

                <!-- OTHER -->
                <div class="location-box other">
                    <div class="location-name">Other</div>
                    <div class="tree-count"><?php echo $locations['OTHER']; ?></div>
                    <div class="location-description">Trees planted in other locations</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard - Total Trees Planted</title>
  <link rel="stylesheet" type="text/css" href="totaltrees.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php
require_once "database.php";
require_once "procedure.php";

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Function to get user's trees by location
function getUserTreesByLocation($conn, $userId) {
    $locations = array(
        'LIPA' => 0,
        'SAN JUAN' => 0,
        'BATANGAS CITY' => 0,
        'TAAL' => 0,
        'NASUGBU' => 0,
        'OTHER' => 0
    );

    $totalTrees = 0;
    
    // Prepare and execute the stored procedure
    $stmt = $conn->prepare("CALL GetTreePlantingLocations(?)");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $location = strtoupper($row['location']);
                $count = $row['number'];
                $totalTrees += $count;
                
                if (array_key_exists($location, $locations)) {
                    $locations[$location] += $count;
                } else {
                    $locations['OTHER'] += $count;
                }
            }
            $result->free();
        }
        $stmt->close();
        $conn->next_result();
    }

    return array('locations' => $locations, 'total' => $totalTrees);
}

// Get user ID from session
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get the data
$data = getUserTreesByLocation($conn, $userId);
$locations = $data['locations'];
$totalTrees = $data['total'];
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
            <h3>Manage Account</h3>
            <li><a href="volunteer.php"> 👤 My Account</a></li>
          </ul>
          <ul>
            <h3>Submission</h3>
             <li><a href="treeplantingform.php">📄 Tree Planting Form</a></li>
             <li><a href="viewsubmission.php">🌐 View Submission</a></li>
             <li><a href="editsubmission.php">🌐 Edit Submission</a></li>
             <li><a href="cancel.php">🌐 Cancel Submission</a></li>
          </ul>
          <h3>View</h3>
          <ul>
            <li><a class="active" href="totaltrees.php">🌐 Total Trees Planted</a></li>
             <li><a href="estimate.php">🌐 CO₂ Absorbed Estimate</a></li>
          </ul>
          <div class="sidebar1">
          <ul>
                <li><a class="logout" href="Homepage.php">Logout</a></li>
          </ul>
      </div>
</div>

    <div class="main-content">
        <div class="stats-overview">
            <div class="total-trees-card">
                <i class="fas fa-tree"></i>
                <div class="stats-info">
                    <h3>My Total Trees Planted</h3>
                    <p class="count"><?php echo number_format($totalTrees); ?></p>
                </div>
            </div>
        </div>

        <div class="location-grid">
            <div class="location-box">
                <div class="location-header">
                    <h3>LIPA</h3>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="tree-count">
                    <span class="number"><?php echo number_format($locations['LIPA']); ?></span>
                    <span class="label">Trees</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $totalTrees > 0 ? ($locations['LIPA'] / $totalTrees) * 100 : 0; ?>%"></div>
                </div>
                <div class="percentage">
                    <?php echo $totalTrees > 0 ? round(($locations['LIPA'] / $totalTrees) * 100, 1) : 0; ?>%
                </div>
            </div>

            <div class="location-box">
                <div class="location-header">
                    <h3>SAN JUAN</h3>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="tree-count">
                    <span class="number"><?php echo number_format($locations['SAN JUAN']); ?></span>
                    <span class="label">Trees</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $totalTrees > 0 ? ($locations['SAN JUAN'] / $totalTrees) * 100 : 0; ?>%"></div>
                </div>
                <div class="percentage">
                    <?php echo $totalTrees > 0 ? round(($locations['SAN JUAN'] / $totalTrees) * 100, 1) : 0; ?>%
                </div>
            </div>

            <div class="location-box">
                <div class="location-header">
                    <h3>BATANGAS CITY</h3>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="tree-count">
                    <span class="number"><?php echo number_format($locations['BATANGAS CITY']); ?></span>
                    <span class="label">Trees</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $totalTrees > 0 ? ($locations['BATANGAS CITY'] / $totalTrees) * 100 : 0; ?>%"></div>
                </div>
                <div class="percentage">
                    <?php echo $totalTrees > 0 ? round(($locations['BATANGAS CITY'] / $totalTrees) * 100, 1) : 0; ?>%
                </div>
            </div>

            <div class="location-box">
                <div class="location-header">
                    <h3>TAAL</h3>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="tree-count">
                    <span class="number"><?php echo number_format($locations['TAAL']); ?></span>
                    <span class="label">Trees</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $totalTrees > 0 ? ($locations['TAAL'] / $totalTrees) * 100 : 0; ?>%"></div>
                </div>
                <div class="percentage">
                    <?php echo $totalTrees > 0 ? round(($locations['TAAL'] / $totalTrees) * 100, 1) : 0; ?>%
                </div>
            </div>

            <div class="location-box">
                <div class="location-header">
                    <h3>NASUGBU</h3>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="tree-count">
                    <span class="number"><?php echo number_format($locations['NASUGBU']); ?></span>
                    <span class="label">Trees</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $totalTrees > 0 ? ($locations['NASUGBU'] / $totalTrees) * 100 : 0; ?>%"></div>
                </div>
                <div class="percentage">
                    <?php echo $totalTrees > 0 ? round(($locations['NASUGBU'] / $totalTrees) * 100, 1) : 0; ?>%
                </div>
            </div>

            <div class="location-box">
                <div class="location-header">
                    <h3>OTHER</h3>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="tree-count">
                    <span class="number"><?php echo number_format($locations['OTHER']); ?></span>
                    <span class="label">Trees</span>
                </div>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo $totalTrees > 0 ? ($locations['OTHER'] / $totalTrees) * 100 : 0; ?>%"></div>
                </div>
                <div class="percentage">
                    <?php echo $totalTrees > 0 ? round(($locations['OTHER'] / $totalTrees) * 100, 1) : 0; ?>%
                </div>
            </div>
        </div>
    </div>
  </div>
</body>
</html>


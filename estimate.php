<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard - CO₂ Estimate</title>
  <link rel="stylesheet" type="text/css" href="estimate.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php
require_once "database.php";
require_once "procedure.php";

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Function to get user's CO2 data
function getUserCO2Data($conn, $userId) {
    $co2Data = array(
        'total' => 0,
        'by_location' => array()
    );

    $stmt = $conn->prepare("CALL GetTreePlantingLocations(?)");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add to total
                $co2Data['total'] += $row['total_co2'];

                // Group by location
                $location = $row['location'];
                if (!isset($co2Data['by_location'][$location])) {
                    $co2Data['by_location'][$location] = 0;
                }
                $co2Data['by_location'][$location] += $row['total_co2'];
            }
            $result->free();
        }
        $stmt->close();
        $conn->next_result();
    }

    return $co2Data;
}

// Get user ID from session
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get the data
$co2Data = getUserCO2Data($conn, $userId);
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
            <li><a href="totaltrees.php">🌐 Total Trees Planted</a></li>
            <li><a class="active" href="estimate.php">🌐 CO₂ Absorbed Estimate</a></li>
        </ul>
        <div class="sidebar1">
            <ul>
                <li><a class="logout" href="Homepage.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="stats-overview">
            <div class="total-co2-card">
                <i class="fas fa-leaf"></i>
                <div class="stats-info">
                    <h3>Total CO₂ Absorbed</h3>
                    <p class="count"><?php echo number_format($co2Data['total'], 2); ?> kg</p>
                </div>
            </div>
        </div>

        <div class="data-grid">
            <div class="data-section">
                <h2>CO₂ Absorption by Location</h2>
                <div class="data-cards">
                    <?php foreach ($co2Data['by_location'] as $location => $co2): ?>
                    <div class="data-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($location); ?></h3>
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="card-value">
                            <span class="number"><?php echo number_format($co2, 2); ?></span>
                            <span class="unit">kg CO₂</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo ($co2 / $co2Data['total']) * 100; ?>%"></div>
                        </div>
                        <div class="percentage">
                            <?php echo round(($co2 / $co2Data['total']) * 100, 1); ?>%
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard - View Submission</title>
  <link rel="stylesheet" type="text/css" href="viewsubmission.css?v=<?php echo time(); ?>">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
<?php 
session_start();
require_once 'database.php';
require_once 'procedure.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
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
      <li><a class="active" href="viewsubmission.php">🌐 View Submission</a></li>
      <li><a href="editsubmission.php">🌐 Edit Submission</a></li>
      <li><a href="cancel.php">🌐 Cancel Submission</a></li>
    </ul>
    <h3>View</h3>
    <ul>
      <li><a href="totaltrees.php">🌐 Total Trees Planted</a></li>
      <li><a href="estimate.php">🌐 CO₂ Absorbed Estimate</a></li>
    </ul>
    <div class="sidebar1">
      <ul>
        <li><a class="logout" href="Homepage.php">Logout</a></li>
      </ul>
    </div>
  </div>

  <div class="form-wrapper">
    
  <div class="form-header">View Submission</div>

  <form method="GET" id="filterForm" class="filter-form"> 
  <select name="year" onchange="document.getElementById('filterForm').submit();">
    <option value="">Select Year</option>
    <?php
    $currentYear = date('Y');
    $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear; 
    for ($y = 2020; $y <= $currentYear; $y++) {
        $selected = ($selectedYear == $y) ? 'selected' : '';
        echo "<option value='$y' $selected>$y</option>";
    }
    ?>
  </select>

  <select name="month" onchange="document.getElementById('filterForm').submit();">
    <option value="">Select Month</option>
    <?php
    $currentMonth = date('n'); 
    $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : $currentMonth; 
    for ($m = 1; $m <= 12; $m++) {
        $monthName = date('F', mktime(0, 0, 0, $m, 10));
        $selected = ($selectedMonth == $m) ? 'selected' : '';
        echo "<option value='$m' $selected>$monthName</option>";
    }
    ?>
  </select>
</form>


<div class="form-container">

<table border="1" cellpadding="10" cellspacing="0">
  <thead>
    <tr>
      <th>Username</th>
      <th>Tree Type</th>
      <th>Date</th>
      <th>Location</th>
      <th>Total CO₂</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>

<?php
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$year = isset($_GET['year']) ? (int)$_GET['year'] : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;

// Get total count
$countQuery = "CALL CountSubmissions()";
$countResult = $conn->query($countQuery);

if ($countResult) {
    $totalRow = $countResult->fetch_assoc();
    $total = $totalRow['total'];
    $pages = ceil($total / $limit);

    $countResult->free();
    $conn->next_result();
}

// Get submissions
$stmt = $conn->prepare("CALL ViewSubmissions(?, ?, ?, ?, ?)");
$stmt->bind_param("iiiii", $start, $limit, $year, $month, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tree_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['total_Co2']) . "</td>";

        $status = htmlspecialchars($row['status']);
        $color = '';
        
        if ($status == 'Pending') {
            $color = 'color: #FFA500;'; // Yellow/Orange
        } elseif ($status == 'Approved') {
            $color = 'color: #28a745;'; // Green
        } elseif ($status == 'Disapproved') {
            $color = 'color: #dc3545;'; // Red
        }
        
        echo "<td style='$color font-weight: bold;'>$status</td>";
        
        // Create a data object for the modal
        $modalData = array(
            'username' => $row['username'],
            'email' => $row['email'],
            'tree_type' => $row['tree_type'],
            'number' => $row['number'],
            'date_planted' => $row['created_at'],
            'location' => $row['location'],
            'image_path' => $row['image_path'],
            'group_members' => $row['group_members'] ?? 'N/A'
        );
        
        echo "<td><button class='view-button' onclick='openModal(" . json_encode($modalData) . ")'>View</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7'>No submissions found.</td></tr>";
}

$defaultYear = date('Y');
$defaultMonth = date('n');

$year = isset($_GET['year']) ? (int)$_GET['year'] : $defaultYear;
$month = isset($_GET['month']) ? (int)$_GET['month'] : $defaultMonth;

$stmt->close();
$conn->next_result();
?>

  </tbody>
</table>

</div>



<div class="pagination">
  <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page-1; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>">&laquo; Previous</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $pages; $i++): ?>
      <a class="<?php if ($i == $page) echo 'active'; ?>" href="?page=<?php echo $i; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>"><?php echo $i; ?></a>
  <?php endfor; ?>

  <?php if ($page < $pages): ?>
      <a href="?page=<?php echo $page+1; ?>&year=<?php echo $year; ?>&month=<?php echo $month; ?>">Next &raquo;</a>
  <?php endif; ?>
</div>


</div>
</div>


<div id="confirmationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="margin">
            <!-- TOP HEADER -->
            <div class="modal-header">
                <button onclick="closeModal()" class="x-button">&times;</button>
                <img src="LOGO.png" alt="GreenTrack Logo" class="logo">
                <h1>GreenTrack System</h1>
                <h4>Tree Planting Activity Report</h4>
                <hr class="divider">
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <div class="form-section">
                    <h3>Activity Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Date of Planting:</strong>
                            <span id="dateDisplay"></span>
                        </div>
                        <div class="info-item">
                            <strong>Location:</strong>
                            <span id="locationDisplay"></span>
                        </div>
                        <div class="info-item">
                            <strong>Tree Type:</strong>
                            <span id="treeTypeDisplay"></span>
                        </div>
                        <div class="info-item">
                            <strong>Number of Trees:</strong>
                            <span id="numberDisplay"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Volunteer Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Username:</strong>
                            <span id="usernameDisplay"></span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <span id="emailDisplay"></span>
                        </div>
                        <div class="info-item">
                            <strong>Group Members:</strong>
                            <span id="groupMembersDisplay"></span>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Proof of Activity</h3>
                    <img id="imageDisplay" src="" alt="Proof Image" class="image-preview">
                </div>

                <div class="footer">
                    <p>This document was generated by GreenTrack System</p>
                    <p>Date Generated: <span id="currentDate"></span></p>
                </div>

                <!-- Action Buttons -->
                <div class="modal-footer">
                    <button onclick="printModal()" class="action-btn print-btn">
                        <i class="print-icon">🖨️</i> Print
                    </button>
                    <button onclick="saveAsPDF()" class="action-btn save-btn">
                        <i class="save-icon">💾</i> Save as PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
function openModal(data) {
    document.getElementById('usernameDisplay').innerText = data.username || 'N/A';
    document.getElementById('emailDisplay').innerText = data.email || 'N/A';
    document.getElementById('groupMembersDisplay').innerText = data.group_members || 'N/A';
    document.getElementById('treeTypeDisplay').innerText = data.tree_type || 'N/A';
    document.getElementById('numberDisplay').innerText = data.number || 'N/A';
    document.getElementById('dateDisplay').innerText = data.date_planted || 'N/A';
    document.getElementById('locationDisplay').innerText = data.location || 'N/A';
    document.getElementById('currentDate').innerText = new Date().toLocaleDateString();

    if (data.image_path && data.image_path !== '') {
        document.getElementById('imageDisplay').src = data.image_path;
        document.getElementById('imageDisplay').style.display = 'block';
    } else {
        document.getElementById('imageDisplay').src = '';
        document.getElementById('imageDisplay').style.display = 'none';
    }

    document.getElementById('confirmationModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('confirmationModal').style.display = 'none';
}

function printModal() {
    const printContent = document.querySelector('.modal-content').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div class="print-container" style="width: 8.5in; min-height: 11in; padding: 0.5in; margin: 0 auto;">
            ${printContent}
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
}

function saveAsPDF() {
    const element = document.querySelector('.modal-content');
    const opt = {
        margin: 0.5,
        filename: 'tree-planting-report.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    // Add print-specific styles
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            body * {
                visibility: hidden;
            }
            .print-container, .print-container * {
                visibility: visible;
            }
            .print-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 8.5in !important;
                min-height: 11in !important;
                padding: 0.5in !important;
                margin: 0 !important;
            }
            .action-btn {
                display: none !important;
            }
            .x-button {
                display: none !important;
            }
        }
    `;
    document.head.appendChild(style);

    html2pdf().set(opt).from(element).save();
}



</script>


</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard - View Submission</title>
  <link rel="stylesheet" type="text/css" href="cancel.css?v=<?php echo time(); ?>">
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

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("CALL CancelSubmission(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Submission cancelled successfully.";
        header("Location: cancel.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error cancelling submission: " . $e->getMessage();
    }
}

$user_id = $_SESSION['user_id'];
?>

<!-- Add notification div -->
<div id="notification" class="notification">
    <span class="icon">✓</span>
    <span class="message"></span>
    <span class="close-notification">&times;</span>
</div>

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
             <li><a  href="volunteer.php"> 👤 My Account</a></li>
          </ul>
          <ul>
            <h3>Submission</h3>
             <li><a href="treeplantingform.php">📄 Tree Planting Form</a></li>
             <li><a href="viewsubmission.php">🌐 View Submission</a></li>
             <li><a href="editsubmission.php">🌐 Edit Submission</a></li>
             <li><a class="active" href="cancel.php">🌐 Cancel Submission</a></li>
          </ul>
          <h3>View</h3>
          <ul>
             <li><a href="totaltrees.php">🌐 Total Trees Planted</a></li>
             <li><a href="estimate.php">🌐 CO₂ Absorbed Estimate</a></li>
          </ul>
             <div class="sidebar1">
          <ul>
             <li><a class ="logout" href="Homepage.php">Logout</a></li>
          </ul>
      </div>
</div>

<div class="form-wrapper">
    
    <div class="form-header">Cancel</div>
  
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
  
  $countQuery = "CALL CountSubmissions()";
  $countResult = mysqli_query($conn, $countQuery);
  
  if ($countResult) {
      $totalRow = mysqli_fetch_assoc($countResult);
      $total = $totalRow['total'];
      $pages = ceil($total / $limit);
  
      mysqli_free_result($countResult);
  
      $conn->next_result();
  }
  
  
  
  
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
              $color = 'color: #FFA500;'; 
          } elseif ($status == 'Approved') {
              $color = 'color: #28a745;'; 
          } elseif ($status == 'Disapproved') {
              $color = 'color: #dc3545;'; 
          } elseif ($status == 'Cancelled') {
            $color = 'color:rgb(220, 89, 53);'; 
        }
          
          echo "<td style='$color font-weight: bold;'>$status</td>";
          
          echo "<td><button class='cancel-button' onclick='openCancelModal(" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ")'>Cancel</button></td>";
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
  
      
    </div>
  </div>

  <!-- Simplified Modal -->
  <div id="cancelModal" class="modal" style="display: none;">
    <div class="modal-content cancel-modal">
      <div class="modal-header">
        <button onclick="closeCancelModal()" class="x-button">&times;</button>
        <h1>Cancel Submission</h1>
      </div>

      <div class="modal-body">
        <div class="warning-icon">⚠️</div>
        <h2>Are you sure you want to cancel this submission?</h2>
        <p class="warning-message">This action cannot be undone.</p>
      </div>

      <div class="modal-footer">
        <button class="cancel-btn" onclick="closeCancelModal()">No, Keep It</button>
        <button id="confirmCancelBtn" class="confirm-btn">Yes, Cancel</button>
      </div>
    </div>
  </div>

<script>
let currentSubmissionId = null;

function openCancelModal(data) {
    currentSubmissionId = data.id;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

document.getElementById('confirmCancelBtn').addEventListener('click', function () {
    window.location.href = 'cancel.php?id=' + currentSubmissionId;
});

// Function to show notification
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const icon = notification.querySelector('.icon');
    const messageEl = notification.querySelector('.message');
    
    // Set icon based on type
    icon.textContent = type === 'success' ? '✓' : '✕';
    
    // Set message and type
    messageEl.textContent = message;
    notification.className = `notification ${type}`;
    
    // Show notification
    notification.classList.add('show');
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Add event listeners when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show notification if there's a message in session
    <?php if (isset($_SESSION['success_message'])): ?>
        showNotification('<?php echo $_SESSION['success_message']; ?>', 'success');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        showNotification('<?php echo $_SESSION['error_message']; ?>', 'error');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    // Add event listener for close button
    document.querySelector('.close-notification').addEventListener('click', function() {
        document.getElementById('notification').classList.remove('show');
    });
});
</script>


</body>
</html>

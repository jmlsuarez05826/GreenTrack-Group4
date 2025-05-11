<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard - Edit Submission</title>
 <link rel="stylesheet" type="text/css" href="editsubmission.css?v=<?php echo time(); ?>">
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

$user_id = $_SESSION['user_id']; // Set user_id from session

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_submission'])) {
     $id = $_POST['id'];  
    $tree_type = $_POST['tree_type'];
    $number = $_POST['number'];
    $location = $_POST['location'];
    $other_location = ($location === 'OTHER') ? $_POST['other_location'] : null;
    $image_path = '';

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image_path = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Update main record
        $stmt = $conn->prepare("CALL EditSubmissions(?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("isiss", $id, $tree_type, $number, $location, $image_path);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $conn->commit();
        
        $_SESSION['success_message'] = "Submission updated successfully.";
        header("Location: editsubmission.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating submission: " . $e->getMessage();
    }

    $stmt->close();
    $conn->next_result();
    exit;
}
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
             <li><a class="active" href="editsubmission.php">🌐 Edit Submission</a></li>
             <li><a href="cancel.php">🌐 Cancel Submission</a></li>
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
    
    <div class="form-header">Edit</div>
  
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
        <th>Create Date</th>
        <th>Update Date</th>
        <th>Location</th>
        <th>Number</th>
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
  
  
  
  
  // Debug user session
  echo "<!-- Debug Session Info:
  Session User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . "
  -->";
  
  // Get submissions
  $stmt = $conn->prepare("CALL ViewSubmissions(?, ?, ?, ?, ?)");
  if (!$stmt) {
      die("Prepare failed: " . $conn->error);
  }
  
  // Debug parameters before binding
  echo "<!-- Debug Parameters:
  Start: " . $start . "
  Limit: " . $limit . "
  Year: " . ($year ? $year : 'NULL') . "
  Month: " . ($month ? $month : 'NULL') . "
  User ID: " . $user_id . "
  -->";
  
  $stmt->bind_param("iiiii", $start, $limit, $year, $month, $user_id);
  if (!$stmt->execute()) {
      die("Execute failed: " . $stmt->error);
  }
  
  $result = $stmt->get_result();
  
  // Debug result
  echo "<!-- Debug Result:
  Number of rows: " . $result->num_rows . "
  -->";
  
  // Free the result and close the statement
  $stmt->close();
  $conn->next_result();
  
  // Now check total count
  $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tree_plantings WHERE user_id = ?");
  if (!$checkStmt) {
      die("Prepare failed for count: " . $conn->error);
  }
  
  $checkStmt->bind_param("i", $user_id);
  if (!$checkStmt->execute()) {
      die("Execute failed for count: " . $checkStmt->error);
  }
  
  $checkResult = $checkStmt->get_result();
  $totalCount = $checkResult->fetch_assoc()['count'];
  $checkStmt->close();
  
  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars($row['username']) . "</td>";
          echo "<td>" . htmlspecialchars($row['tree_type']) . "</td>";
          echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
          echo "<td>" . (isset($row['updated_at']) ? date('Y-m-d', strtotime($row['updated_at'])) : 'N/A') . "</td>";
          echo "<td>" . htmlspecialchars($row['location']) . "</td>";
          echo "<td>" . htmlspecialchars($row['number']) . "</td>";
  
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
          
          echo "<td><button class='view-button' onclick='openEditModal(" . htmlspecialchars(json_encode($row), ENT_QUOTES) . ")'>Edit</button></td>";
          echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='8'>No submissions found for this user. (Total submissions in database: " . $totalCount . ")</td></tr>";
  }
  
  $defaultYear = date('Y');
  $defaultMonth = date('n');
  
  $year = isset($_GET['year']) ? (int)$_GET['year'] : $defaultYear;
  $month = isset($_GET['month']) ? (int)$_GET['month'] : $defaultMonth;
  
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

  <div id="editModal" class="modal" style="display: none;">
  <div class="modal-content">
    <form method="POST" enctype="multipart/form-data">
      <h2>Edit Submission</h2>

      <input type="hidden" name="id" id="edit-id" />

      <label for="tree_type">Tree Types:</label>
                    <select id="edit-tree_type" name="tree_type" required onchange="showDescription()">
                        <option value="" disabled selected>Select a tree type</option>
                        <option value="Narra" data-desc="Narra is a hardwood tree native to the Philippines and absorbs ~21.8 kg of CO₂ per year.">Narra (~21.8 kg CO₂/year)</option>
                        <option value="Mahogany" data-desc="Mahogany is a fast-growing hardwood tree that absorbs ~28.0 kg of CO₂ per year.">Mahogany (~28.0 kg CO₂/year)</option>
                        <option value="Molave" data-desc="Molave is a durable native tree that absorbs ~25.0 kg of CO₂ per year.">Molave (~25.0 kg CO₂/year)</option>
                        <option value="Acacia" data-desc="Acacia grows quickly and absorbs ~35.0 kg of CO₂ per year.">Acacia (~35.0 kg CO₂/year)</option>
                        <option value="Yakal" data-desc="Yakal is a tough native tree that absorbs ~20.0 kg of CO₂ per year.">Yakal (~20.0 kg CO₂/year)</option>
                        <option value="Ipil-ipil" data-desc="Ipil-ipil is fast-growing and absorbs ~15.0 kg of CO₂ per year.">Ipil-ipil (~15.0 kg CO₂/year)</option>
                        <option value="Bamboo" data-desc="Bamboo grows very fast and absorbs ~62.0 kg of CO₂ per year.">Bamboo (~62.0 kg CO₂/year)</option>
                        <option value="Banaba" data-desc="Banaba has medicinal properties and absorbs ~22.5 kg of CO₂ per year.">Banaba (~22.5 kg CO₂/year)</option>
                        <option value="Talisay" data-desc="Talisay grows well near coasts and absorbs ~18.0 kg of CO₂ per year.">Talisay (~18.0 kg CO₂/year)</option>
                        <option value="Balete" data-desc="Balete is iconic for its large roots and absorbs ~30.0 kg of CO₂ per year.">Balete (~30.0 kg CO₂/year)</option>
                    </select>

      <label for="number">Number:</label>
      <input type="number" name="number" id="edit-number" required />

      <label for="location">Location:</label>
      <select id="edit-location" name="location" required onchange="toggleOtherLocation()">
          <option value="" disabled selected>Select a location</option>
          <option value="LIPA">LIPA</option>
          <option value="SAN JUAN">SAN JUAN</option>
          <option value="BATANGAS CITY">BATANGAS CITY</option>
          <option value="TAAL">TAAL</option>
          <option value="NASUGBU">NASUGBU</option>
          <option value="OTHER">OTHER</option>
      </select>
      <div id="otherLocationDiv" style="display: none; margin-top: 10px;">
          <input type="text" id="edit-other_location" name="other_location" placeholder="Enter other location">
      </div>

      <label for="image">New Proof Image (optional):</label>
      <input type="file" name="image" id="edit-image" accept="image/*" />

      <br><br>
      <button type="submit" name="update_submission">Save Changes</button>
      <button type="button" onclick="closeEditModal()">Cancel</button>
    </form>
  </div>
</div>



<script>
function openEditModal(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-tree_type').value = data.tree_type;
    document.getElementById('edit-number').value = data.number;
    
    // Handle location and other_location
    if (data.location === 'OTHER') {
        document.getElementById('edit-location').value = 'OTHER';
        document.getElementById('otherLocationDiv').style.display = 'block';
        document.getElementById('edit-other_location').value = data.other_location;
    } else {
        document.getElementById('edit-location').value = data.location;
        document.getElementById('otherLocationDiv').style.display = 'none';
    }

    // Display current image if it exists
    if (data.image_path) {
        const imagePreview = document.createElement('div');
        imagePreview.innerHTML = `
            <label>Current Image:</label>
            <img src="${data.image_path}" alt="Current proof image" style="max-width: 200px; margin: 10px 0;">
        `;
        document.getElementById('edit-image').parentNode.insertBefore(imagePreview, document.getElementById('edit-image'));
    }

    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    // Remove the image preview when closing the modal
    const imagePreview = document.querySelector('img[alt="Current proof image"]');
    if (imagePreview) {
        imagePreview.parentNode.remove();
    }
}

function toggleOtherLocation() {
    const locationSelect = document.getElementById('edit-location');
    const otherLocationDiv = document.getElementById('otherLocationDiv');
    const otherLocationInput = document.getElementById('edit-other_location');
    
    if (locationSelect.value === 'OTHER') {
        otherLocationDiv.style.display = 'block';
        otherLocationInput.required = true;
    } else {
        otherLocationDiv.style.display = 'none';
        otherLocationInput.required = false;
        otherLocationInput.value = '';
    }
}

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

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 600px;
    border-radius: 8px;
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.modal-content h2 {
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

.modal-content label {
    font-weight: bold;
    color: #555;
}

.modal-content select,
.modal-content input[type="number"],
.modal-content input[type="date"],
.modal-content input[type="text"],
.modal-content input[type="file"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-top: 5px;
}

.modal-content button {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
}

.modal-content button[type="submit"] {
    background-color: #4CAF50;
    color: white;
}

.modal-content button[type="button"] {
    background-color: #f44336;
    color: white;
}

.modal-content img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin: 10px 0;
}

/* Add a nice scrollbar */
.modal-content::-webkit-scrollbar {
    width: 8px;
}

.modal-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Make sure the form elements don't overflow */
.modal-content input,
.modal-content select,
.modal-content textarea {
    max-width: 100%;
    box-sizing: border-box;
}

/* Add some spacing between buttons */
.modal-content button + button {
    margin-left: 10px;
}

/* Style the image preview container */
.image-preview-container {
    margin: 10px 0;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f9f9f9;
}

/* Make the form more compact on smaller screens */
@media screen and (max-height: 700px) {
    .modal-content {
        margin: 2% auto;
        max-height: 96vh;
    }
    
    .modal-content form {
        gap: 10px;
    }
    
    .modal-content label {
        margin-bottom: 2px;
    }
}
</style>

</body>
</html>

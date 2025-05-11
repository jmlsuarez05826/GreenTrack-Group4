<!DOCTYPE html>
<html>
<head>
    <title>GreenTrack - Admin Account</title>
    <link rel="stylesheet" type="text/css" href="Tree_subm.css?v=<?php echo time(); ?>">
</head>
<body>
<?php 
session_start();
require_once 'procedure.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_id'], $_POST['action'])) {
    $id = intval($_POST['submission_id']);
    $status = $_POST['action'] === 'approve' ? 'Approved' : 'Disapproved';

    if ($crud->updateSubmissionStatus($id, $status)) {
        $_SESSION['alert'] = [
            'message' => 'Submission updated successfully!',
            'type' => 'success'
        ];
    } else {
        $_SESSION['alert'] = [
            'message' => 'Failed to update submission.',
            'type' => 'error'
        ];
    }
    header('Location: Tree_submission.php');
    exit;
}

// Display alert if it exists in session
if (isset($_SESSION['alert'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('" . $_SESSION['alert']['message'] . "', '" . $_SESSION['alert']['type'] . "');
        });
    </script>";
    unset($_SESSION['alert']); // Clear the alert after displaying
}
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
            <li><a  href="Admin_Page.php">👤 Admin Account</a></li>
            <li><a href="Manage.php">👥 Manage Account</a></li>
</ul>

<h3>View & Approval</h3>
             <ul>
             <li><a class="active" href="Tree_submission.php">🌳 Tree submission</a></li>
             <li><a href="Total_trees.php">🌲 Total trees planted</a></li>
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

x`        <div id="alert" class="alert">
            <span class="icon">✓</span>
            <span class="message"></span>
            <span class="close-alert">&times;</span>
        </div>

        <div class="form-wrapper">
            <div class="form-header">Tree Submissions</div>

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

                // Get total count and calculate pages
                $total = $crud->countSubmissions();
                $pages = ceil($total / $limit);

                // Get submissions for current page
                $submissions = $crud->viewSubmissions($start, $limit, $year, $month, null);

                if (!empty($submissions)) {
                    foreach ($submissions as $row) {
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
                        }
                        
                        echo "<td style='$color font-weight: bold;'>$status</td>";
                        
                        // Create a data object for the modal
                        $modalData = array(
                            'id' => $row['id'],
                            'username' => $row['username'],
                            'email' => $row['email'],
                            'tree_type' => $row['tree_type'],
                            'number' => $row['number'],
                            'date_planted' => $row['created_at'],
                            'location' => $row['location'],
                            'image_path' => $row['image_path'],
                            'group_members' => $row['group_members'] ?? 'N/A',
                            'status' => $row['status']
                        );
                        
                        echo "<td>
                            <button class='view-button' onclick='openModal(" . json_encode($modalData) . ")'>View</button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No submissions found.</td></tr>";
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

<div id="confirmModal" class="modal">
  <div class="modal-content">
    <p id="confirmText">Are you sure?</p>
    <button id="confirmYes">Yes</button>
    <button id="confirmNo">No</button>
  </div>
</div>

<div id="viewModal" class="modal">
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
                    <form method="post" class="action-form" style="display:inline;">
                        <input type="hidden" name="submission_id" id="submissionId">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="action-btn approve-btn">
                            <i class="approve-icon">✓</i> Approve
                        </button>
                    </form>
                    <form method="post" class="action-form" style="display:inline;">
                        <input type="hidden" name="submission_id" id="submissionIdDisapprove">
                        <input type="hidden" name="action" value="disapprove">
                        <button type="submit" class="action-btn disapprove-btn">
                            <i class="disapprove-icon">✕</i> Disapprove
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedForm = null;
const modal = document.getElementById('confirmModal');
const confirmText = document.getElementById('confirmText');
const confirmYes = document.getElementById('confirmYes');
const confirmNo = document.getElementById('confirmNo');
const alert = document.getElementById('alert');

function showAlert(message, type = 'success') {
    const alertElement = document.getElementById('alert');
    alertElement.className = `alert ${type}`;
    alertElement.querySelector('.message').textContent = message;
    alertElement.classList.add('show');
    
    setTimeout(() => {
        alertElement.classList.remove('show');
    }, 3000);
}

document.querySelectorAll('.confirm-action').forEach(button => {
    button.addEventListener('click', function () {
        selectedForm = this.closest('form');
        const action = this.dataset.action;
        confirmText.textContent = `Are you sure you want to ${action} this submission?`;
        modal.style.display = 'block';
    });
});

confirmYes.addEventListener('click', function () {
    if (selectedForm) {
        const action = selectedForm.querySelector("button[data-action]").dataset.action;
        selectedForm.querySelector("input[name='action']").value = action;
        selectedForm.submit();
        showAlert(`Submission ${action}d successfully!`, 'success');
    }
    modal.style.display = 'none';
});

confirmNo.addEventListener('click', function () {
    modal.style.display = 'none';
});

document.querySelector('.close-alert').addEventListener('click', function() {
    document.getElementById('alert').classList.remove('show');
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

function openModal(data) {
    document.getElementById('usernameDisplay').innerText = data.username || 'N/A';
    document.getElementById('emailDisplay').innerText = data.email || 'N/A';
    document.getElementById('groupMembersDisplay').innerText = data.group_members || 'N/A';
    document.getElementById('treeTypeDisplay').innerText = data.tree_type || 'N/A';
    document.getElementById('numberDisplay').innerText = data.number || 'N/A';
    document.getElementById('dateDisplay').innerText = data.date_planted || 'N/A';
    document.getElementById('locationDisplay').innerText = data.location || 'N/A';
    document.getElementById('currentDate').innerText = new Date().toLocaleDateString();
    document.getElementById('submissionId').value = data.id;
    document.getElementById('submissionIdDisapprove').value = data.id;

    if (data.image_path && data.image_path !== '') {
        document.getElementById('imageDisplay').src = data.image_path;
        document.getElementById('imageDisplay').style.display = 'block';
    } else {
        document.getElementById('imageDisplay').src = '';
        document.getElementById('imageDisplay').style.display = 'none';
    }

    document.getElementById('viewModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('viewModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>

<style>
.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.alert.show {
    transform: translateX(0);
}

.alert.success {
    border-left: 4px solid #4CAF50;
}

.alert.error {
    border-left: 4px solid #dc3545;
}

.alert .icon {
    font-size: 20px;
}

.alert.success .icon {
    color: #4CAF50;
}

.alert.error .icon {
    color: #dc3545;
}

.alert .message {
    color: #333;
    font-size: 14px;
    font-weight: 500;
}

.alert .close-alert {
    margin-left: 15px;
    cursor: pointer;
    color: #666;
    font-size: 18px;
    padding: 4px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.alert .close-alert:hover {
    background-color: #f5f5f5;
    color: #333;
}

.viewModal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    padding: 20px;
}

.modal-content {
    background: #ffffff;
    margin: 20px auto;
    width: 90%;
    max-width: 8.5in;
    min-height: auto;
    padding: 0.5in;
    box-sizing: border-box;
    position: relative;
    border-radius: 10px;
    box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.2);
    max-height: 90vh;
    overflow-y: auto;
    scroll-behavior: smooth;
}

.modal-header {
    position: sticky;
    top: 0;
    background: #ffffff;
    z-index: 1;
    padding-top: 20px;
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #000;
}

.modal-header .logo {
    width: 80px;
    height: auto;
    margin-bottom: 15px;
}

.modal-header h1 {
    font-family: 'Times New Roman', Times, serif;
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
    color: #000;
}

.modal-header h4 {
    font-family: 'Times New Roman', Times, serif;
    font-size: 16px;
    font-style: italic;
    margin: 5px 0;
    color: #000;
}

.modal-body {
    padding: 20px;
    border: 1px solid #000;
    overflow-y: auto;
}

.margin {
    padding: 30px;
    border: 1px solid #000;
    overflow-y: auto;
}

.x-button {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    background: none;
    border: none;
    color: #000;
    cursor: pointer;
    transition: color 0.3s ease;
    z-index: 1000;
}

.x-button:hover {
    color: #666;
}

.form-section {
    margin-bottom: 30px;
}

.form-section h3 {
    font-family: 'Times New Roman', Times, serif;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    border-bottom: 1px solid #000;
    padding-bottom: 5px;
    color: #000;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.info-item {
    border: 1px solid #000;
    padding: 10px;
    background: #fff;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    font-family: 'Times New Roman', Times, serif;
    color: #000;
}

.info-item span {
    font-family: 'Times New Roman', Times, serif;
    color: #000;
}

.image-preview {
    max-width: 200px;
    height: auto;
    margin: 15px auto;
    border: 1px solid #000;
    padding: 5px;
    display: block;
}

.footer {
    margin-top: 40px;
    text-align: center;
    font-size: 12px;
    border-top: 1px solid #000;
    padding-top: 20px;
    font-family: 'Times New Roman', Times, serif;
    color: #000;
}

.modal-footer {
    position: sticky;
    bottom: 0;
    background: #ffffff;
    z-index: 1;
    padding-bottom: 20px;
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #000;
    display: flex;
    justify-content: center;
    gap: 20px;
}

.action-btn {
    padding: 12px 24px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-family: 'Times New Roman', Times, serif;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    min-width: 120px;
    justify-content: center;
    border-radius: 8px;
}

.approve-btn {
    background-color: #4CAF50;
    color: white;
}

.approve-btn:hover {
    background-color: #45a049;
}

.disapprove-btn {
    background-color: #dc3545;
    color: white;
}

.disapprove-btn:hover {
    background-color: #c82333;
}

.divider {
    border: 1px solid #000;
    margin: 20px auto;
    width: 80%;
}

/* Style the scrollbar for better appearance */
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
</style>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Volunteer Dashboard - Home</title>
  <link rel="stylesheet" type="text/css" href="volunteer.css?v=<?php echo time(); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php 
session_start();
require_once 'database.php';
require_once 'procedure.php';

$user_id = $_SESSION['user_id']; 

// Create database connection using the Database class
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("CALL GetAccountInfo(?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $realUsername = htmlspecialchars($row['username']);
    $email = htmlspecialchars($row['email']);
    $role = htmlspecialchars($row['role']);
    $account_type = htmlspecialchars($row['account_type']);
    $group_members = $row['group_members'] ?? '';
    $profileImage = $row['profile'] ?? "uploads/default.png"; 
} else {
    $realUsername = "N/A";
    $email = "N/A";
    $role = "N/A";
    $account_type = "N/A";
    $group_members = "";
    $profileImage = "uploads/default.png";
}
$result->free();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];

    // Check if username exists for other users
    if ($crud->checkUsernameExistsForOtherUsers($newUsername, $user_id)) {
        $usernameError = "The username that you input is already taken";
    } else {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $imageTmpName = $_FILES['profile_image']['tmp_name'];
            $imageName = uniqid() . '_' . basename($_FILES['profile_image']['name']);
            $imagePath = "uploads/" . $imageName;
        
            if (move_uploaded_file($imageTmpName, $imagePath)) {
                $profileImage = $imagePath;
            } else {
                $profileImage = $row['profile'];
            }
        } else {
            $profileImage = $row['profile']; 
        }

        $stmtUpdate = $conn->prepare("CALL UpdateAccountInfo(?, ?, ?, ?)");
        $stmtUpdate->bind_param("isss", $user_id, $newUsername, $newEmail, $profileImage);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $_SESSION['update_success'] = true;
        header("Location: volunteer.php");
        exit();
    }
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
         <button class="menu-toggle">☰</button>
</header>

<div class="container">
      <div class="sidebar">
          <ul>
            <h3>Manage Account</h3>
             <li><a  class="active" href="volunteer.php"> 👤 My Account</a></li>
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
             <li><a href="estimate.php">🌐 CO₂ Absorbed Estimate</a></li>
          </ul>
             <div class="sidebar1">
          <ul>
             <li><a class ="logout" href="Homepage.php">Logout</a></li>
          </ul>
      </div>
</div>

<div class="main-content">
    <?php if (isset($_SESSION['update_success']) && $_SESSION['update_success'] === true) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Your changes have been saved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['update_success']); ?>
    <?php } ?>
    <div class="account-box">
    <div class="account-header">
        <h2 class="myAccount">My Account</h2>
    </div>

    <div class="account-box-1">
        <div class="account-info">
            <img src="<?= !empty($profileImage) ? $profileImage : 'uploads/default.png' ?>" class="profile-pic" alt="Profile">

            <div class="account-details">
                <p><strong>Username:</strong> <?= $realUsername ?></p>
                <p><strong>Email:</strong> <?= $email ?></p>
                <p><strong>Role:</strong> <?= $role ?></p>
                <p><strong>Account Type:</strong> <?= ucfirst($account_type) ?></p>
                <?php if ($account_type === 'Group Account' && !empty($group_members)): ?>
                    <p><strong>Group Members:</strong></p>
                    <div class="group-members-list">
                        <?php 
                        $members = explode(',', $group_members);
                        foreach ($members as $member) {
                            echo '<p class="member-name">• ' . trim($member) . '</p>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="edit-box">
    <form action="volunteer.php" enctype="multipart/form-data" method="POST">
        <h3>Edit Your Account Info</h3>
        <div class="edit-account">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= $realUsername ?>" required>
            <?php if (isset($usernameError)): ?>
                <div class="error-message"><?= $usernameError ?></div>
            <?php endif; ?>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= $email ?>" required>

            <label for="profile_image">Profile Image:</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">

            <button type="submit" class="save-btn">Save Changes</button>
        </div>
    </form>
</div>




  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add hamburger menu functionality
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const menuToggle = document.querySelector('.menu-toggle');
        
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>

</body>
</html>

<!DOCTYPE html>
 <html>
 <head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>GreenTrack - Admin Account</title>
     <link rel="stylesheet" type="text/css" href="Admin.css?v=<?php echo time(); ?>">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
 </head>
 <body>

 <?php 
 require_once 'procedure.php';
 require_once 'database.php';
 session_start();

 // Create database connection
 $db = new Database();
 $conn = $db->getConnection();

 $user_id = $_SESSION['user_id']; 

// Get user information using Crud class
$userInfo = $crud->getAccountInfo($user_id);

if ($userInfo) {
    $realUsername = htmlspecialchars($userInfo['username']);
    $email = htmlspecialchars($userInfo['email']);
    $role = htmlspecialchars($userInfo['role']);
    $profileImage = $userInfo['profile'] ?? "uploads/default.png"; 
} else {
    $realUsername = "N/A";
    $email = "N/A";
    $role = "N/A";
    $profileImage = "uploads/default.png";
}

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
                $profileImage = $userInfo['profile'];
            }
        } else {
            $profileImage = $userInfo['profile']; 
        }

        $stmtUpdate = $conn->prepare("CALL UpdateAccountInfo(?, ?, ?, ?)");
        $stmtUpdate->bind_param("isss", $user_id, $newUsername, $newEmail, $profileImage);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        $_SESSION['update_success'] = true;
        header("Location: Admin_Page.php");
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
             <li><a  href="Dashboard.php"> 📊Dashboard</a></li>
 </ul>
             <h3>Manage Account</h3>
             <ul>
             <li><a class="active" href="Admin_Page.php">👤 Admin Account</a></li>
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
            </div>
        </div>
    </div>
</div>

<div class="edit-box">
    <form action="Admin_Page.php" enctype="multipart/form-data" method="POST">
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
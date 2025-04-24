<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenTrack - Register</title>
    <link rel="stylesheet" type="text/css" href="Register.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   
</head>
<body>
<?php include "database.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username      = $_POST['username'];
    $email         = $_POST['email'];
    $password      = $_POST['password'];
    $accountType   = $_POST['account_type'];
    $groupMembers  = isset($_POST['group_members']) ? $_POST['group_members'] : null;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("CALL insertRegisteredAccount(?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $hashedPassword, $accountType, $groupMembers);

    if ($stmt->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: 'You can now log in.',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                window.location.href = 'Register.php';
            });
        </script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

    <div class="navbar">
        <div class="brand">GREENTRACK</div>
        <div>
            <a class="button" href="Homepage.php" style="background-color:white; color:black;"><i class="fa-solid fa-house"></i></a>
        </div>
    </div>
    <div class="form-container">

    <div class="register-section">
    <h2>Register</h2>
        <form method="POST" action="register.php">
            <label>Fill up the form to register an account.</label>
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <select name="account_type" id="account_type" required>
                <option value="">Select account type</option>
                <option value="individual">Individual</option>
                <option value="groupaccount">Group  Account</option>
            </select>

            <textarea id="group_members" name="group_members" placeholder="Enter group member names..." style="display: none;"></textarea>
            <button type="submit" class="submit">Register</button>
            <div class="have-account">
  If you have an account <a href="Login.php">Login here</a>.
</div>
        </form>
</div>
<div class="welcome-section">
    </div>
    </div>



    <script>
        const accountTypeSelect = document.getElementById('account_type');
        const groupMembersTextarea = document.getElementById('group_members');

        accountTypeSelect.addEventListener('change', function () {
            if (this.value === 'groupaccount') {
                groupMembersTextarea.style.display = 'block';
            } else {
                groupMembersTextarea.style.display = 'none';
            }
        });
    </script>
</body>
</html>

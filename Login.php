<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenTrack - Register</title>
    <link rel="stylesheet" type="text/css" href="Login.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php 
require_once 'procedure.php';
require_once 'database.php';
session_start(); 

// Create database connection
$db = new Database();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    $user = $crud->getUsername($inputUsername);

    if ($user) {
        if (password_verify($inputPassword, $user['password'])) {
            $role = $user['role'];
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            // Log successful login
            $stmt = $conn->prepare("CALL InsertLoginLog(?, ?, 'Success')");
            $stmt->bind_param("is", $user['user_id'], $user['username']);
            $stmt->execute();
            $stmt->close();

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Successful!',
                        text: 'You can now go to the dashboard.',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        window.location.href = '" . ($role === 'Admin' ? 'Dashboard.php' : 'volunteer.php') . "';
                    });
                  </script>";
            exit();
        } else {
            // Log failed login attempt
            $stmt = $conn->prepare("CALL InsertLoginLog(?, ?, 'Failed')");
            $stmt->bind_param("is", $user['user_id'], $user['username']);
            $stmt->execute();
            $stmt->close();

            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: 'Invalid username or password.',
                        confirmButtonColor: '#3085d6'
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                  </script>";
        }
    } else {
        // Log failed login attempt for non-existent user
        try {
            // First check if the username exists in registered_accounts
            $checkUser = $crud->getUsername($inputUsername);
            if ($checkUser) {
                $stmt = $conn->prepare("CALL InsertLoginLog(?, ?, 'Failed')");
                $stmt->bind_param("is", $checkUser['user_id'], $inputUsername);
                $stmt->execute();
            } else {
                // For non-existent users, just log the attempt without user_id
                $stmt = $conn->prepare("INSERT INTO user_logs (username, login_status, login_time) VALUES (?, 'Failed', NOW())");
                $stmt->bind_param("s", $inputUsername);
                $stmt->execute();
            }
            $stmt->close();
        } catch (Exception $e) {
            // Log the error but continue with the login process
            error_log("Error logging failed login attempt: " . $e->getMessage());
        }

        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: 'Invalid username or password.',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    window.location.href = 'login.php';
                });
              </script>";
    }
}
?>

<header class="navbar">
  <div class="brand">
    <img src="LOGO.png" class="logo" alt="GreenTrack Logo" />
    <h1><span>GREEN</span>TRACK</h1>
  </div>
  <a class="button" href="Homepage.php">
    <i class="fa-solid fa-house"></i>
  </a>
</header>


<div class="form-container">
  <div class="login-section">
    <h2>Log In</h2>
    <form method="POST" action="login.php">
      <label>Login your account</label>
      <div class="input-group">
        <input type="text" name="username" id="username" placeholder="Username" required>
      </div>
      <div class="input-group password-group">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <i class="fa-solid fa-eye" id="togglePassword"></i>
      </div>

      <div class="options">
        <div class="forgot">
          <a href="Forgot_Password.php">Forgot Password?</a>
        </div>
      </div>
      <button type="submit" class="login">Log In</button>
      <div class="no-account">
        If you don't have an account <br><a href="Register.php">Register here</a>.
      </div>
    </form>
  </div>
  <div class="welcome-section"></div>
</div>

<script>
  const passwordInput = document.getElementById("password");
  const togglePassword = document.getElementById("togglePassword");

  togglePassword.addEventListener("click", () => {
    const type = passwordInput.type === "password" ? "text" : "password";
    passwordInput.type = type;
    togglePassword.classList.toggle("fa-eye");
    togglePassword.classList.toggle("fa-eye-slash");
  });
</script>
</body>
</html>

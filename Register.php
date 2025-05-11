<?php
session_start();
require_once 'procedure.php';
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_GET['close_otp']) && $_GET['close_otp'] === 'true') {
    unset($_SESSION['otp_sent']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); 
    exit();
}

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'greentrackweb@gmail.com';
        $mail->Password   = 'vmwg jgub wgrf oxkk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('greentrackweb@gmail.com', 'GreenTrack');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your GreenTrack OTP Code';
        $mail->Body    = "<h3>Your OTP code is: <b>$otp</b></h3>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle registration form
if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $username = $_POST['username'];

    // Check if email exists
    if ($crud->checkEmailExists($email)) {
        $_SESSION['email_exists'] = true;
        header("Location: Register.php");
        exit;
    } 
    // Check if username exists
    else if ($crud->checkUsernameExists($username)) {
        $_SESSION['username_exists'] = true;
        header("Location: Register.php");
        exit;
    }
    else {
        $_SESSION['temp_username'] = $username;
        $_SESSION['temp_email'] = $email;
        $_SESSION['temp_password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $_SESSION['temp_account_type'] = $_POST['account_type'];
        $_SESSION['temp_group_members'] = isset($_POST['group_members']) ? $_POST['group_members'] : null;

        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

        if (sendOTP($email, $otp)) {
            $_SESSION['otp_sent'] = true;
            header("Location: Register.php");
            exit;
        } else {
            echo "<script>alert('Failed to send OTP. Please try again later.');</script>";
        }
    }
}

if (isset($_POST['verify_otp'])) {
    $enteredOtp = '';

    // Combine all 6 digits into one string
    for ($i = 1; $i <= 6; $i++) {
        $key = 'otp_digit_' . $i;
        if (!isset($_POST[$key]) || !preg_match('/^\d$/', $_POST[$key])) {
            echo "<script>alert('Please enter all 6 digits correctly.');</script>";
            exit;
        }
        $enteredOtp .= $_POST[$key];
    }

    if (isset($_SESSION['otp']) && $enteredOtp == $_SESSION['otp']) {
        $defaultProfile = 'PROFILE.png';
        
        if ($crud->insertRegisteredAccount(
            $_SESSION['temp_username'],
            $_SESSION['temp_email'],
            $_SESSION['temp_password'],
            $_SESSION['temp_account_type'],
            $_SESSION['temp_group_members'],
            $defaultProfile
        )) {
            // Clear all session variables
            unset(
                $_SESSION['temp_username'],
                $_SESSION['temp_email'],
                $_SESSION['temp_password'],
                $_SESSION['temp_account_type'],
                $_SESSION['temp_group_members'],
                $_SESSION['otp'],
                $_SESSION['otp_sent']
            );
            
            // Set success message and redirect
            $_SESSION['registration_success'] = true;
            header("Location: Login.php");
            exit;
        } else {
            echo "<script>alert('Database Error: Failed to register account');</script>";
        }
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}
?>

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
    <div class="register-section">
        <h2>Register</h2>
        <form method="POST" action="">
            <label>Fill up the form to register an account.</label>

            <input type="text" name="username" placeholder="Username" required>
            <?php if (isset($_SESSION['username_exists']) && $_SESSION['username_exists']): ?>
                <div class="error-message">The username that you input is already taken</div>
                <?php unset($_SESSION['username_exists']); ?>
            <?php endif; ?>

            <input type="email" name="email" placeholder="Email" required>
            <?php if (isset($_SESSION['email_exists']) && $_SESSION['email_exists']): ?>
                <div class="error-message">This email is already registered. Please use a different email.</div>
                <?php unset($_SESSION['email_exists']); ?>
            <?php endif; ?>

            <div class="input-group password-group">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <i class="fa-solid fa-eye" id="togglePassword"></i>
      </div>

            <select name="account_type" id="account_type" required>
                <option value="">Select account type</option>
                <option value="Individual">Individual</option>
                <option value="Group Account">Group Account</option>
            </select>

            <textarea id="group_members" name="group_members" class="group-members" placeholder="Enter group members (comma separated)"></textarea>

            <button type="submit" name="register" class="submit">Register</button>

            <div class="have-account">
                Already have an account? <a href="Login.php">Login here</a>.
            </div>
        </form>
    </div>

    <div class="welcome-section"></div>
</div>

<!-- OTP Modal -->
<div id="otpModal" class="otp-modal <?php echo isset($_SESSION['otp_sent']) ? 'show' : ''; ?>">
    <div class="otp-container">
        <div class="otp-header">
            <h3>OTP Verification</h3>
            <button class="close-btn" onclick="closeOTP()">&times;</button>
        </div>
        <div class="label-verification">
            <label>We've sent a verification code to your email. Enter the 6-digit code below.</label>
        </div>
        <form method="POST" action="" class="otp-form">
            <div class="otp-inputs">
                <input type="text" name="otp_digit_1" maxlength="1" pattern="\d*" required>
                <input type="text" name="otp_digit_2" maxlength="1" pattern="\d*" required>
                <input type="text" name="otp_digit_3" maxlength="1" pattern="\d*" required>
                <input type="text" name="otp_digit_4" maxlength="1" pattern="\d*" required>
                <input type="text" name="otp_digit_5" maxlength="1" pattern="\d*" required>
                <input type="text" name="otp_digit_6" maxlength="1" pattern="\d*" required>
            </div>
            <button type="submit" name="verify_otp" class="submit">Verify OTP</button>
        </form>
    </div>
</div>


<script>
    const accountTypeSelect = document.getElementById('account_type');
    const groupMembersTextarea = document.getElementById('group_members');

    // Function to handle account type change
    function handleAccountTypeChange() {
        if (accountTypeSelect.value === 'Group Account') {
            groupMembersTextarea.classList.add('show');
            groupMembersTextarea.required = true;
        } else {
            groupMembersTextarea.classList.remove('show');
            groupMembersTextarea.required = false;
        }
    }

    // Add event listener for account type change
    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', handleAccountTypeChange);
        // Check initial value
        handleAccountTypeChange();
    }

    document.querySelectorAll('.otp-inputs input').forEach((input, index, inputs) => {
    input.addEventListener('input', () => {
        if (input.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
    });
});


  function closeOTP() {
  window.location.href = window.location.pathname + '?close_otp=true';
}




    <?php if (isset($_SESSION['registration_success'])): ?>
        alert("Registration successful! You can now log in.");
        <?php unset($_SESSION['registration_success']); ?>
    <?php endif; ?>

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
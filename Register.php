<?php
session_start();
include "database.php";
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer and OTP functions
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

    $stmt = $conn->prepare("SELECT * FROM registeredacc WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['email_exists'] = true;
        header("Location: Register.php");
        exit;
    } else {
        $_SESSION['temp_username'] = $_POST['username'];
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

    $stmt->close();
}

// Handle OTP verification
if (isset($_POST['verify_otp'])) {
    $enteredOtp = $_POST['otp_verification'];

    if ($enteredOtp == $_SESSION['otp']) {
        $stmt = $conn->prepare("CALL insertRegisteredAccount(?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssss",
            $_SESSION['temp_username'],
            $_SESSION['temp_email'],
            $_SESSION['temp_password'],
            $_SESSION['temp_account_type'],
            $_SESSION['temp_group_members']
        );

        if ($stmt->execute()) {
            session_unset();
            session_start();
            $_SESSION['otp_success'] = true;
            header("Location: Register.php");
            exit;
        } else {
            echo "<script>alert('Database Error: " . addslashes($stmt->error) . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
        header("Location: Register.php");
        exit;
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

<div class="navbar">
    <div class="brand">GREENTRACK</div>
    <div>
        <a class="home-button" href="Homepage.php">
            <i class="fa-solid fa-house"></i>
        </a>
    </div>
</div>

<div class="form-container">
    <div class="register-section">
        <h2>Register</h2>
        <form method="POST" action="">
            <label>Fill up the form to register an account.</label>

            <input type="text" name="username" placeholder="Username" required>

            <?php if (isset($_SESSION['email_exists']) && $_SESSION['email_exists']): ?>
                <div class="error-message">
                    This email is already registered. Please use a different email.
                </div>
                <?php unset($_SESSION['email_exists']); ?>
            <?php endif; ?>
            <input type="email" name="email" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <select name="account_type" id="account_type" required>
                <option value="">Select account type</option>
                <option value="individual">Individual</option>
                <option value="groupaccount">Group Account</option>
            </select>

            <textarea id="group_members" name="group_members" class="group-members" placeholder="Enter group member names..."></textarea>

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
        <h3>OTP Verification</h3>
        <div class="label-verification">
            <label>We've sent a verification code to your email. Kindly enter the 6-digit code below.</label>
        </div>
        <form method="POST" action="">
            <input type="text" name="otp_verification" placeholder="Enter OTP" required maxlength="6" minlength="6" pattern="\d{6}">
            <div class="otp-buttons">
                <button type="submit" name="verify_otp" class="submit">Verify OTP</button>
                <button type="button" onclick="cancelOTP()" class="cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    const accountTypeSelect = document.getElementById('account_type');
    const groupMembersTextarea = document.getElementById('group_members');

    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', function () {
            if (this.value === 'groupaccount') {
                groupMembersTextarea.classList.add('show');
            } else {
                groupMembersTextarea.classList.remove('show');
            }
        });
    }

    function cancelOTP() {
        window.location.href = 'Register.php'; // Clear OTP session
    }

    <?php if (isset($_SESSION['otp_success'])): ?>
        alert("Registration successful! You can now log in.");
        <?php unset($_SESSION['otp_success']); ?>
    <?php endif; ?>
</script>

</body>
</html>

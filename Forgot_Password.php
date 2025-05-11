<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenTrack - Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="Login.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php 
require_once 'procedure.php';
require_once 'database.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

// Create database connection
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    // Check if email exists
    if ($crud->checkEmailExists($email)) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store the token in the database
        $stmt = $conn->prepare("CALL StorePasswordResetToken(?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expiry);
        $stmt->execute();
        $stmt->close();
        
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'greentrackweb@gmail.com';
            $mail->Password   = 'vmwg jgub wgrf oxkk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            // Recipients
            $mail->setFrom('greentrackweb@gmail.com', 'GreenTrack');
        $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - GreenTrack';
            
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/ADBMS-Suarez/Reset_Password.php?token=" . $token;
            
            $mail->Body = "
                <h2>Password Reset Request</h2>
                <p>Hello,</p>
                <p>You have requested to reset your password. Click the link below to reset your password:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request this password reset, please ignore this email.</p>
                <br>
                <p>Best regards,<br>GreenTrack Team</p>
            ";

            $mail->send();
            $message = "Password reset instructions have been sent to your email.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Failed to send reset email. Please try again. Error: {$mail->ErrorInfo}";
            $messageType = "error";
        }
    } else {
        $message = "Email not found in our records.";
        $messageType = "error";
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

<?php if ($message): ?>
    <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?> alert-front">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <div class="login-section">
        <h2>Forgot Password</h2>
        <form method="POST" action="Forgot_Password.php">
            <label>Enter your email address</label>
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder="Email" required>
            </div>
            <button type="submit" class="login">Send Reset Link</button>
            <div class="no-account">
                Remember your password? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>
    <div class="welcome-section"></div>
</div>

</body>
</html> 
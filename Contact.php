<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Get In Touch</title>
  <link rel="stylesheet" type="text/css" href="Contact.css?v=<?php echo time(); ?>">
</head>
<body>

<?php
require_once 'procedure.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message_type = $_POST['message_type'] ?? '';
    $message = $_POST['message'] ?? '';

    // Insert message using the Crud class
    if ($crud->insertMessage($name, $email, $message_type, $message)) {
        echo "<script>alert('Your message has been sent successfully!'); window.location.href='Contact.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error: Failed to send message');</script>";
    }
}
?>

<header>
<div class="logo-container">
    <img src="LOGO.png" class="logo" />
    <h1><span>GREEN</span>TRACK</h1>
  </div>
  <button class="home-btn" onclick="window.location.href='Homepage.php'">Home</button>
</header>

<div class="contact-container">
  <div class="form-section">
    <h1>Get In Touch</h1>
    <p>We are Here for You, How can we Help?</p>
    <form action="Contact.php" method="POST">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" placeholder="Your Name" required>
      
      <label for="email">Email</label>
      <input type="email" id="email" name="email" placeholder="Your Email" required>
      
      <label for="message-type">Message Type</label>
      <select id="message-type" name="message_type" required>
        <option value="">Select Message Type</option>
        <option value="General Inquiry">General Inquiry</option>
        <option value="Support">Support</option>
        <option value="Feedback">Feedback</option>
      </select>
      
      <label for="message">Message</label>
      <textarea id="message" name="message" placeholder="Your Message" required></textarea>
      
      <button type="submit" class="submit-btn">Submit</button>
    </form>
  </div>

  <div class="contact-info">
    <img src="logo.png" class="logo-1">
    <h2>Contact Us</h2>
    <div class="contact-option">
      <div class="circle"></div>
      <span>09123456789</span>
    </div>
    <div class="contact-option">
      <div class="circle"></div>
      <span>greentrack@gmail.com</span>
    </div>
  </div>
</div>

<script>

  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('header');
    header.classList.add('animate-slide');
  });


</script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="Contact.css">
</head>
<body>

<div id="contact" class="contact">
        <h2>Contact Us</h2>
        <form action="#" method="POST">
          
          <label for="name">Name</label>
          <input type="text" id="name" name="name" required>
      
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
      
          <label for="messageType">Message Type</label>
          <select id="messageType" name="messageType" required>
            <option value="">Select Message type</option>
            <option value="General Inquiry">General Inquiry</option>
            <option value="Support Request">Support Request</option>
            <option value="Feedback">Feedback</option>
            <option value="Other">Other</option>
          </select>
      
          <label for="message">Message</label>
          <textarea id="message" name="message" rows="5" required></textarea>
      
          <button type="submit">Send Message</button>
        </form>
</div>

    
</body>
</html>
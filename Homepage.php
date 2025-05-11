<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenTrack Homepage</title>
  <link rel="stylesheet" type="text/css" href="Homepage.css?v=<?php echo time(); ?>">
</head>

<?php 
session_start();
require_once 'database.php';
require_once 'procedure.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

$totalTrees = 0;
$totalCO2 = 0;

$totalResult = $conn->query("CALL Total()");

if ($totalResult) {
    $row = $totalResult->fetch_assoc();
    $totalTrees = $row['TotTree'];
    $totalCO2 = $row['TotCo2'];

    mysqli_free_result($totalResult);
    $conn->next_result();
}

// If user is logged in and clicks logout
if (isset($_SESSION['user_id'])) {
    // Update logout time in user_logs
    $stmt = $conn->prepare("CALL UpdateLogoutTime(?)");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    
    // Clear session
    session_destroy();
}

?>

  <body>
  <header data-scroll>
  <div class="logo-container">
    <img src="LOGO.png" class="logo" />
    <h1><span>GREEN</span>TRACK</h1>
  </div>

  <nav class="main-nav">
    <a href="#home">Home</a>
    <a href="#about">About</a>
    <a href="#how">How It Works</a>
    <div class="dropdown">
      <a href="#" class="dropdown-trigger">Learn More</a>
      <div class="dropdown-content">
        <a href="Contact.php">Contact</a>
        <a href="#learn">Discover</a>
        <a href="Leaderboard.php">Leaderboard</a>
        <a href="about_us.php">About Us</a>
      </div>
    </div>
    <a href="Login.php" class="login-btn">Login</a>
  </nav>
</header>



  <section id="home" class="home">
    <div class="welcome">
      <h2>Welcome to GreenTrack</h2>
      <p>GreenTrack is a system that supports and promotes tree planting and reforestation efforts.<br>
        The system allows organizations, volunteers, and community members to  register <br> and the trees they plant, 
        track their progress, and measure the positive environmental impact, <br> especially in terms of carbon dioxide (CO₂) reduction.</p>
        <a href="Register.php" class="join-a"><button class="join-btn">Join Us</button></a>
    </div>
  </section>


  <section id="about" class="about">
    <h2>About GreenTrack</h2>
      <p>About GreenTrack: GreenTrack is a digital platform designed to accelerate global reforestation efforts and support Sustainable Development Goal (SDG) 13: Climate Action. 
        We empower individuals, organizations, and communities to take meaningful action against climate change-one tree at a time.<p><br>
  
      <p>Through GreenTrack, users can register the trees they plant, contribute to verified reforestation projects, and monitor their impact on the environment, particularly in reducing carbon dioxide (CO2) levels.
        Each tree planted is tracked using the system, allowing users to follow its growth and share their climate journey with others.<p><br>

      <p>Whether you're a volunteer, student, local government, or corporate entity, GreenTrack helps you be part of the solution. 
        By planting trees and tracking their progress, you're not only restoring ecosystems-you're also building a greener, more sustainable future for generations to come.
        Join us. Plant a tree. Track your impact. Take climate action.<p>
  </section>

  <section id="how" class="hiw">
    <h2>How It Works</h2>
      <div class="steps">
        <div class="step-box-1" >
          <div class="step-text">
            <h2>Sign Up and<br />Choose a Project</h2>
            <p>Join the GreenTrack community and explore verified tree planting and reforestation initiatives. Select a project aligned with your values and SDG 13: Climate Action, and dedicate your efforts to making a real impact.</p>
          </div>
        </div>
        <div class="step-box-2">
          <div class="step-text">
            <h2>Make a Contribution<br />To Environment<br /></h2>
            <p style="height">Decide how many trees you want to plant or support. Whether you're contributing one tree or one thousand, each action counts toward reducing CO₂ and healing the planet.</p>
          </div>
        </div>
        <div class="step-box-3">
          <div class="step-text">
            <h2>Track and<br />Share Your Impact</h2>
            <p>Register your trees in the GreenTrack system and monitor their growth. Watch your reforestation journey unfold and share your environmental impact with others to inspire collective climate action.</p>
          </div>
        </div>
      </div>
      <h1 class="learn-more">This innovative platform encourages reforestation and tree planting to combat climate change and protect the environment.</h1>
      <a href="#learn"><button class="learn-btn">Learn more about our system</button></a>
</section>


<section id="learn" class="learn">
  <div class="container">
    <h2>Discover the Impact</h2>
    <div class="learn-content">
      
      <div class="info-text">
        <p>Our platform is more than just a tool—it's a movement for environmental restoration. We empower individuals, communities, and organizations to join hands in planting trees and revitalizing nature.</p>
        <div class="highlights">
          <div class="highlight-item">
            <h4>🌱 Eco-Friendly Action</h4>
            <p>Plant trees with a purpose—each tree contributes to reducing carbon emissions and restoring biodiversity.</p>
          </div>
          <div class="highlight-item">
            <h4>📊 Real-Time Tracking</h4>
            <p>Monitor your planting progress and see the collective impact of our global community.</p>
          </div>
          <div class="highlight-item">
            <h4>📍 Community Engagement</h4>
            <p>Participate in local planting events or start your own project through our platform.</p>
          </div>
          <div class="highlight-item">
            <h4>📈 Environmental Data</h4>
            <p>Access reports showing total trees planted, CO₂ reductions, and more—all backed by reliable data.</p>
          </div>
        </div>
        <p class="contact-link">Want to collaborate with us or have questions? <a href="Contact.php">Click here to contact us</a>.</p>
      </div>

      <div class="stats-box">
        <h3>Total Trees Planted</h3>
        <div class="tree-count">
          <span class="count"><?php echo $totalTrees; ?></span>
          <p>Growing stronger every day</p>
        </div>
        <div class="stat-desc">
          <p>This number represents our shared success in creating a greener future. Each tree planted makes a difference.</p>
        </div>
        <a href="Leaderboard.php" class="btn">See Leaderboard</a>
      </div>
      
    </div>
  </div>
</section>




      <footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-logo">
        <h2>GreenTrack</h2>
        <p>Sustainable Development.</p>
      </div>
      <div class="footer-section">
        <h3>FAQs</h3>
        <div class="faq">
          <details>
            <summary>What is GreenTrack?</summary>
            <p>GreenTrack is a platform promoting tree planting and environmental awareness.</p>
          </details>
          <details>
            <summary>How can I register?</summary>
            <p>You can register through the Sign-Up page and start your green journey.</p>
          </details>
          <details>
            <summary>Can I track my planted trees?</summary>
            <p>Yes! Your dashboard shows all your planting history and stats.</p>
          </details>
          <details>
            <summary>Is GreenTrack free to use?</summary>
            <p>Yes, it's completely free for all users.</p>
          </details>
          <details>
            <summary>How does GreenTrack help the environment?</summary>
            <p>We partner with organizations to plant real trees and educate communities.</p>
          </details>
        </div>
      </div>
      <div class="footer-section">
        <h3>Explore</h3>
        <ul>
          <li><a href="#about">About Us</a></li>
          <li><a href="#how">How It Works</a></li>
          <li><a href="Contact.php">Contact</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 GreenTrack. All rights reserved.</p>
    </div>
  </div>
</footer>






      <script src="https://unpkg.com/scroll-out/dist/scroll-out.min.js"></script>
<script>
  ScrollOut({
    targets: '[data-scroll]',
  });

  window.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('header');
    header.classList.add('animate-slide');

    // Add dropdown click handler
    const dropdownTrigger = document.querySelector('.dropdown-trigger');
    const dropdown = document.querySelector('.dropdown');

    dropdownTrigger.addEventListener('click', (e) => {
      e.preventDefault();
      dropdown.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('active');
      }
    });
  });


</script>



    
</body>
</html>




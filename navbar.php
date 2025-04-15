<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Volunteer Dashboard</title>
  <link rel="stylesheet" href="navbar.css" />
</head>
<body>

  <nav class="navbar">
    <div class="container">
      <a class="navbar-brand" href="home.php">GREENTRACK</a>
      <button class="navbar-toggler" id="navbarToggle">&#9776;</button>
      <ul class="navbar-menu" id="navbarMenu">
        <li><a class="nav-link" href="home.php">HOME</a></li>
        <li><a class="nav-link" href="about.php">ABOUT</a></li>
        <li><a class="nav-link" href="#">HOW IT WORKS</a></li>
        <li><a class="nav-link" href="#">CONTACT</a></li>
        <li><a class="nav-link border-button" href="#">VOLUNTEER PANEL</a></li>
      </ul>
    </div>
  </nav>

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <aside class="left-sidebar" id="leftSidebar">
    <div class="sidebar-section">
      <h3>Manage Account</h3>
      <a href="home.php">👤 My Account</a>
      <a href="treeplantingform.php">📄 Tree Planting Form</a>
    </div>
    <div class="sidebar-section">
      <h3>Submission</h3>
      <a href="viewsubmission.php">🌐 View Submission</a>
      <a href="editsubmission.php">🌐 Edit Submission</a>
      <a href="updatesubmission.php">🌐 Update Submission</a>
    </div>
    <div class="sidebar-section">
      <h3>Approval</h3>
      <a href="totaltrees.php">🌐 Total Trees Planted</a>
      <a href="estimate.php">🌐 CO₂ Absorbed Estimate</a>
    </div>
  </aside>


  <script>
    const toggle = document.getElementById('navbarToggle');
    const menu = document.getElementById('navbarMenu');
    const sidebar = document.getElementById('leftSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    toggle.addEventListener('click', () => {
      menu.classList.toggle('active');
    });

    const openSidebarLinks = document.querySelectorAll('.nav-link');

    openSidebarLinks.forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          sidebar.classList.add('active');
          overlay.classList.add('active');
        }
      });
    });

    overlay.addEventListener('click', () => {
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
    });
  </script>
</body>
</html>

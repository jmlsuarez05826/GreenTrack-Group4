<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - Tree Planting Form</title>
    <link rel="stylesheet" type="text/css" href="treeplantingform.css?v=<?php echo time(); ?>"> 
    <script>
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <span class="icon">${type === 'success' ? '✓' : '✕'}</span>
                <span class="message">${message}</span>
                <span class="close-notification" onclick="this.parentElement.remove()">×</span>
            `;
            document.body.appendChild(notification);
            
            // Trigger animation
            setTimeout(() => notification.classList.add('show'), 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    </script>
</head>
<body>
<?php 
session_start();
require_once 'database.php';
require_once 'procedure.php';

if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Create database connection
$db = new Database();
$conn = $db->getConnection();

// Get user account info
$stmt = $conn->prepare("CALL GetAccountInfo(?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Clear the result set
$result->free();
$stmt->close();
$conn->next_result();

if ($user) {
    $username = $user['username'];
    $email = $user['email'];
    $role = $user['role'];
    $is_active = $user['status'];
    $account_type = $user['account_type'];
    $group_members = $user['group_members'];
    $profile = $user['profile'];
} else {
    $username = "N/A";
    $email = "N/A";
    $role = "N/A";
    $is_active = 0;
    $account_type = "N/A";
    $group_members = "N/A";
    $profile = "uploads/default.png";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tree_type'], $_POST['number'], $_POST['date'], $_POST['location'])) {
    // Debug logging
    error_log("Form submission started");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    $tree_type = $_POST['tree_type'];
    $number = (int)$_POST['number'];
    $created_at = $_POST['date'];
    $location = $_POST['location'];
    $other_location = ($location === 'OTHER') ? $_POST['other_location'] : null;
    $image_path = '';

    // Debug logging
    error_log("Processed data:");
    error_log("Tree Type: " . $tree_type);
    error_log("Number: " . $number);
    error_log("Created At: " . $created_at);
    error_log("Location: " . $location);
    error_log("Other Location: " . $other_location);
    error_log("User ID: " . $user_id);

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/';
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $image_path = $upload_dir . $image_name;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            error_log("Failed to move uploaded file");
            $_SESSION['error_message'] = "Error uploading image.";
            header("Location: treeplantingform.php");
            exit();
        }
        error_log("Image uploaded successfully: " . $image_path);
    }

    try {
        error_log("Attempting to submit tree planting data");
        error_log("Using Crud instance: " . (isset($crud) ? "Yes" : "No"));
        
        // First check if the tree_types table exists
        $result = $conn->query("SHOW TABLES LIKE 'tree_types'");
        if ($result->num_rows == 0) {
            throw new Exception("tree_types table does not exist");
        }

        // Check if tree_plantings table exists
        $result = $conn->query("SHOW TABLES LIKE 'tree_plantings'");
        if ($result->num_rows == 0) {
            throw new Exception("tree_plantings table does not exist");
        }

        // Check if the stored procedure exists
        $result = $conn->query("SHOW PROCEDURE STATUS WHERE Name = 'tree_planting_data'");
        if ($result->num_rows == 0) {
            throw new Exception("tree_planting_data stored procedure does not exist");
        }

        // Get table structure for debugging
        $result = $conn->query("DESCRIBE tree_plantings");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'] . ' (' . $row['Type'] . ')';
        }
        error_log("Tree plantings table structure: " . implode(', ', $columns));

        // Get stored procedure parameters
        $result = $conn->query("SELECT PARAMETER_NAME, DATA_TYPE FROM information_schema.PARAMETERS WHERE SPECIFIC_NAME = 'tree_planting_data'");
        $params = [];
        while ($row = $result->fetch_assoc()) {
            $params[] = $row['PARAMETER_NAME'] . ' (' . $row['DATA_TYPE'] . ')';
        }
        error_log("Stored procedure parameters: " . implode(', ', $params));

        error_log("All required database objects exist, proceeding with submission");
        
        // Call the stored procedure directly
        $stmt = $conn->prepare("CALL tree_planting_data(?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        error_log("Binding parameters...");
        error_log("Parameters to bind: tree_type=$tree_type, number=$number, created_at=$created_at, location=$location, image_path=$image_path, user_id=$user_id, other_location=$other_location");
        
        $stmt->bind_param("sisssis", 
            $tree_type,
            $number,
            $created_at,
            $location,
            $image_path,
            $user_id,
            $other_location
        );
        
        error_log("Executing stored procedure...");
        if (!$stmt->execute()) {
            $error = $stmt->error;
            error_log("MySQL Error: " . $error);
            throw new Exception("Execute failed: " . $error);
        }

        error_log("Stored procedure executed successfully");
        
        // Clear the result set
        $stmt->close();
        $conn->next_result();
        
        $_SESSION['success_message'] = "Tree planting record submitted successfully!";
        error_log("Success message set in session");
        
        header("Location: treeplantingform.php");
        exit();
    } catch (Exception $e) {
        error_log("Error in tree planting submission: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: treeplantingform.php");
        exit();
    }
}

// Add this at the top of the file after session_start()
if (isset($_SESSION['success_message'])) {
    echo "<script>showNotification('" . $_SESSION['success_message'] . "', 'success');</script>";
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo "<script>showNotification('" . $_SESSION['error_message'] . "', 'error');</script>";
    unset($_SESSION['error_message']);
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
</header>

<div class="container">
    <div class="sidebar">
        <ul>
            <h3>Manage Account</h3>
            <li><a href="volunteer.php"> 👤 My Account</a></li>
        </ul>
        <ul>
            <h3>Submission</h3>
            <li><a class="active" href="treeplantingform.php">📄 Tree Planting Form</a></li>
            <li><a href="viewsubmission.php">🌐 View Submission</a></li>
            <li><a href="editsubmission.php">🌐 Edit Submission</a></li>
            <li><a href="cancel.php">🌐 Cancel Submission</a></li>
        </ul>
        <h3>View</h3>
        <ul>
            <li><a href="totaltrees.php">🌐 Total Trees Planted</a></li>
            <li><a href="estimate.php">🌐 CO₂ Absorbed Estimate</a></li>
        </ul>
        <div class="sidebar1">
            <ul>
                <li><a class="logout" href="Homepage.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="form-wrapper">
    <div class="form-header">Tree Planting Form</div>
    <div class="form-container">
        <div id="alert" class="alert">
            <span class="icon">✓</span>
            <span class="message"></span>
            <span class="close-alert">&times;</span>
        </div>

        <form id="plantingForm" method="POST" enctype="multipart/form-data" style="display: flex; width: 100%;">
            <div class="left-section">
                <div class="form-group">
                    <label for="tree_type">Tree Types:</label>
                    <select id="tree_type" name="tree_type" required onchange="showDescription()">
                        <option value="" disabled selected>Select a tree type</option>
                        <option value="Narra" data-desc="Narra is a hardwood tree native to the Philippines and absorbs ~21.8 kg of CO₂ per year.">Narra (~21.8 kg CO₂/year)</option>
                        <option value="Mahogany" data-desc="Mahogany is a fast-growing hardwood tree that absorbs ~28.0 kg of CO₂ per year.">Mahogany (~28.0 kg CO₂/year)</option>
                        <option value="Molave" data-desc="Molave is a durable native tree that absorbs ~25.0 kg of CO₂ per year.">Molave (~25.0 kg CO₂/year)</option>
                        <option value="Acacia" data-desc="Acacia grows quickly and absorbs ~35.0 kg of CO₂ per year.">Acacia (~35.0 kg CO₂/year)</option>
                        <option value="Yakal" data-desc="Yakal is a tough native tree that absorbs ~20.0 kg of CO₂ per year.">Yakal (~20.0 kg CO₂/year)</option>
                        <option value="Ipil-ipil" data-desc="Ipil-ipil is fast-growing and absorbs ~15.0 kg of CO₂ per year.">Ipil-ipil (~15.0 kg CO₂/year)</option>
                        <option value="Bamboo" data-desc="Bamboo grows very fast and absorbs ~62.0 kg of CO₂ per year.">Bamboo (~62.0 kg CO₂/year)</option>
                        <option value="Banaba" data-desc="Banaba has medicinal properties and absorbs ~22.5 kg of CO₂ per year.">Banaba (~22.5 kg CO₂/year)</option>
                        <option value="Talisay" data-desc="Talisay grows well near coasts and absorbs ~18.0 kg of CO₂ per year.">Talisay (~18.0 kg CO₂/year)</option>
                        <option value="Balete" data-desc="Balete is iconic for its large roots and absorbs ~30.0 kg of CO₂ per year.">Balete (~30.0 kg CO₂/year)</option>
                    </select>
                </div>
                <div id="tree-description" class="tree-description-box" style="display: none;"></div>

                <div class="form-group">
                    <label for="number">Number:</label>
                    <input type="number" id="number" name="number" min="1" required>
                </div>

                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="text" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" readonly required>
                </div>
            </div>

            <div class="right-section">
                <div class="form-group">
                    <label for="location">Location:</label>
                    <select id="location" name="location" required onchange="toggleOtherLocation()">
                        <option value="" disabled selected>Select a location</option>
                        <option value="LIPA">LIPA</option>
                        <option value="SAN JUAN">SAN JUAN</option>
                        <option value="BATANGAS CITY">BATANGAS CITY</option>
                        <option value="TAAL">TAAL</option>
                        <option value="NASUGBU">NASUGBU</option>
                        <option value="OTHER">OTHER</option>
                    </select>
                    <div id="otherLocationDiv" style="display: none; margin-top: 10px;">
                        <input type="text" id="other_location" name="other_location" placeholder="Enter other location">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Proof Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <button type="button" class="submit-btn" onclick="openModal()">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div id="confirmationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="margin">
            <!-- TOP HEADER -->
            <div class="modal-header">
                <button onclick="closeModal()" class="x-button">&times;</button>
                <img src="LOGO.png" alt="GreenTrack Logo" class="logo">
                <h1>GreenTrack System</h1>
                <h4>Go Green, Grow Green - For Better Track</h4>
                <hr class="divider">
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <div class="form-section">
                    <h3>System Information</h3>
                    <p><strong>System Name:</strong> GreenTrack</p>
                    <p><strong>Purpose:</strong> The GreenTrack system helps volunteers track and report tree planting activities.</p>
                </div>

                <div class="form-section">
                    <h3>User Information</h3>
                    <p><strong>Username:</strong> <?php echo isset($username) ? $username : 'N/A'; ?></p>
                    <p><strong>Email:</strong> <?php echo isset($email) ? $email : 'N/A'; ?></p>

                    <?php if ($account_type == "Group Account"): ?>
                        <p><strong>Group Members:</strong> <?php echo isset($group_members) ? $group_members : 'N/A'; ?></p>
                    <?php endif; ?>
                </div>

                <div class="form-section">
                    <h3>Form Details</h3>
                    <p><strong>Tree Type:</strong> <span id="treeTypeDisplay"></span></p>
                    <p><strong>Number of Trees:</strong> <span id="numberDisplay"></span></p>
                    <p><strong>Date Planted:</strong> <span id="dateDisplay"></span></p>
                    <p><strong>Location:</strong> <span id="locationDisplay"></span></p>
                    <p><strong>Proof Image:</strong> <img id="imageDisplay" src="" alt="Proof Image" class="image-preview"></p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeModal()">Cancel</button>
                <button class="agree-btn" onclick="submitForm()">Agree & Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to show tree description
    function showDescription() {
        const select = document.getElementById('tree_type');
        const descBox = document.getElementById('tree-description');
        const selected = select.options[select.selectedIndex];
        
        if (selected.value) {
            const desc = selected.getAttribute('data-desc');
            descBox.innerText = desc;
            descBox.style.display = 'block';
        } else {
            descBox.style.display = 'none';
        }
    }

    // Function to toggle other location input
    function toggleOtherLocation() {
        const locationSelect = document.getElementById('location');
        const otherLocationDiv = document.getElementById('otherLocationDiv');
        const otherLocationInput = document.getElementById('other_location');
        
        if (locationSelect.value === 'OTHER') {
            otherLocationDiv.style.display = 'block';
            otherLocationInput.required = true;
        } else {
            otherLocationDiv.style.display = 'none';
            otherLocationInput.required = false;
            otherLocationInput.value = '';
        }
    }

    // Function to open modal
    function openModal() {
        const form = document.getElementById('plantingForm');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const treeType = document.getElementById('tree_type').value;
        const number = document.getElementById('number').value;
        const date = document.getElementById('date').value;
        const location = document.getElementById('location').value;
        const otherLocation = location === 'OTHER' ? document.getElementById('other_location').value : '';
        const imageFile = document.getElementById('image').files[0];

        // Fill the modal with the form data
        document.getElementById('treeTypeDisplay').textContent = treeType;
        document.getElementById('numberDisplay').textContent = number;
        document.getElementById('dateDisplay').textContent = date;
        document.getElementById('locationDisplay').textContent = location === 'OTHER' ? otherLocation : location;

        if (imageFile) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imageDisplay').src = e.target.result;
            };
            reader.readAsDataURL(imageFile);
        }

        // Display the modal
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'flex';
    }

    // Function to close modal
    function closeModal() {
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'none';
    }

    // Function to submit form
    function submitForm() {
        const form = document.getElementById('plantingForm');
        if (form.checkValidity()) {
            form.submit();
        } else {
            form.reportValidity();
        }
    }

    // Show success/error messages if they exist
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['success_message'])): ?>
            showAlert('<?php echo $_SESSION['success_message']; ?>', 'success');
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            showAlert('<?php echo $_SESSION['error_message']; ?>', 'error');
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    });

    function showAlert(message, type = 'success') {
        const alertElement = document.getElementById('alert');
        alertElement.className = `alert ${type}`;
        alertElement.querySelector('.message').textContent = message;
        alertElement.classList.add('show');
        
        setTimeout(() => {
            alertElement.classList.remove('show');
        }, 3000);
    }

    document.querySelector('.close-alert').addEventListener('click', function() {
        document.getElementById('alert').classList.remove('show');
    });
</script>

<style>
.alert {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.alert.show {
    transform: translateX(0);
}

.alert.success {
    border-left: 4px solid #4CAF50;
}

.alert.error {
    border-left: 4px solid #dc3545;
}

.alert .icon {
    font-size: 20px;
}

.alert.success .icon {
    color: #4CAF50;
}

.alert.error .icon {
    color: #dc3545;
}

.alert .message {
    color: #333;
    font-size: 14px;
    font-weight: 500;
}

.alert .close-alert {
    margin-left: 15px;
    cursor: pointer;
    color: #666;
    font-size: 18px;
    padding: 4px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.alert .close-alert:hover {
    background-color: #f5f5f5;
    color: #333;
}
</style>
</body>
</html>
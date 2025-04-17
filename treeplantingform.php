<?php
$host = 'localhost';
$db = 'greentrack';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$submission_status = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tree_type = $_POST['tree_type'];
    $number = $_POST['number'];
    $date = $_POST['date'];
    $location = $_POST['location'];

    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = 'uploads/';
        $image_name = preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", basename($_FILES['image']['name']));
        $image_path = $upload_dir . $image_name;

        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            if ($_FILES['image']['size'] <= 2 * 1024 * 1024) {
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    echo "Error: Failed to move uploaded file.";
                    exit;
                }
            } else {
                echo "Error: Image file is too large. Maximum size is 2MB.";
                exit;
            }
        } else {
            echo "Error: Only JPG, PNG, or GIF files are allowed.";
            exit;
        }
    }

    $stmt = $conn->prepare("CALL tree_planting_data(?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $tree_type, $number, $date, $location, $image_path);

    if ($stmt->execute()) {
        $submission_status = "success"; 
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;  
    } else {
        $submission_status = "error";  
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - Tree Planting Form</title>
    <link rel="stylesheet" href="treeplantingform.css">
    
</head>
<body>
    <?php include "navbar.php";?>

    <div class="form-wrapper">
        <div class="form-header">Tree Planting Form</div>
        <div class="form-container">
            <form action="#" method="POST" enctype="multipart/form-data">

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
                    <input type="number" id="number" name="number" required>
                </div>

                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                </div>

                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" required>
                </div>

                <div class="form-group">
                    <label for="image">Proof Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>

                <button type="submit" class="submit-btn">Submit</button>
            </form>
        </div>
    </div>

    <script>
        function showDescription() {
            const select = document.getElementById('tree_type');
            const descBox = document.getElementById('tree-description');
            const selected = select.options[select.selectedIndex];
            const desc = selected.getAttribute('data-desc');
            
            if (desc) {
                descBox.innerText = desc;
                descBox.style.display = 'block';
            } else {
                descBox.innerText = '';
                descBox.style.display = 'none';
            }
        }

        <?php if($submission_status == "success"): ?>
        window.onload = function() {
            showModal('Success', 'Tree planting data has been successfully submitted.', 'success');
        };
        <?php elseif($submission_status == "error"): ?>
            window.onload = function() {
                showModal('Error', 'Error: Something went wrong. Please try again.', 'error');
            };
        <?php endif; ?>
    </script>

</body>
</html>

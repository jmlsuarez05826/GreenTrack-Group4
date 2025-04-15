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
                    <input type="text" id="tree_type" name="tree_type" required>
                </div>

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

</body>
</html>

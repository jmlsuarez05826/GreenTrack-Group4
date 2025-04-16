<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenTrack - Register</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('forestforest.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            padding: 20px 40px;
            background: rgba(57, 82, 41, 0.99);
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 30px;
            font-weight: bold;
        }
        .navbar a.button {
            border: 1px solid white;
            border-radius: 10px;
            padding: 5px 15px;
            background-color: rgba(0, 0, 0, 0.2);
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .brand {
            font-size: 28px;
            font-weight: bold;
        }
        .form-container {
            max-width: 400px;
            margin: 100px auto;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            border: 2px solid #ccc;
            color: black;
        }

        .tab-buttons {
            display: flex;
            background: #dee;
            border-radius: 20px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }

       .tab-buttons a {
            flex: 1;
            padding: 10px;
            border: none;
            background: rgb(209, 216, 204);
            color: white;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-family: Tahoma, sans-serif;
            transition: 0.3s ease-out;
        }

        .tab-buttons a:hover{
            color: black;
            background-color:rgb(84, 105, 81);
            padding: 12px;
        }


        .tab-buttons .active {
            background:rgb(84, 105, 81);
            color: black;
        }
        .form-container input, .form-container select, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-bottom: 1px solid black;
            background: transparent;
            color: black;
        }
        .form-container textarea {
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }
        .form-container button.submit {
            background: black;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include "database.php"; 

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $accountType  = $_POST['account_type'];
    $groupMembers = isset($_POST['group_members']) ? $_POST['group_members']: null;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO registeredacc (username, email, password, account_type, group_members) 
    VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $email, $hashedPassword, $accountType, $groupMembers);

        if ($stmt->execute()) {
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                Swal.fire({
                icon: 'success',
                title: 'Registration Successful!',
                text: 'You can now log in.',
                confirmButtonColor: '#3085d6'
            }).then(() => {
            window.location.href = 'Register.php';
        });
    </script>";
            exit();
        }

    }
?>



    <div class="navbar">
        <div class="brand">GREENTRACK</div>
        <div>
            <a class="button" href="Homepage.php">Back</a>
        </div>
    </div>

    <div class="form-container">
        <div class="tab-buttons">
            <a href= "Login.php" >Log In</a>
            <a href="" class="active">Register</a>
        </div>
        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            
            <select name="account_type" id="account_type" required>
                <option value="">Select account type</option>
                <option value="Individual">Individual</option>
                <option value="Group">Group  Account</option>
            </select>

            <textarea id="group_members" name="group_members" placeholder="Enter group member names..." style="display: none;"></textarea>

            <button type="submit" class="submit">Register</button>
        </form>
    </div>

    <script>
        const accountTypeSelect = document.getElementById('account_type');
        const groupMembersTextarea = document.getElementById('group_members');

        accountTypeSelect.addEventListener('change', function () {
            if (this.value === 'groupaccount') {
                groupMembersTextarea.style.display = 'block';
            } else {
                groupMembersTextarea.style.display = 'none';
            }
        });
    </script>
</body>
</html>

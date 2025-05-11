<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenTrack - Admin Account</title>
    <link rel="stylesheet" type="text/css" href="Manage.css?v=<?php echo time(); ?>">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <!-- Add SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        function filterTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let rows = document.querySelectorAll(".user-table tr:not(:first-child)");
            let noResults = document.querySelector('.no-results');
            let visibleRows = 0;

            rows.forEach(row => {
                let username = row.cells[1].textContent.toLowerCase();
                let email = row.cells[2].textContent.toLowerCase();
                let isVisible = (username.includes(filter) || email.includes(filter));
                row.style.display = isVisible ? "" : "none";
                if (isVisible) visibleRows++;
            });

            // Show/hide no results message
            if (visibleRows === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
    </script>
</head>
<body>

<?php
require_once 'procedure.php';

// Handle update and delete operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $user_id = $_POST['user_id'];
                $username = $_POST['username'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $account_type = $_POST['account_type'];
                $group_members = '';
                
                // Check if username exists for other users
                if ($crud->checkUsernameExistsForOtherUsers($username, $user_id)) {
                    $usernameError = "The username that you input is already taken";
                } else {
                    // Only set group members if account type is Group
                    if ($account_type === 'Group' && isset($_POST['group_members'])) {
                        $group_members = $_POST['group_members'];
                    }
                    
                    // Handle profile image upload
                    $profileImage = '';
                    if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
                        $imageTmpName = $_FILES['profile']['tmp_name'];
                        $imageName = uniqid() . '_' . basename($_FILES['profile']['name']);
                        $imagePath = "uploads/" . $imageName;
                        
                        // Create uploads directory if it doesn't exist
                        if (!file_exists('uploads')) {
                            mkdir('uploads', 0777, true);
                        }
                        
                        if (move_uploaded_file($imageTmpName, $imagePath)) {
                            $profileImage = $imagePath;
                        } else {
                            // If upload fails, get the current profile image
                            $user = $crud->getAccountInfo($user_id);
                            $profileImage = $user['profile'];
                        }
                    } else {
                        // If no new image uploaded, get the current profile image
                        $user = $crud->getAccountInfo($user_id);
                        $profileImage = $user['profile'];
                    }

                    if ($crud->updateUserAccount($user_id, $username, $email, $role, $group_members, $account_type) && 
                        $crud->updateAccountInfo($user_id, $username, $email, $profileImage)) {
                        echo "<script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'User updated successfully',
                                showConfirmButton: false,
                                timer: 1500,
                                customClass: {
                                    popup: 'animated fadeInDown'
                                },
                                background: '#fff',
                                iconColor: '#4CAF50',
                                timerProgressBar: true
                            }).then(() => {
                                closeModal('updateModal');
                                // Update the table row
                                const row = document.querySelector(`tr[data-user-id='${$user_id}']`);
                                if (row) {
                                    row.cells[1].textContent = '$username';
                                    row.cells[2].textContent = '$email';
                                    row.cells[3].textContent = '$role';
                                    row.cells[4].textContent = '$account_type';
                                    row.cells[5].textContent = " . (!empty($group_members) ? "'$group_members'" : "'N/A'") . ";
                                }
                            });
                        </script>";
                    } else {
                        echo "<script>
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Error updating user',
                                confirmButtonColor: '#4CAF50',
                                customClass: {
                                    popup: 'animated fadeInDown'
                                },
                                background: '#fff',
                                iconColor: '#dc3545'
                            });
                        </script>";
                    }
                }
                break;

            case 'delete':
                $user_id = $_POST['user_id'];
                if ($crud->deactivateUserAccount($user_id)) {
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'User deactivated successfully',
                            showConfirmButton: false,
                            timer: 1500,
                            customClass: {
                                popup: 'animated fadeInDown'
                            },
                            background: '#fff',
                            iconColor: '#4CAF50',
                            timerProgressBar: true
                        }).then(() => {
                            closeModal('deleteModal');
                            // Remove the row from the table
                            const row = document.querySelector(`tr[data-user-id='${$user_id}']`);
                            if (row) {
                                row.remove();
                            }
                        });
                    </script>";
                } else {
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Error deactivating user',
                            confirmButtonColor: '#4CAF50',
                            customClass: {
                                popup: 'animated fadeInDown'
                            },
                            background: '#fff',
                            iconColor: '#dc3545'
                        });
                    </script>";
                }
                break;
        }
    }
}

function displayUsers() {
    global $crud;
    
    // Get all active users
    $users = $crud->getAllActiveUsers();

    echo "<table class='user-table' id='userTable'>
            <tr>
                <th>Profile</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Account Type</th>
                <th>Group Members</th>
            </tr>";

    if (!empty($users)) {
        foreach($users as $row) {
            $profilePath = (!empty($row['profile']) && file_exists($row['profile'])) 
                ? $row['profile'] 
                : 'images/PROFILE-DEFAULT.png';

            $profileImg = "<img class='profile-img' src='$profilePath' alt='Profile'>";
            $username = htmlspecialchars($row['username']);
            $email = htmlspecialchars($row['email']);
            $role = htmlspecialchars($row['role']);
            $account_type = htmlspecialchars($row['account_type']);
            $group_members = !empty($row['group_members']) ? htmlspecialchars($row['group_members']) : 'N/A';

            echo "<tr data-user-id='{$row['user_id']}' data-username='$username'>
                    <td>$profileImg</td>
                    <td>$username</td>
                    <td>$email</td>
                    <td>$role</td>
                    <td>$account_type</td>
                    <td>$group_members</td>
                  </tr>";
        }
    } else {
        echo "<tr>
                <td colspan='6' class='no-users' style='text-align: center; padding: 20px; color: #888;'>
                    No active users found.
                </td>
              </tr>";
    }

    echo "</table>";
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
        <ul><li><a href="Dashboard.php"> 📊Dashboard</a></li></ul>
        <h3>Manage Account</h3> 
        <ul>
            <li><a href="Admin_Page.php">👤 Admin Account</a></li>
            <li><a class="active" href="Manage.php">👥 Manage Account</a></li>
        </ul>
        <h3>View & Approval</h3>
             <ul>
             <li><a href="Tree_submission.php">🌳 Tree submission</a></li>
             <li><a href="Total_trees.php">🌲 Total trees planted</a></li>
 </ul>
        <h3>Feedback</h3>
        <ul><li><a href="Feedback.php">💬 View Feedbacks</a></li></ul>
        <div class="sidebar1">
            <ul><li><a class="logout" href="Homepage.php">Logout</a></li></ul>
        </div>
    </div>

    <div class="form-wrapper">
        <div class="form-header">Volunteer Accounts</div>
        <div class="search-container">
            <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by username or email...">
        </div>
        <div class="form-container scrollable">
        <?php displayUsers(); ?>
            <div class="no-results">
                <img src="not-found.png" alt="No Results">
                <h3>No Users Found</h3>
                <p>Try adjusting your search or filter to find what you're looking for.</p>
            </div>
           
        </div>
    </div>
</div>

<!-- Context Menu -->
<ul id="contextMenu" class="custom-context-menu">
    <li onclick="viewUser()">View</li>
    <li onclick="updateUser()">Update</li>
    <li onclick="deleteUser()">Delete</li>
</ul>

<!-- View Modal -->
<div class="modal" id="viewModal">
    <div class="modal-content-1">
        <span class="close" onclick="closeModal('viewModal')">&times;</span>
        <h2>View User</h2>
        <div class="user-details">
            <div class="id-card">
                <div class="id-label">User ID</div>
                <div class="id-number" id="viewId"></div>
            </div>
            <img id="viewProfile" src="" alt="Profile" class="modal-profile">
            <div class="user-info">
                <p><strong>Username:</strong> <span id="viewUsername"></span></p>
                <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                <p><strong>Role:</strong> <span id="viewRole"></span></p>
                <p><strong>Account Type:</strong> <span id="viewType"></span></p>
                <p><strong>Group Members:</strong> <span id="viewGroup"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal" id="updateModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('updateModal')">&times;</span>
        <h2>Update User</h2>
        <form id="updateForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="user_id" id="updateUserId">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" id="updateUsername" required>
                <?php if (isset($usernameError)): ?>
                    <div class="error-message"><?= $usernameError ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" id="updateEmail" required>
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role" id="updateRole" required>
                    <option value="Volunteer">Volunteer</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Account Type:</label>
                <select name="account_type" id="updateAccountType" required onchange="toggleGroupMembers()">
                    <option value="Individual">Individual</option>
                    <option value="Group Account">Group Account</option>
                </select>
            </div>
            <div class="form-group" id="groupMembersField" style="display: none;">
                <label>Group Members:</label>
                <input type="text" name="group_members" id="updateGroupMembers" placeholder="Enter group members (comma separated)">
            </div>
            <div class="form-group">
                <label>Profile Image:</label>
                <input type="file" name="profile" id="updateProfile" accept="image/*">
            </div>
            <button type="submit" class="save-btn">Update</button>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('deleteModal')">&times;</span>
        <div class="modal-icon">⚠️</div>
        <h2>Deactivate User</h2>
        <p>Are you sure you want to deactivate the account for <br><strong id="deleteUsername"></strong>?</p>
        <p style="font-size: 14px; color: #666;">This action cannot be undone.</p>
        <form id="deleteForm" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" id="deleteUserId">
            <div class="button-group">
                <button type="button" class="cancel" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="danger">Deactivate</button>
            </div>
        </form>
    </div>
</div>


<script>
let selectedUser = null;

document.addEventListener("DOMContentLoaded", () => {
    const table = document.getElementById("userTable");
    const contextMenu = document.getElementById("contextMenu");
    const noResults = document.querySelector('.no-results');

    table.addEventListener("contextmenu", (e) => {
        if (e.target.closest("tr") && !e.target.closest("tr").querySelector("th")) {
            e.preventDefault();
            selectedUser = e.target.closest("tr");
            const rect = selectedUser.getBoundingClientRect();
            contextMenu.style.display = "block";
            contextMenu.style.left = e.pageX + "px";
            contextMenu.style.top = e.pageY + "px";
        }
    });

    document.addEventListener("click", () => {
        contextMenu.style.display = "none";
    });
});

function viewUser() {
    if (!selectedUser) return;
    
    const userId = selectedUser.dataset.userId;
    const username = selectedUser.dataset.username;
    const email = selectedUser.cells[2].textContent;
    const role = selectedUser.cells[3].textContent;
    const accountType = selectedUser.cells[4].textContent;
    const groupMembers = selectedUser.cells[5].textContent;
    const profileImg = selectedUser.cells[0].querySelector('img').src;

    document.getElementById("viewProfile").src = profileImg;
    document.getElementById("viewId").textContent = userId;
    document.getElementById("viewUsername").textContent = username;
    document.getElementById("viewEmail").textContent = email;
    document.getElementById("viewRole").textContent = role;
    document.getElementById("viewType").textContent = accountType;
    document.getElementById("viewGroup").textContent = groupMembers;

    const modal = document.getElementById("viewModal");
    modal.style.display = "block";
    modal.classList.add("show");
}

function toggleGroupMembers() {
    const accountType = document.getElementById('updateAccountType').value;
    const groupMembersField = document.getElementById('groupMembersField');
    const groupMembersInput = document.getElementById('updateGroupMembers');
    
    if (accountType === 'Group Account') {
        groupMembersField.style.display = 'block';
    } else {
        groupMembersField.style.display = 'none';
        groupMembersInput.value = ''; // Clear the value when switching to Individual
    }
}

function updateUser() {
    if (!selectedUser) return;
    
    const userId = selectedUser.dataset.userId;
    const username = selectedUser.dataset.username;
    const email = selectedUser.cells[2].textContent;
    const role = selectedUser.cells[3].textContent;
    const accountType = selectedUser.cells[4].textContent;
    const groupMembers = selectedUser.cells[5].textContent;

    document.getElementById("updateUserId").value = userId;
    document.getElementById("updateUsername").value = username;
    document.getElementById("updateEmail").value = email;
    document.getElementById("updateRole").value = role;
    document.getElementById("updateAccountType").value = accountType;
    
    const groupMembersField = document.getElementById("groupMembersField");
    const groupMembersInput = document.getElementById("updateGroupMembers");
    if (accountType === 'Group') {
        groupMembersField.style.display = 'block';
        groupMembersInput.value = groupMembers !== 'N/A' ? groupMembers : '';
    } else {
        groupMembersField.style.display = 'none';
        groupMembersInput.value = '';
    }

    const modal = document.getElementById("updateModal");
    modal.style.display = "block";
    modal.classList.add("show");
}

function deleteUser() {
    if (!selectedUser) return;
    
    const userId = selectedUser.dataset.userId;
    const username = selectedUser.dataset.username;

    document.getElementById("deleteUserId").value = userId;
    document.getElementById("deleteUsername").textContent = username;

    const modal = document.getElementById("deleteModal");
    modal.style.display = "block";
    modal.classList.add("show");
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "none";
    modal.classList.remove("show");
}
</script>

</body>
</html>

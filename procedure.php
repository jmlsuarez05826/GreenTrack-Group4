<?php
require_once 'database.php';

class Crud {
    private $conn;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Tree Planting Operations
    public function editSubmissions($id, $tree_type, $number, $location, $image_path) {
        $stmt = $this->conn->prepare("CALL EditSubmissions(?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $id, $tree_type, $number, $location, $image_path);
        return $stmt->execute();
    }

    public function viewSubmissions($start, $limit, $year, $month, $user_id) {
        $stmt = $this->conn->prepare("CALL ViewSubmissions(?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiii", $start, $limit, $year, $month, $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countSubmissions() {
        $stmt = $this->conn->prepare("CALL CountSubmissions()");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    // Message Operations
    public function insertMessage($name, $email, $message_type, $message) {
        $stmt = $this->conn->prepare("CALL InsertMessage(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $message_type, $message);
        return $stmt->execute();
    }

    public function getAllMessages($type = 'all') {
        $stmt = $this->conn->prepare("CALL GetAllMessages(?)");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // User Account Operations
    public function getAccountInfo($user_id) {
        $stmt = $this->conn->prepare("CALL GetAccountInfo(?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateAccountInfo($user_id, $username, $email, $profile) {
        $stmt = $this->conn->prepare("CALL UpdateAccountInfo(?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $username, $email, $profile);
        return $stmt->execute();
    }

    public function deleteUserAccount($user_id) {
        $stmt = $this->conn->prepare("CALL DeleteUserAccount(?)");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function getUsername($username) {
        $stmt = $this->conn->prepare("CALL GetUsername(?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function insertRegisteredAccount($username, $email, $password, $account_type, $group_members, $profile) {
        $stmt = $this->conn->prepare("CALL insertRegisteredAccount(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $password, $account_type, $group_members, $profile);
        return $stmt->execute();
    }

    // Tree Statistics Operations
    public function getTotal() {
        $stmt = $this->conn->prepare("CALL Total()");
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getLeaderboard() {
        $stmt = $this->conn->prepare("CALL GetLeaderboard()");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTreePlantingLocations($user_id) {
        $stmt = $this->conn->prepare("CALL GetTreePlantingLocations(?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllTreePlantingLocations() {
        $stmt = $this->conn->prepare("CALL GetAllTreePlantingLocations()");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTotalStats() {
        $stmt = $this->conn->prepare("CALL Total()");
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Initialize totals
        $totals = [
            'TotTree' => 0,
            'TotCo2' => 0
        ];
        
        // Sum up all the results
        while ($row = $result->fetch_assoc()) {
            $totals['TotTree'] += $row['TotTree'];
            $totals['TotCo2'] += $row['TotCo2'];
        }
        
        return $totals;
    }

    // Additional Operations
    public function getUserById($user_id) {
        $stmt = $this->conn->prepare("CALL GetUserById(?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllActiveUsers() {
        $stmt = $this->conn->prepare("CALL GetAccountInfo(NULL)");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTreeSubmissionById($id) {
        $stmt = $this->conn->prepare("CALL GetTreeSubmissionById(?)");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllTreeSubmissions() {
        $stmt = $this->conn->prepare("CALL GetAllTreeSubmissions()");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPendingTreeSubmissions() {
        $stmt = $this->conn->prepare("CALL GetPendingTreeSubmissions()");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function approveTreeSubmission($id) {
        $stmt = $this->conn->prepare("CALL ApproveTreeSubmission(?)");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function rejectTreeSubmission($id) {
        $stmt = $this->conn->prepare("CALL RejectTreeSubmission(?)");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Search and Pagination
    public function searchTreeSubmissions($search, $limit, $offset) {
        $stmt = $this->conn->prepare("CALL SearchTreeSubmissions(?, ?, ?)");
        $stmt->bind_param("sii", $search, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countTreeSubmissions($search) {
        $stmt = $this->conn->prepare("CALL CountTreeSubmissions(?)");
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['TotalSubmissions'];
    }

    public function deactivateUserAccount($user_id) {
        $stmt = $this->conn->prepare("CALL DeactivateUserAccount(?)");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }

    public function updateSubmissionStatus($id, $status) {
        try {
            $stmt = $this->conn->prepare("CALL SubmissionStatus(?, ?)");
            $stmt->bind_param("is", $id, $status);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['affected_rows'] > 0;
        } catch(Exception $e) {
            error_log("Error updating submission status: " . $e->getMessage());
            return false;
        }
    }

    // Add the checkEmailExists method
    public function checkEmailExists($email) {
        $stmt = $this->conn->prepare("CALL CheckEmailExists(?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['exists'] > 0;
    }

    // Add new method for tree planting submission
    public function submitTreePlanting($tree_type, $number, $date_planted, $location, $image_path, $user_id, $other_location) {
        try {
            // Call the stored procedure
            $stmt = $this->conn->prepare("CALL tree_planting_data(?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed for tree planting data: " . $this->conn->error);
            }

            $stmt->bind_param("sisssis", 
                $tree_type,
                $number,
                $date_planted,
                $location,
                $image_path,
                $user_id,
                $other_location
            );

            if (!$stmt->execute()) {
                throw new Exception("Execute failed for tree planting data: " . $stmt->error);
            }

            $stmt->close();
            $this->conn->next_result();
            
            return true;

        } catch (Exception $e) {
            error_log("Error in submitTreePlanting: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateUserAccount($user_id, $username, $email, $role, $group_members, $account_type) {
        try {
            // Prepare the stored procedure call
            $stmt = $this->conn->prepare("CALL UpdateUserAccount(?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            // Bind parameters in the correct order
            $stmt->bind_param("isssss", 
                $user_id,
                $username,
                $email,
                $role,
                $group_members,  // This will be the comma-separated list of group members
                $account_type
            );
            
            // Execute the stored procedure
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();
            return true;
        } catch (Exception $e) {
            error_log("Error in updateUserAccount: " . $e->getMessage());
            return false;
        }
    }

    public function checkUsernameExists($username) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM username_check_view WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] > 0;
    }

    public function checkUsernameExistsForOtherUsers($username, $current_user_id) {
        $stmt = $this->conn->prepare("CALL CheckUsernameExistsForOtherUsers(?, ?, @exists)");
        $stmt->bind_param("si", $username, $current_user_id);
        $stmt->execute();
        
        $result = $this->conn->query("SELECT @exists as exists_flag");
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['exists_flag'] == 1;
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Create a global instance
$crud = new Crud();

// Add error handling for database connection
if (!$crud->getConnection()) {
    die("Database connection failed: " . $crud->db->getConnection()->connect_error);
}

?> 
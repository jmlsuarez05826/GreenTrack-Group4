-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2025 at 05:27 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `greentrack`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CancelSubmission` (IN `p_id` INT)   BEGIN
    UPDATE tree_plantings
    SET status = 'Cancelled'
    WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckEmailExists` (IN `p_email` VARCHAR(255))   BEGIN
    SELECT EXISTS (
        SELECT 1 FROM registered_accounts WHERE email = p_email
    ) AS `exists`;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckUsernameExistsForOtherUsers` (IN `p_username` VARCHAR(255), IN `p_current_user_id` INT, OUT `p_exists` BOOLEAN)   BEGIN
    SELECT COUNT(*) > 0 INTO p_exists
    FROM registered_accounts
    WHERE username = p_username 
    AND user_id != p_current_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CountSubmissions` ()   BEGIN
    SELECT COUNT(*) AS total
    FROM tree_plantings tp
    JOIN registered_accounts ra ON tp.user_id = ra.user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeactivateUserAccount` (IN `p_user_id` INT)   BEGIN
    UPDATE registered_accounts
    SET 
        status = 'Deactivated',
        updated_at = CURRENT_TIMESTAMP
    WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeletePasswordResetToken` (IN `p_token` VARCHAR(64))   BEGIN
    DELETE FROM password_resets
    WHERE token = p_token;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `EditSubmissions` (IN `p_id` INT, IN `p_tree_type` VARCHAR(50), IN `p_number` INT, IN `p_location` VARCHAR(100), IN `p_image_path` VARCHAR(255))   BEGIN
    DECLARE co2_rate DECIMAL(10,2);
    DECLARE tree_type_id INT;

    -- Error handler for SQL exceptions
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error in tree planting data insertion';
    END;

    -- Handle case where tree type is not found
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET tree_type_id = NULL;

    -- Start transaction
    START TRANSACTION;

    -- Try to get existing tree type ID
    SELECT tree_type_id INTO tree_type_id
    FROM tree_types
    WHERE name = p_tree_type
    LIMIT 1;

    -- Insert tree type if it does not exist
    IF tree_type_id IS NULL THEN
        INSERT INTO tree_types (name) VALUES (p_tree_type);
        SET tree_type_id = LAST_INSERT_ID();
    END IF;

    -- Set CO2 rate based on tree type
    CASE p_tree_type
        WHEN 'Narra' THEN SET co2_rate = 21.8;
        WHEN 'Mahogany' THEN SET co2_rate = 28.0;
        WHEN 'Molave' THEN SET co2_rate = 25.0;
        WHEN 'Acacia' THEN SET co2_rate = 35.0;
        WHEN 'Yakal' THEN SET co2_rate = 20.0;
        WHEN 'Ipil-ipil' THEN SET co2_rate = 15.0;
        WHEN 'Bamboo' THEN SET co2_rate = 62.0;
        WHEN 'Banaba' THEN SET co2_rate = 22.5;
        WHEN 'Talisay' THEN SET co2_rate = 18.0;
        WHEN 'Balete' THEN SET co2_rate = 30.0;
        ELSE SET co2_rate = 0;
    END CASE;

    -- Update tree planting data
    UPDATE tree_plantings 
    SET 
        tree_type_id = tree_type_id,
        number = p_number,
        updated_at = NOW(),
        location = p_location,
        co2_per_tree = co2_rate,
        total_co2 = (p_number * co2_rate),
        image_path = IF(p_image_path = '', image_path, p_image_path),
        status = 'Pending'
    WHERE id = p_id;

    -- Commit the transaction
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAccountInfo` (IN `p_user_id` INT)   BEGIN
    IF p_user_id IS NULL THEN
        SELECT 
            ra.user_id,
            ra.username,
            ra.password,
            ra.role,
            ra.status,
            ra.account_type,
            ra.email,
            ra.profile,
            GROUP_CONCAT(gm.member_name) AS group_members
        FROM registered_accounts ra
        LEFT JOIN group_members gm ON ra.user_id = gm.user_id
        WHERE ra.status = 'Active'
        GROUP BY ra.user_id, ra.username, ra.password, ra.role, ra.status, ra.account_type, ra.email, ra.profile;
    ELSE
        SELECT 
            ra.user_id,
            ra.username,
            ra.password,
            ra.role,
            ra.status,
            ra.account_type,
            ra.email,
            ra.profile,
            GROUP_CONCAT(gm.member_name) AS group_members
        FROM registered_accounts ra
        LEFT JOIN group_members gm ON ra.user_id = gm.user_id
        WHERE ra.user_id = p_user_id AND ra.status = 'Active'
        GROUP BY ra.user_id, ra.username, ra.password, ra.role, ra.status, ra.account_type, ra.email, ra.profile;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllMessages` (IN `p_type` VARCHAR(50))   BEGIN
    SELECT 
        m.id,
        m.name,
        m.email,
        m.message,
        m.message_type,
        m.date_sent
    FROM contact_messages m
    WHERE 
        p_type = 'all' OR m.message_type = p_type
    ORDER BY m.date_sent DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllTreePlantingLocations` ()   BEGIN
    SELECT 
        CASE 
            WHEN tp.location = 'OTHER' THEN tp.other_location
            ELSE tp.location
        END AS location,
        SUM(tp.number) AS total_trees_planted,
        SUM(tp.total_co2) as total_Co2
    FROM 
        tree_plantings tp
    WHERE 
        tp.status = 'Approved'
    GROUP BY 
        CASE 
            WHEN tp.location = 'OTHER' THEN tp.other_location
            ELSE tp.location
        END;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetLeaderboard` ()   BEGIN
    SELECT 
        ra.username,
        ra.email,
        COUNT(tp.id) AS total_submissions,
        SUM(tp.number) AS total_trees,
        SUM(tp.total_co2) AS total_co2
    FROM registered_accounts ra
    JOIN tree_plantings tp 
        ON ra.user_id = tp.user_id
    WHERE tp.status = 'Approved'
    GROUP BY ra.user_id, ra.username, ra.email
    ORDER BY total_co2 DESC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTreePlantingLocations` (IN `p_user_id` INT)   BEGIN
    SELECT 
        CASE 
            WHEN tp.location = 'OTHER' THEN tp.other_location
            ELSE tp.location
        END AS location,
        tp.number,
        tp.status,
        tp.total_co2
    FROM tree_plantings tp
    WHERE tp.status = 'Approved'
      AND tp.user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUsername` (IN `p_username` VARCHAR(50))   BEGIN
    SELECT 
        user_id,
        username,
        password,
        role
    FROM registered_accounts
    WHERE username = p_username
      AND status = 'Active';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertLoginLog` (IN `p_user_id` INT, IN `p_username` VARCHAR(255), IN `p_login_status` VARCHAR(10))   BEGIN
    INSERT INTO user_logs (user_id, username, login_status)
    VALUES (p_user_id, p_username, p_login_status);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertMessage` (IN `p_name` VARCHAR(100), IN `p_email` VARCHAR(100), IN `p_message_type` VARCHAR(50), IN `p_message` TEXT)   BEGIN
    INSERT INTO contact_messages (name, email, message_type, message, date_sent)
    VALUES (p_name, p_email, p_message_type, p_message, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `insertRegisteredAccount` (IN `p_username` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_account_type` VARCHAR(50), IN `p_group_members` TEXT, IN `p_profile` VARCHAR(255))   BEGIN
    DECLARE v_user_id INT;
    
       IF EXISTS (
        SELECT 1 FROM registered_accounts 
        WHERE username = p_username
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Username already taken.';
    END IF;
    
    -- Insert into registered_accounts
    INSERT INTO registered_accounts(
        username,
        email,
        password,
        account_type,
        profile,
        status
    ) VALUES (
        p_username,
        p_email,
        p_password,
        p_account_type,
        p_profile,
        'Active'
    );
    
    -- Get the last inserted user_id
    SET v_user_id = LAST_INSERT_ID();
    
    -- If it's a group account, insert group members
    IF p_account_type = 'Group Account' AND p_group_members IS NOT NULL THEN
        INSERT INTO group_members (user_id, member_name)
        VALUES (v_user_id, p_group_members);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `StorePasswordResetToken` (IN `p_email` VARCHAR(255), IN `p_token` VARCHAR(64), IN `p_expiry` DATETIME)   BEGIN
    INSERT INTO password_resets (email, token, expiry)
    VALUES (p_email, p_token, p_expiry);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SubmissionStatus` (IN `p_id` INT, IN `p_status` VARCHAR(20))   BEGIN
    UPDATE tree_plantings 
    SET 
        status = p_status,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_id;
    
    SELECT ROW_COUNT() AS affected_rows;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Total` ()   BEGIN
    SELECT 
        SUM(number) AS TotTree, 
        SUM(total_co2) AS TotCo2
    FROM tree_plantings
    WHERE status = 'Approved';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tree_planting_data` (IN `p_tree_type` VARCHAR(50), IN `p_number` INT, IN `p_date_planted` DATE, IN `p_location` VARCHAR(100), IN `p_image_path` VARCHAR(255), IN `p_user_id` INT, IN `p_other_location` VARCHAR(150))   BEGIN
    DECLARE co2_rate DECIMAL(10,2);
    DECLARE tree_type_id INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error in tree planting data insertion';
    END;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET tree_type_id = NULL;

    START TRANSACTION;

    -- Try to get existing tree type ID
 SELECT tree_type_id INTO tree_type_id
FROM tree_types
WHERE name = p_tree_type
LIMIT 1;


    -- Insert if not exists
    IF tree_type_id IS NULL THEN
        INSERT INTO tree_types (name) VALUES (p_tree_type);
        SET tree_type_id = LAST_INSERT_ID();
    END IF;

    -- Set CO2 rate
    CASE p_tree_type
        WHEN 'Narra' THEN SET co2_rate = 21.8;
        WHEN 'Mahogany' THEN SET co2_rate = 28.0;
        WHEN 'Molave' THEN SET co2_rate = 25.0;
        WHEN 'Acacia' THEN SET co2_rate = 35.0;
        WHEN 'Yakal' THEN SET co2_rate = 20.0;
        WHEN 'Ipil-ipil' THEN SET co2_rate = 15.0;
        WHEN 'Bamboo' THEN SET co2_rate = 62.0;
        WHEN 'Banaba' THEN SET co2_rate = 22.5;
        WHEN 'Talisay' THEN SET co2_rate = 18.0;
        WHEN 'Balete' THEN SET co2_rate = 30.0;
        ELSE SET co2_rate = 0;
    END CASE;

    -- Insert into tree_plantings
    INSERT INTO tree_plantings (
        tree_type_id,
        number,
        created_at,
        location,
        image_path,
        user_id,
        co2_per_tree,
        total_co2,
        other_location,
        status
    ) VALUES (
        tree_type_id,
        p_number,
        p_date_planted,
        p_location,
        p_image_path,
        p_user_id,
        co2_rate,
        (p_number * co2_rate),
        p_other_location,
        'Pending'
    );

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateAccountInfo` (IN `p_user_id` INT, IN `p_username` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_profile` VARCHAR(255))   BEGIN
    UPDATE registered_accounts
    SET 
        username = p_username,
        email = p_email,
        profile = p_profile
    WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateLogoutTime` (IN `p_user_id` INT)   BEGIN
    UPDATE user_logs 
    SET logout_time = CURRENT_TIMESTAMP 
    WHERE user_id = p_user_id 
      AND logout_time IS NULL 
    ORDER BY login_time DESC 
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserAccount` (IN `p_user_id` INT, IN `p_username` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_role` VARCHAR(20), IN `p_group_members` TEXT, IN `p_account_type` VARCHAR(20))   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error updating user account.';
    END;

    START TRANSACTION;

    -- Update the user account
    UPDATE registered_accounts
    SET 
        username = p_username,
        email = p_email,
        role = p_role,
        account_type = p_account_type,
        updated_at = NOW()
    WHERE user_id = p_user_id;

    -- Check if update was successful
    IF ROW_COUNT() = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No user found with the given ID';
    END IF;

    -- If account type is Group Account, update group members
    IF p_account_type = 'Group Account' THEN
        -- Delete existing group members
        DELETE FROM group_members WHERE user_id = p_user_id;

        -- Insert new group members from comma-separated string
        WHILE LOCATE(',', p_group_members) > 0 DO
            INSERT INTO group_members (user_id, member_name)
            VALUES (
                p_user_id,
                TRIM(SUBSTRING_INDEX(p_group_members, ',', 1))
            );

            SET p_group_members = SUBSTRING(p_group_members, LOCATE(',', p_group_members) + 1);
        END WHILE;

        -- Insert the last (or only) member
        IF LENGTH(TRIM(p_group_members)) > 0 THEN
            INSERT INTO group_members (user_id, member_name)
            VALUES (p_user_id, TRIM(p_group_members));
        END IF;
    END IF;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserPassword` (IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255))   BEGIN
    UPDATE registered_accounts
    SET password = p_password
    WHERE email = p_email;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerifyPasswordResetToken` (IN `p_token` VARCHAR(64), OUT `p_email` VARCHAR(255), OUT `p_valid` BOOLEAN)   BEGIN
    DECLARE v_email VARCHAR(255);
    DECLARE v_expiry DATETIME;
    
    SELECT email, expiry INTO v_email, v_expiry
    FROM password_resets
    WHERE token = p_token
    AND expiry > NOW();
    
    IF v_email IS NOT NULL THEN
        SET p_email = v_email;
        SET p_valid = TRUE;
    ELSE
        SET p_email = NULL;
        SET p_valid = FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ViewSubmissions` (IN `p_start` INT, IN `p_limit` INT, IN `p_year` INT, IN `p_month` INT, IN `p_user_id` INT)   BEGIN
    IF p_user_id IS NULL THEN
        -- Admin view: Show only Pending submissions
        SELECT 
            tp.id,
            ra.username,
            ra.email,
            GROUP_CONCAT(gm.member_name) as group_members,
            tt.name AS tree_type,
            tp.number,
            tp.created_at,
            tp.updated_at,
            CASE 
                WHEN tp.location = 'OTHER' THEN tp.other_location
                ELSE tp.location
            END AS location,
            tp.total_co2 AS total_Co2,
            tp.status,
            tp.image_path
        FROM tree_plantings tp
        JOIN registered_accounts ra ON tp.user_id = ra.user_id
        LEFT JOIN tree_types tt ON tp.tree_type_id = tt.tree_type_id
        LEFT JOIN group_members gm ON ra.user_id = gm.user_id
        WHERE 
            (p_year IS NULL OR YEAR(tp.created_at) = p_year)
            AND (p_month IS NULL OR MONTH(tp.created_at) = p_month)
            AND tp.status = 'Pending'  -- Only show Pending submissions for admin
        GROUP BY tp.id, ra.username, ra.email, tt.name, tp.number, tp.created_at, 
                 tp.updated_at, tp.location, tp.other_location, tp.total_co2, 
                 tp.status, tp.image_path
        ORDER BY tp.created_at DESC
        LIMIT p_start, p_limit;
    ELSE
        -- User view: Show both Pending and Approved submissions
        SELECT 
            tp.id,
            ra.username,
            ra.email,
            GROUP_CONCAT(gm.member_name) as group_members,
            tt.name AS tree_type,
            tp.number,
            tp.created_at,
            tp.updated_at,
            CASE 
                WHEN tp.location = 'OTHER' THEN tp.other_location
                ELSE tp.location
            END AS location,
            tp.total_co2 AS total_Co2,
            tp.status,
            tp.image_path
        FROM tree_plantings tp
        JOIN registered_accounts ra ON tp.user_id = ra.user_id
        LEFT JOIN tree_types tt ON tp.tree_type_id = tt.tree_type_id
        LEFT JOIN group_members gm ON ra.user_id = gm.user_id
        WHERE 
            (p_year IS NULL OR YEAR(tp.created_at) = p_year)
            AND (p_month IS NULL OR MONTH(tp.created_at) = p_month)
            AND tp.user_id = p_user_id
            AND tp.status IN ('Pending', 'Approved')  -- Show both Pending and Approved for users
        GROUP BY tp.id, ra.username, ra.email, tt.name, tp.number, tp.created_at, 
                 tp.updated_at, tp.location, tp.other_location, tp.total_co2, 
                 tp.status, tp.image_path
        ORDER BY tp.created_at DESC
        LIMIT p_start, p_limit;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `message_type`, `message`, `date_sent`) VALUES
(1, 'MARK', 'suarezjohnmark65@gmail.com', 'Feedback', 'arigato', '2025-05-06 22:40:58');

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `member_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`id`, `user_id`, `member_name`) VALUES
(1, 17, 'Monater Mau, Daniel Padilla, Robin Padilla, Alden Richards, Super Man, Wowowowow'),
(4, 23, '\r\nJak Robert, Jonathan, Samantha');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expiry`, `created_at`) VALUES
(1, 'suarezjohnmark65@gmail.com', 'b6d7cd432417a61c81b487219ffe03d9bb788e4df8e47b7041cb01e6ab69930d', '2025-05-11 04:30:56', '2025-05-11 01:30:56'),
(2, 'suarezjohnmark65@gmail.com', 'ce062e3ca3f900aaecb7ad6c25e4baca3aa2e830693fe01d1f411f30bb452e68', '2025-05-11 04:33:02', '2025-05-11 01:33:02'),
(3, 'suarezjohnmark65@gmail.com', 'a10d97119f91c1700e823bf3573e2d8ef52af4691a622136faeb58dc165a9f05', '2025-05-11 04:34:57', '2025-05-11 01:34:57'),
(4, 'suarezjohnmark65@gmail.com', '0264369cb1b81821fe2417b1ef2584451122ba92127fd8be9490f2b0f5e7db65', '2025-05-11 04:36:44', '2025-05-11 01:36:44'),
(5, 'suarezjohnmark65@gmail.com', '9d5209a2bf2ae5bd941de16067e12206041036072c6a3d8fc81d70e4d99a717c', '2025-05-11 04:37:36', '2025-05-11 01:37:36'),
(6, 'suarezjohnmark65@gmail.com', 'fb935f9e2448fe75a5e4d9715a918d934dd057222385019e56f1b7eca5248c71', '2025-05-11 04:39:57', '2025-05-11 01:39:57'),
(7, 'suarezjohnmark65@gmail.com', 'b1f9b6ab8a4c60fbc73d669e90ab5cee96f996801b9844c7f4bfd3d81270ec0e', '2025-05-11 04:40:38', '2025-05-11 01:40:38'),
(8, 'suarezjohnmark65@gmail.com', '8843602bd377e1cf39781381ead509341008e61aebe3d3f070a59d5f2cb020ac', '2025-05-11 04:42:33', '2025-05-11 01:42:33'),
(9, 'suarezjohnmark65@gmail.com', '9b97f1a73bdfeadab7eb4df4233b882e0dda9e0cd82da92888cbc4641c01eadb', '2025-05-11 04:49:32', '2025-05-11 01:49:32'),
(10, 'suarezjohnmark65@gmail.com', '1559372de6c586645c1b19300218dda69a83fb99ebf24bd62c66c2339d125b8b', '2025-05-11 04:52:58', '2025-05-11 01:52:58'),
(11, 'suarezjohnmark65@gmail.com', '77a1fbe602f7857e3d9d0f3182f9134655a0b9797bf378999e4f7f7aced902d4', '2025-05-11 04:57:40', '2025-05-11 01:57:40'),
(12, 'suarezjohnmark65@gmail.com', '8625688c1e7ba21c318f87bf3edb1b62484ec577508f86528a114b8fb77eef43', '2025-05-11 04:57:50', '2025-05-11 01:57:50');

-- --------------------------------------------------------

--
-- Table structure for table `registered_accounts`
--

CREATE TABLE `registered_accounts` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(200) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'Volunteer',
  `account_type` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registered_accounts`
--

INSERT INTO `registered_accounts` (`user_id`, `username`, `password`, `role`, `account_type`, `email`, `profile`, `status`, `updated_at`) VALUES
(9, 'SuperMan', '$2y$10$yd5UYc/.xp5g7FBdpRrApuFwVf/3v4Y2CiiFx45f4LWcngGWcnD/q', 'Volunteer', 'Individual', 'suarezjohnmark65@gmail.com', 'uploads/681a20d99976c_GEO.jpg', 'Active', '2025-05-11 02:44:28'),
(10, 'Admin1', '$2y$10$9IurpkHHDKoUdGwGvbfRqOoX7Z.klbRoC3n/pWZq5OXGfUECthGCa', 'Admin', '', 'admin@gmail.com', 'uploads/681b8ea057769_LOGO.jpg', 'Active', '2025-05-11 01:58:21'),
(11, 'Volunteer2', '$2y$10$AsTY4JUKgQZsJ.iGZAQ44O76rbahBXzY0WYw/hNSlDIHYGxNeVxi2', 'Volunteer', 'Individual', 'suarezjohnmark65@gmail.com', 'uploads/68149b9e51972_PROFILE.png', 'Active', '2025-05-10 23:15:39'),
(12, 'Volunteer1', '$2y$10$4LKbsTxUaPcYOi0plNf70OkEyfQONcJj/wSR4o00KFFJDMMDVpf0u', 'Volunteer', '', 'jasmine_suarez38@yahoo.com', 'uploads/68149bd878c2b_trees4.jpg', 'Active', '2025-05-10 23:30:27'),
(15, 'Ancil', '$2y$10$mnsRbL9J2/NWvDTf2Wvb8eTgB3rdXarrcjsDIB7uw7kEwfAHQ5Bna', 'Volunteer', 'individual', 'ancilmacalalad14@gmail.com', NULL, 'Deactivated', '2025-05-09 14:50:52'),
(16, 'John', '$2y$10$e9RLF9andlvHOykUd/IDLOTQ1s0ZKf4Thc1ahvCg1kGHV3O7Rzlcq', 'Admin', 'Individual', '23-01629@g.batstate-u.edu.ph', 'uploads/681d3397539ee_LOGO.png', 'Active', '2025-05-09 14:25:21'),
(17, 'Jenny', '$2y$10$mTXioGdRzqCIr2HBtky.ruMJzwuXkFH4EWLv8Wy2lBZlJLp.4jUj.', 'Volunteer', 'Group Account', 'jennyclairesuarez@gmail.com', 'uploads/681f4942db771_GEO.jpg', 'Active', '2025-05-10 12:40:34'),
(18, 'janedeleon', 'samplepass1', 'Volunteer', 'Group Account', 'jane.deleon@treeorg.org', 'uploads/681fc6c10d9e5_PROFILE.png', 'Active', '2025-05-10 21:36:44'),
(19, 'michaelchan', 'samplepass2', 'Admin', 'Individual', 'michael.chan@email.com', 'uploads/681fc697cd63e_PROFILE.png', 'Active', '2025-05-10 21:35:19'),
(20, 'ana.santos', 'samplepass3', 'Volunteer', 'Individual', 'ana.santos@email.com', 'uploads/681fc69f5be8f_PROFILE.png', 'Deactivated', '2025-05-11 02:44:37'),
(21, 'greenplanetorg', 'samplepass4', 'Admin', 'Individual', 'contact@greenplanet.org', 'uploads/681fc73e3a6ba_LOGO.png', 'Active', '2025-05-10 21:38:06'),
(22, 'ricoverano', 'samplepass5', 'Volunteer', 'Individual', 'rico.verano@email.com', 'uploads/681fc6b310b20_PROFILE.png', 'Active', '2025-05-10 21:35:47'),
(23, 'My Account', '$2y$10$92tP5I2VwvEfDu6qxtUboOC0ZtNov6jaBZ5ekXTLjqPVk9iX8Yu8u', 'Volunteer', 'Group Account', 'tlga.johnmarksuarez@gmail.com', 'PROFILE.png', 'Active', '2025-05-10 23:42:29');

-- --------------------------------------------------------

--
-- Table structure for table `tree_plantings`
--

CREATE TABLE `tree_plantings` (
  `id` int(11) NOT NULL,
  `number` int(11) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `co2_per_tree` double DEFAULT NULL,
  `total_co2` double DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `other_location` varchar(150) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `tree_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tree_plantings`
--

INSERT INTO `tree_plantings` (`id`, `number`, `created_at`, `location`, `image_path`, `co2_per_tree`, `total_co2`, `user_id`, `status`, `other_location`, `updated_at`, `tree_type_id`) VALUES
(21, 10, '2025-04-26', 'Lipa City', 'uploads/2.jpg', 21.8, 218, 9, 'Approved', NULL, '2025-05-09 22:31:58', 1),
(22, 7, '2025-04-25', 'Lipa City', 'uploads/2.jpg', 25, 175, 9, 'Disapproved', NULL, '2025-05-09 22:32:46', 2),
(23, 5, '2025-04-25', 'Lipa City', '', 21.8, 109, 9, 'Approved', NULL, '2025-05-09 06:03:13', 1),
(26, 3, '2025-04-26', 'Lipa City', 'uploads/backg2.jpg', 28, 84, 9, 'Approved', NULL, '2025-05-09 22:32:10', 3),
(28, 4, '2025-04-26', 'Lipa City', 'uploads/possible.jpg', 28, 112, 9, 'Disapproved', NULL, '2025-05-09 22:32:24', 3),
(29, 3, '2025-04-26', 'Lipa City', 'uploads/backg1.jpg', 20, 60, 9, 'Disapproved', NULL, '2025-05-09 22:32:26', 4),
(30, 3, '2025-04-26', 'Lipa City', 'uploads/backg1.jpg', 20, 60, 9, 'Approved', NULL, '2025-05-09 06:03:13', 4),
(31, 3, '2025-04-26', 'Lipa City', 'uploads/bg5.jpg', 22.5, 67.5, 9, 'Cancelled', NULL, '2025-05-09 06:03:13', 5),
(32, 3, '2025-04-26', 'Lipa City', 'uploads/bg5.jpg', 28, 84, 9, 'Disapproved', NULL, '2025-05-09 22:32:27', 3),
(33, 3, '2025-04-26', 'Lipa City', 'uploads/LOGO.png', 28, 84, 9, 'Disapproved', NULL, '2025-05-09 22:32:29', 3),
(34, 3, '2025-04-26', 'Lipa City', 'uploads/LOGO.png', 28, 84, 9, 'Approved', NULL, '2025-05-09 22:32:34', 3),
(35, 3, '2025-04-26', 'Lipa City', 'uploads/LOGO.png', 28, 84, 9, 'Approved', NULL, '2025-05-09 22:32:38', 3),
(36, 4, '2025-05-02', 'SAN JUAN', 'uploads/backg1.jpg', 21.8, 65.4, 9, 'Approved', NULL, '2025-05-09 06:03:13', 3),
(37, 3, '2025-05-07', 'LIPA', 'uploads/bg5.jpg', 28, 84, 12, 'Cancelled', NULL, '2025-05-09 06:03:13', 3),
(38, 32, '2025-05-07', 'BATANGAS CITY', 'uploads/backg2.jpg', 35, 1120, 12, 'Approved', NULL, '2025-05-09 06:03:13', 6),
(39, 19, '2025-05-07', 'TAAL', 'uploads/681baf461d8fe_bg5.jpg', 22.5, 427.5, 12, 'Approved', NULL, '2025-05-09 06:03:13', 5),
(40, 13, '2025-05-07', 'LIPA', 'uploads/681baf65650f1_123.jpg', 21.8, 283.4, 12, 'Disapproved', NULL, '2025-05-09 22:31:52', 1),
(41, 15, '2025-05-07', 'TAAL', 'uploads/681bb65b217a5_forestforest.jpg', 28, 420, 12, 'Approved', NULL, '2025-05-09 06:54:55', 3),
(42, 10, '2025-05-07', 'NASUGBU', 'uploads/681bb810f14e7_possible.jpg', 30, 300, 12, 'Approved', NULL, '2025-05-09 06:03:13', 7),
(43, 34, '2025-05-09', 'LIPA', 'uploads/681e2efd9ba81_2.jpg', 21.8, 741.2, 11, 'Approved', NULL, '2025-05-10 00:45:32', 8),
(44, 26, '2025-05-09', 'LIPA', 'uploads/681e31134e0a8_123.jpg', 21.8, 566.8, 11, 'Cancelled', NULL, NULL, 9),
(45, 39, '2025-05-09', 'LIPA', 'uploads/681e31e917676_2.jpg', 21.8, 850.2, 11, 'Cancelled', NULL, NULL, 10),
(46, 35, '2025-05-09', 'BATANGAS CITY', 'uploads/681e32057aa0f_2.jpg', 25, 875, 11, 'Approved', NULL, '2025-05-10 01:09:58', 11),
(47, 25, '2025-05-09', 'SAN JUAN', 'uploads/681e370c9c6c3_2.jpg', 22.5, 562.5, 11, 'Approved', NULL, '2025-05-10 01:11:31', 12),
(48, 19, '2025-05-10', 'LIPA', 'uploads/681f496f5ad2f_backg1.jpg', 21.8, 414.2, 17, 'Cancelled', NULL, NULL, 13),
(49, 46, '2025-05-10', 'TAAL', 'uploads/681faa55b6903_2.jpg', 28, 1288, 11, 'Approved', NULL, '2025-05-11 09:13:56', 17),
(50, 32, '2025-05-10', 'LIPA', 'uploads/681faa6669579_123.jpg', 35, 1120, 11, 'Approved', NULL, '2025-05-11 10:50:50', 15),
(51, 22, '2025-05-11', 'LIPA', 'uploads/681fdcc6bbbd2_Screenshot (3).png', 21.8, 479.6, 17, 'Approved', 'Manila', '2025-05-11 08:52:07', 19),
(52, 26, '2025-05-11', 'LIPA', 'uploads/681fe594c5c85_Screenshot (6).png', 28, 728, 23, 'Approved', NULL, '2025-05-11 08:52:11', 21),
(53, 27, '2025-05-11', 'SAN JUAN', 'uploads/681ffabd40f3a_Screenshot (3).png', 21.8, 588.6, 12, 'Cancelled', NULL, '2025-05-11 10:46:24', 25),
(54, 73, '2025-05-11', 'LIPA', 'uploads/68200f498f61c_Screenshot (3).png', 21.8, 1591.4, 12, 'Pending', NULL, NULL, 23),
(55, 36, '2025-05-11', 'LIPA', 'uploads/68200f7110e39_Screenshot (4).png', 28, 1008, 12, 'Pending', NULL, NULL, 24);

-- --------------------------------------------------------

--
-- Table structure for table `tree_types`
--

CREATE TABLE `tree_types` (
  `tree_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `avg_co2_reduction` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tree_types`
--

INSERT INTO `tree_types` (`tree_type_id`, `name`, `description`, `avg_co2_reduction`) VALUES
(1, 'Narra', NULL, NULL),
(2, 'Molave', NULL, NULL),
(3, 'Mahogany', NULL, NULL),
(4, 'Yakal', NULL, NULL),
(5, 'Banaba', NULL, NULL),
(6, 'Acacia', NULL, NULL),
(7, 'Balete', NULL, NULL),
(8, 'Narra', NULL, NULL),
(9, 'Narra', NULL, NULL),
(10, 'Narra', NULL, NULL),
(11, 'Molave', NULL, NULL),
(12, 'Banaba', NULL, NULL),
(13, 'Narra', NULL, NULL),
(14, 'Acacia', NULL, NULL),
(15, 'Acacia', NULL, NULL),
(16, 'Acacia', NULL, NULL),
(17, 'Mahogany', NULL, NULL),
(18, 'Balete', NULL, NULL),
(19, 'Narra', NULL, NULL),
(20, 'Mahogany', NULL, NULL),
(21, 'Mahogany', NULL, NULL),
(22, 'Mahogany', NULL, NULL),
(23, 'Narra', NULL, NULL),
(24, 'Mahogany', NULL, NULL),
(25, 'Narra', NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `username_check_view`
-- (See below for the actual view)
--
CREATE TABLE `username_check_view` (
`username` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `login_status` enum('Success','Failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`log_id`, `user_id`, `username`, `login_time`, `logout_time`, `login_status`) VALUES
(1, 10, 'Admin1', '2025-05-11 08:32:54', '2025-05-11 08:33:01', 'Success'),
(2, 10, 'Admin1', '2025-05-11 08:43:29', '2025-05-11 08:45:01', 'Success'),
(3, 12, 'Volunteer1', '2025-05-11 08:45:09', '2025-05-11 08:48:20', 'Success'),
(4, 10, 'Admin1', '2025-05-11 08:51:54', '2025-05-11 09:17:23', 'Success'),
(5, 12, 'Volunteer1', '2025-05-11 09:17:28', '2025-05-11 09:18:15', 'Success'),
(7, 10, 'Admin1', '2025-05-11 09:18:52', NULL, 'Success'),
(9, 10, 'Admin1', '2025-05-11 09:19:11', '2025-05-11 09:23:41', 'Success'),
(11, 10, 'Admin2', '2025-05-11 09:58:15', '2025-05-11 10:05:22', 'Success'),
(12, 10, 'Admin1', '2025-05-11 10:05:27', '2025-05-11 10:29:24', 'Success'),
(13, 10, 'Admin1', '2025-05-11 10:42:08', '2025-05-11 10:44:55', 'Success'),
(14, 12, 'Volunteer1', '2025-05-11 10:45:02', '2025-05-11 10:46:32', 'Success'),
(15, 10, 'Admin1', '2025-05-11 10:46:44', '2025-05-11 10:52:15', 'Success');

-- --------------------------------------------------------

--
-- Structure for view `username_check_view`
--
DROP TABLE IF EXISTS `username_check_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `username_check_view`  AS SELECT `registered_accounts`.`username` AS `username` FROM `registered_accounts` WHERE `registered_accounts`.`status` = 'Active' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token` (`token`);

--
-- Indexes for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- Indexes for table `tree_plantings`
--
ALTER TABLE `tree_plantings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_tree_type` (`tree_type_id`);

--
-- Indexes for table `tree_types`
--
ALTER TABLE `tree_types`
  ADD PRIMARY KEY (`tree_type_id`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `group_members`
--
ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `registered_accounts`
--
ALTER TABLE `registered_accounts`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tree_plantings`
--
ALTER TABLE `tree_plantings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tree_types`
--
ALTER TABLE `tree_types`
  MODIFY `tree_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `registered_accounts` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tree_plantings`
--
ALTER TABLE `tree_plantings`
  ADD CONSTRAINT `fk_tree_type` FOREIGN KEY (`tree_type_id`) REFERENCES `tree_types` (`tree_type_id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `registered_accounts` (`user_id`);

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `registered_accounts` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

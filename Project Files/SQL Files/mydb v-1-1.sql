-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 02:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `AID` int(11) NOT NULL,
  `address_line_1` varchar(45) NOT NULL,
  `address_line_2` varchar(45) NOT NULL,
  `city` varchar(45) DEFAULT NULL,
  `country` varchar(45) NOT NULL,
  `ZIP_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`AID`, `address_line_1`, `address_line_2`, `city`, `country`, `ZIP_code`) VALUES
(1, 'John Avenue', '#03-09', 'Singapore', 'Singapore', 123456);

-- --------------------------------------------------------

--
-- Table structure for table `colour`
--

CREATE TABLE `colour` (
  `CID` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `image` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `colour`
--

INSERT INTO `colour` (`CID`, `name`, `image`) VALUES
(1, 'Black', '/images/colours/black.png');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `IID` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `availability` tinyint(1) DEFAULT 1,
  `role_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`IID`, `name`, `price`, `description`, `availability`, `role_id`) VALUES
(2, 'Test PC 2', 100.42, 'PC from 2019 EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE', 1, 3),
(91, 'test PC 23', 803.21, 'testpc23', 1, 4),
(201, 'Test PC', 1023.24, 'This PC is so trash that even Om wouldn\'t use it as a bomb', 0, 4),
(893, 'Test PC 123', 1842.32, 'TestPC123', 1, 1),
(901, 'Test PC 345', 1024.94, 'Test PC 345', 0, 1),
(903, 'My own PC', 958.23, 'My personal PC', 0, 1),
(905, 'Test PC 12', 1028.32, 'Another Test PC, Their piling', 1, 1),
(906, 'Bomb PC', 1.00, 'Om\'s Bomb PC', 1, 1),
(907, 'Super Mega DDR5 abuser', 99999.00, 'powerholic', 1, 1),
(908, 'Mass Produca PC', 0.01, 'Whatever', 1, 3),
(909, 'test PC 99', 1560.23, 'High performance PC with powerful CPU', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `item_audit_logging`
--

CREATE TABLE `item_audit_logging` (
  `item_audit_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `action` varchar(45) NOT NULL,
  `actor_id` varchar(36) NOT NULL,
  `previous_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`previous_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`new_values`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_stat`
--

CREATE TABLE `order_stat` (
  `OSID` int(11) NOT NULL,
  `order_status` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `order_stat`
--

INSERT INTO `order_stat` (`OSID`, `order_status`) VALUES
(1, 'pending'),
(2, 'Manufacturing'),
(3, 'Shipping'),
(4, 'Completed'),
(5, 'Cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `order_table`
--

CREATE TABLE `order_table` (
  `OID` int(11) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `order_status_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `colour_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `item_qty` int(11) NOT NULL,
  `order_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_hash` varchar(45) NOT NULL,
  `order_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `order_table`
--

INSERT INTO `order_table` (`OID`, `user_id`, `order_status_id`, `item_id`, `colour_id`, `size_id`, `payment_id`, `address_id`, `item_qty`, `order_time`, `order_hash`, `order_price`) VALUES
(12, '1', 4, 2, 1, 2, 1, 1, 12, '2026-01-21 03:34:52', '182845', 104.00),
(15, '1', 5, 906, 1, 1, 1, 1, 1, '2026-01-21 03:36:13', '12498141', 109.34);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PID` int(11) NOT NULL,
  `token` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PID`, `token`) VALUES
(1, 123456);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `RID` int(11) NOT NULL,
  `RoleName` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`RID`, `RoleName`) VALUES
(1, 'admin'),
(2, 'staff'),
(3, 'individual'),
(4, 'enterprise');

-- --------------------------------------------------------

--
-- Table structure for table `size`
--

CREATE TABLE `size` (
  `SID` int(11) NOT NULL,
  `size` varchar(45) NOT NULL,
  `size_price_multi` decimal(3,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `size`
--

INSERT INTO `size` (`SID`, `size`, `size_price_multi`) VALUES
(1, 'Small', 0.80),
(2, 'Medium', 1.00),
(3, 'Large', 1.20);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UID` varchar(36) NOT NULL,
  `username` varchar(45) NOT NULL,
  `role_ID` int(11) NOT NULL,
  `status_ID` int(11) NOT NULL,
  `payment_ID` int(11) DEFAULT NULL,
  `address_ID` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UID`, `username`, `role_ID`, `status_ID`, `payment_ID`, `address_ID`, `email`, `password_hash`) VALUES
('1', 'John SWAP Tester', 1, 1, 1, 1, 'john.tester@gmail.com', '1234561234'),
('1bd706ac-fc8c-11f0-90b0-a05950b924a8', 'test user 3', 2, 6, 1, 1, 'test.user.3@gmail.com', '$2y$10$5/pxuoePhQniizGLwCLojOTX2hWBMEdjhO/3Vc.XUU0OZa/3RciXe'),
('2', 'John Customer', 3, 1, 1, 1, 'yoooooo.yo@hotmail.com', 'qowr1u1313t'),
('3', 'John Enterprise', 4, 1, 1, 1, 'Aw.shucks@hotmail.com', '9quegoqegjqeg'),
('75281e3f-fc8b-11f0-90b0-a05950b924a8', 'staff dude', 1, 2, 1, 1, 'staff.dude@gmail.com', '$2y$10$ZG9QAFVffrGU0nRUHAADceM/OM1cjU534RbdENA1/4fkT7festm.K'),
('899c0bfb-fbb9-11f0-90b0-a05950b924a8', '2404716I@student.tp.edu.sg', 4, 2, NULL, NULL, '2404716I@student.tp.edu.sg', '$2y$10$CTCoTphW49u.8l.TzmBdeeQu7TCtJ0tXS/F7w.LVtfxXIgMmy/PmO'),
('a745f9d2-fc86-11f0-90b0-a05950b924a8', 'Ombruh', 2, 4, 1, 1, 'Om.bruh@gmail.com', '$2y$10$0Jd.Vpo6LwxMwr6Y4QJLVeJnDPBcXjY53NKfBERXnOCLBCZQYbFuW'),
('ab957136-fc91-11f0-90b0-a05950b924a8', 'Login Tester', 1, 1, 1, 1, 'test.login@gmail.com', '$2y$10$iQcLLpwb7auf2jgcsYee7eckggXfI3NY57L7DWUHCDl2BPz..0Mjy'),
('bdd0f262-fb55-11f0-90b0-a05950b924a8', 'testttt', 3, 3, NULL, NULL, 'johnny.test@gmail.com', '$2y$10$WIFXlJf3ZEuYisp1F3PGWObsQFCrgzdnc01QytSUfUOpMFnUEyHcu'),
('dd2c7cdb-fb55-11f0-90b0-a05950b924a8', 'bleh', 3, 3, NULL, NULL, 'bleh.bleh@gmail.com', '$2y$10$ictBQTukL8rORmqsgD7oNO53vENXzs40SLG9z9vtdO0PB.A22/h0m');

-- --------------------------------------------------------

--
-- Table structure for table `user_audit_logging`
--

CREATE TABLE `user_audit_logging` (
  `user_audit_id` int(11) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `action` varchar(45) NOT NULL,
  `actor_id` varchar(36) NOT NULL,
  `previous_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`previous_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`new_values`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_stat`
--

CREATE TABLE `user_stat` (
  `USID` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user_stat`
--

INSERT INTO `user_stat` (`USID`, `status_name`) VALUES
(1, 'active'),
(2, 'inactive'),
(3, 'pending_activation'),
(4, 'locked'),
(5, 'suspended'),
(6, 'password_expired');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`AID`);

--
-- Indexes for table `colour`
--
ALTER TABLE `colour`
  ADD PRIMARY KEY (`CID`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`IID`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- Indexes for table `item_audit_logging`
--
ALTER TABLE `item_audit_logging`
  ADD PRIMARY KEY (`item_audit_id`),
  ADD KEY `idx_item_audit_item` (`item_id`),
  ADD KEY `idx_item_audit_actor` (`actor_id`);

--
-- Indexes for table `order_stat`
--
ALTER TABLE `order_stat`
  ADD PRIMARY KEY (`OSID`);

--
-- Indexes for table `order_table`
--
ALTER TABLE `order_table`
  ADD PRIMARY KEY (`OID`),
  ADD KEY `idx_order_user` (`user_id`),
  ADD KEY `idx_order_status` (`order_status_id`),
  ADD KEY `idx_order_colour` (`colour_id`),
  ADD KEY `idx_order_size` (`size_id`),
  ADD KEY `idx_order_item` (`item_id`),
  ADD KEY `idx_order_payment` (`payment_id`),
  ADD KEY `idx_order_address` (`address_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PID`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`RID`);

--
-- Indexes for table `size`
--
ALTER TABLE `size`
  ADD PRIMARY KEY (`SID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UID`),
  ADD KEY `idx_user_role` (`role_ID`),
  ADD KEY `idx_user_payment` (`payment_ID`),
  ADD KEY `idx_user_address` (`address_ID`),
  ADD KEY `fk_user_status` (`status_ID`);

--
-- Indexes for table `user_audit_logging`
--
ALTER TABLE `user_audit_logging`
  ADD PRIMARY KEY (`user_audit_id`),
  ADD KEY `idx_user_audit_user` (`user_id`),
  ADD KEY `idx_user_audit_actor` (`actor_id`);

--
-- Indexes for table `user_stat`
--
ALTER TABLE `user_stat`
  ADD PRIMARY KEY (`USID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `AID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `colour`
--
ALTER TABLE `colour`
  MODIFY `CID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `IID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=910;

--
-- AUTO_INCREMENT for table `item_audit_logging`
--
ALTER TABLE `item_audit_logging`
  MODIFY `item_audit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_stat`
--
ALTER TABLE `order_stat`
  MODIFY `OSID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_table`
--
ALTER TABLE `order_table`
  MODIFY `OID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `RID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `size`
--
ALTER TABLE `size`
  MODIFY `SID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_audit_logging`
--
ALTER TABLE `user_audit_logging`
  MODIFY `user_audit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_stat`
--
ALTER TABLE `user_stat`
  MODIFY `USID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`RID`) ON UPDATE CASCADE;

--
-- Constraints for table `item_audit_logging`
--
ALTER TABLE `item_audit_logging`
  ADD CONSTRAINT `fk_item_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `user` (`UID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_item_audit_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`IID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `order_table`
--
ALTER TABLE `order_table`
  ADD CONSTRAINT `fk_order_address` FOREIGN KEY (`address_id`) REFERENCES `address` (`AID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_colour` FOREIGN KEY (`colour_id`) REFERENCES `colour` (`CID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_item` FOREIGN KEY (`item_id`) REFERENCES `item` (`IID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_payment` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`PID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_size` FOREIGN KEY (`size_id`) REFERENCES `size` (`SID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_status` FOREIGN KEY (`order_status_id`) REFERENCES `order_stat` (`OSID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UID`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_address` FOREIGN KEY (`address_ID`) REFERENCES `address` (`AID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_user_payment` FOREIGN KEY (`payment_ID`) REFERENCES `payment` (`PID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_ID`) REFERENCES `roles` (`RID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_status` FOREIGN KEY (`status_ID`) REFERENCES `user_stat` (`USID`) ON UPDATE CASCADE;

--
-- Constraints for table `user_audit_logging`
--
ALTER TABLE `user_audit_logging`
  ADD CONSTRAINT `fk_user_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `user` (`UID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_user_audit_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`UID`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

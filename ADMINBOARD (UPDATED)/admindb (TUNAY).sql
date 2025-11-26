-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 03:11 PM
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
-- Database: `admindb`
--

-- --------------------------------------------------------
--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `TeacherID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Department` varchar(255) NOT NULL,
  PRIMARY KEY (`TeacherID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample data for table `teachers`
--

INSERT INTO `teachers` (`Name`, `Department`) VALUES
('Juan Dela Cruz', 'Mathematics'),
('Maria Santos', 'Science'),
('Pedro Reyes', 'Filipino');

-- --------------------------------------------------------
--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `SubjectID` int(11) NOT NULL AUTO_INCREMENT,
  `SubjectName` varchar(255) NOT NULL,
  `SubjectCode` varchar(50) NOT NULL,
  PRIMARY KEY (`SubjectID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample data for table `subjects`
--

INSERT INTO `subjects` (`SubjectName`, `SubjectCode`) VALUES
('Calculus', 'MATH101'),
('Physics', 'SCI102'),
('Filipino Literature', 'FIL103');

-- --------------------------------------------------------
--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `BuildingID` int(11) NOT NULL,
  `BuildingName` varchar(100) NOT NULL,
  `CampusID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`BuildingID`, `BuildingName`, `CampusID`) VALUES
(1, 'CEIT', 1),
(2, 'CABA', 1),
(3, 'COED', 1),
(4, 'CAS', 2),
(5, 'NB', 2),
(6, 'CPAG', 3);

-- --------------------------------------------------------
--
-- Table structure for table `campus`
--

CREATE TABLE `campus` (
  `CampusID` int(11) NOT NULL,
  `CampusName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campus`
--

INSERT INTO `campus` (`CampusID`, `CampusName`) VALUES
(1, 'Main'),
(2, 'Annex'),
(3, 'CPAG');

-- --------------------------------------------------------
--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `ReservationID` int(11) NOT NULL,
  `InstructorName` varchar(100) NOT NULL,
  `SubjectCode` varchar(50) NOT NULL,
  `CourseSection` varchar(50) NOT NULL,
  `Campus` varchar(50) NOT NULL,
  `Building` varchar(50) NOT NULL,
  `Date` date NOT NULL,
  `Time` varchar(50) NOT NULL,
  `Status` enum('active','expired') DEFAULT 'active',
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `RoomID` int(11) NOT NULL,
  `Room` varchar(100) NOT NULL,
  `PdfPath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `rooms`
--

-- Table: users
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `station` VARCHAR(100) DEFAULT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `task` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `rooms` (
  `RoomID` int(11) NOT NULL,
  `RoomName` varchar(100) NOT NULL,
  `BuildingID` int(11) NOT NULL,
  `TimeAvailable` varchar(100) DEFAULT NULL,
  `DaysAvailable` varchar(100) DEFAULT NULL,
  `DaysOccupied` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`RoomID`, `RoomName`, `BuildingID`, `TimeAvailable`, `DaysAvailable`, `DaysOccupied`) VALUES
(1, 'CAS301', 4, '8:00 AM – 11:00 AM', 'Monday,Tuesday,Wednesday,Thursday,Friday', 'Saturday'),
(2, 'CEIT201', 1, '9:00 AM – 12:00 PM', 'Tuesday,Wednesday,Thursday', 'Monday, Friday, Saturday'),
(3, 'CABA102', 2, '10:30 AM – 1:30 PM', 'Monday,Tuesday,Wednesday', 'Thursday, Friday, Saturday'),
(4, 'CEIT305', 1, '12:00 PM – 3:00 PM', 'Wednesday,Thursday,Friday,Saturday', 'Monday, Tuesday'),
(5, 'CAS204', 4, '1:30 PM – 4:30 PM', 'Monday,Tuesday,Wednesday,Thursday,Friday', 'Saturday'),
(6, 'COED101', 3, '8:00 AM – 10:00 AM', 'Monday,Wednesday,Friday', 'Tuesday, Thursday, Saturday'),
(7, 'COED202', 3, '10:00 AM – 1:00 PM', 'Tuesday,Thursday', 'Monday, Wednesday, Friday, Saturday'),
(8, 'NB105', 5, '9:00 AM – 11:00 AM', 'Monday,Tuesday,Wednesday', 'Thursday, Friday, Saturday'),
(9, 'NB210', 5, '1:00 PM – 4:00 PM', 'Wednesday,Thursday,Friday', 'Monday, Tuesday, Saturday'),
(10, 'CPAG001', 6, '8:30 AM – 11:30 AM', 'Monday,Tuesday,Wednesday,Thursday', 'Friday, Saturday'),
(11, 'CPAG102', 6, '12:00 PM – 3:00 PM', 'Tuesday,Wednesday,Friday', 'Monday, Thursday, Saturday'),
(12, 'CABA203', 2, '2:00 PM – 5:00 PM', 'Monday,Wednesday,Friday', 'Tuesday, Thursday, Saturday'),
(13, 'CEIT101', 1, '7:30 AM – 10:30 AM', 'Monday,Tuesday,Thursday', 'Wednesday, Friday, Saturday'),
(14, 'CAS105', 4, '10:00 AM – 12:00 PM', 'Tuesday,Thursday,Friday', 'Monday, Wednesday, Saturday'),
(15, 'COED303', 3, '3:00 PM – 6:00 PM', 'Monday,Tuesday,Wednesday', 'Thursday, Friday, Saturday');

-- --------------------------------------------------------
--
-- Table structure for table `transactionlogs`
--

CREATE TABLE `transactionlogs` (
  `LogID` int(11) NOT NULL,
  `ReservationID` int(11) NOT NULL,
  `PDFPath` varchar(255) DEFAULT NULL,
  `LoggedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Indexes
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`BuildingID`),
  ADD KEY `CampusID` (`CampusID`);

ALTER TABLE `campus`
  ADD PRIMARY KEY (`CampusID`);

ALTER TABLE `reservations`
  ADD PRIMARY KEY (`ReservationID`);

ALTER TABLE `rooms`
  ADD PRIMARY KEY (`RoomID`),
  ADD KEY `BuildingID` (`BuildingID`);

ALTER TABLE `transactionlogs`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `ReservationID` (`ReservationID`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
ALTER TABLE `reservations`
  MODIFY `ReservationID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `transactionlogs`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Constraints
ALTER TABLE `buildings`
  ADD CONSTRAINT `buildings_ibfk_1` FOREIGN KEY (`CampusID`) REFERENCES `campus` (`CampusID`);

ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`BuildingID`) REFERENCES `buildings` (`BuildingID`);

ALTER TABLE `transactionlogs`
  ADD CONSTRAINT `transactionlogs_ibfk_1` FOREIGN KEY (`ReservationID`) REFERENCES `reservations` (`ReservationID`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 27, 2024 at 01:53 PM
-- Server version: 5.7.34
-- PHP Version: 8.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lmf`
--

-- --------------------------------------------------------

--
-- Table structure for table `AcademicYears`
--

CREATE TABLE `AcademicYears` (
  `academic_year_id` int(11) NOT NULL,
  `academic_year` varchar(9) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `AcademicYears`
--

INSERT INTO `AcademicYears` (`academic_year_id`, `academic_year`) VALUES
(1, '2024-2025'),
(2, '2025-2026'),
(3, '2026-2027'),
(4, '2027-2028'),
(5, '2028-2029'),
(6, '2029-2030');

-- --------------------------------------------------------

--
-- Table structure for table `Admins`
--

CREATE TABLE `Admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Admins`
--

INSERT INTO `Admins` (`admin_id`, `username`, `password`) VALUES
(1, 'muiz.dev.io@gmail.com', '$2y$10$ZeXiVw/nwE7SrRSiQJYitOzcOXQ/BeSlzkN7ZCelun2SQohgWcHa2');

-- --------------------------------------------------------

--
-- Table structure for table `Courses`
--

CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Courses`
--

INSERT INTO `Courses` (`course_id`, `course_code`, `course_name`, `department_id`) VALUES
(1, 'Hey', 'Mech -122', 1),
(2, 'Hey-yo', 'Mech -125', 2);

-- --------------------------------------------------------

--
-- Table structure for table `Departments`
--

CREATE TABLE `Departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Departments`
--

INSERT INTO `Departments` (`department_id`, `department_name`) VALUES
(1, 'Multimedia Technology'),
(2, 'Business Informatics'),
(3, 'Software Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `FinalRemarks`
--

CREATE TABLE `FinalRemarks` (
  `remark_id` int(11) NOT NULL,
  `min_gpa` decimal(3,2) NOT NULL,
  `max_gpa` decimal(3,2) NOT NULL,
  `remark` enum('Distinction','Upper Credit','Lower Credit','Pass','Fail') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `FinalRemarks`
--

INSERT INTO `FinalRemarks` (`remark_id`, `min_gpa`, `max_gpa`, `remark`) VALUES
(1, 3.50, 4.00, 'Distinction'),
(2, 3.00, 3.49, 'Upper Credit'),
(3, 2.50, 2.99, 'Lower Credit'),
(4, 2.00, 2.49, 'Pass'),
(5, 0.00, 1.99, 'Fail');

-- --------------------------------------------------------

--
-- Table structure for table `Grades`
--

CREATE TABLE `Grades` (
  `grade_id` int(11) NOT NULL,
  `grade_letter` char(1) NOT NULL,
  `min_percentage` decimal(5,2) NOT NULL,
  `max_percentage` decimal(5,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Grades`
--

INSERT INTO `Grades` (`grade_id`, `grade_letter`, `min_percentage`, `max_percentage`) VALUES
(1, 'A', 70.00, 100.00),
(2, 'B', 60.00, 69.00),
(3, 'C', 50.00, 59.00),
(4, 'D', 45.00, 49.00),
(5, 'E', 40.00, 44.00),
(6, 'F', 0.00, 39.00);

-- --------------------------------------------------------

--
-- Table structure for table `Results`
--

CREATE TABLE `Results` (
  `result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `grade_id` int(11) NOT NULL,
  `final_remark` enum('Distinction','Upper Credit','Lower Credit','Pass','Fail') DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Results`
--

INSERT INTO `Results` (`result_id`, `student_id`, `course_id`, `academic_year_id`, `session_id`, `score`, `grade_id`, `final_remark`) VALUES
(1, 1, 1, 1, 1, 80.00, 1, NULL),
(2, 2, 2, 1, 1, 90.00, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Sessions`
--

CREATE TABLE `Sessions` (
  `session_id` int(11) NOT NULL,
  `session_name` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Sessions`
--

INSERT INTO `Sessions` (`session_id`, `session_name`) VALUES
(1, 'First Semester'),
(2, 'Second Semester');

-- --------------------------------------------------------

--
-- Table structure for table `StudentCourses`
--

CREATE TABLE `StudentCourses` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `StudentCourses`
--

INSERT INTO `StudentCourses` (`student_id`, `course_id`, `academic_year_id`, `session_id`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `StudentOverallResults`
--

CREATE TABLE `StudentOverallResults` (
  `overall_result_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `cumulative_gpa` decimal(3,2) NOT NULL,
  `total_credits_earned` int(11) NOT NULL,
  `overall_grade_letter` char(1) NOT NULL,
  `final_remark` enum('Distinction','Upper Credit','Lower Credit','Pass','Fail') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `StudentOverallResults`
--

INSERT INTO `StudentOverallResults` (`overall_result_id`, `student_id`, `academic_year_id`, `session_id`, `cumulative_gpa`, `total_credits_earned`, `overall_grade_letter`, `final_remark`) VALUES
(1, 1, 1, 1, 4.00, 1, 'A', 'Distinction'),
(2, 2, 1, 1, 4.00, 1, 'A', 'Distinction');

-- --------------------------------------------------------

--
-- Table structure for table `Students`
--

CREATE TABLE `Students` (
  `student_id` int(11) NOT NULL,
  `matriculation_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `department_id` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `class` enum('ND1','ND2','HND1','HND2') NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Students`
--

INSERT INTO `Students` (`student_id`, `matriculation_number`, `first_name`, `last_name`, `department_id`, `gender`, `class`, `academic_year_id`, `session_id`, `password`) VALUES
(1, '2947484947473', 'Muiz', 'Hey', 1, 'Male', 'ND1', 1, 1, '$2y$10$LBmbdswDY2vGxUzTl2K4eO2U4WKl46qxf1V/0/a80O6e4M1pEBGi.'),
(2, '29474564947473', 'Jay', 'Hhdjd', 2, 'Female', 'ND1', 1, 1, '$2y$10$A1T629sQYO.WNZR2NboE9eZre.SYxWzRNDi0W0g8gOlK7Ogr1uW4.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AcademicYears`
--
ALTER TABLE `AcademicYears`
  ADD PRIMARY KEY (`academic_year_id`),
  ADD UNIQUE KEY `academic_year` (`academic_year`);

--
-- Indexes for table `Admins`
--
ALTER TABLE `Admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `Courses`
--
ALTER TABLE `Courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `Departments`
--
ALTER TABLE `Departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `FinalRemarks`
--
ALTER TABLE `FinalRemarks`
  ADD PRIMARY KEY (`remark_id`);

--
-- Indexes for table `Grades`
--
ALTER TABLE `Grades`
  ADD PRIMARY KEY (`grade_id`);

--
-- Indexes for table `Results`
--
ALTER TABLE `Results`
  ADD PRIMARY KEY (`result_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`course_id`,`academic_year_id`,`session_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `grade_id` (`grade_id`);

--
-- Indexes for table `Sessions`
--
ALTER TABLE `Sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `session_name` (`session_name`);

--
-- Indexes for table `StudentCourses`
--
ALTER TABLE `StudentCourses`
  ADD PRIMARY KEY (`student_id`,`course_id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`,`academic_year_id`,`session_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `StudentOverallResults`
--
ALTER TABLE `StudentOverallResults`
  ADD PRIMARY KEY (`overall_result_id`),
  ADD UNIQUE KEY `unique_student_year_session` (`student_id`,`academic_year_id`,`session_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `Students`
--
ALTER TABLE `Students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `matriculation_number` (`matriculation_number`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `academic_year_id` (`academic_year_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `idx_matriculation_number` (`matriculation_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AcademicYears`
--
ALTER TABLE `AcademicYears`
  MODIFY `academic_year_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Admins`
--
ALTER TABLE `Admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Courses`
--
ALTER TABLE `Courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Departments`
--
ALTER TABLE `Departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `FinalRemarks`
--
ALTER TABLE `FinalRemarks`
  MODIFY `remark_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Grades`
--
ALTER TABLE `Grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Results`
--
ALTER TABLE `Results`
  MODIFY `result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Sessions`
--
ALTER TABLE `Sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `StudentOverallResults`
--
ALTER TABLE `StudentOverallResults`
  MODIFY `overall_result_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Students`
--
ALTER TABLE `Students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 06, 2026 at 04:44 AM
-- Server version: 8.0.45
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `bookID` int NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `authorName` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `coverUrl` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `firstPublishYear` date NOT NULL,
  `edition` int NOT NULL,
  `format` set('Digital','Physical') COLLATE utf8mb4_general_ci NOT NULL,
  `createdAt` date NOT NULL,
  `updatedAt` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`bookID`, `title`, `authorName`, `coverUrl`, `firstPublishYear`, `edition`, `format`, `createdAt`, `updatedAt`) VALUES
(1, 'Book 1', 'Author 1', 'cover1.jpg', '2001-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(2, 'Book 2', 'Author 2', 'cover2.jpg', '2002-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(3, 'Book 3', 'Author 3', 'cover3.jpg', '2003-01-01', 2, 'Digital', '2026-03-04', '2026-03-04'),
(4, 'Book 4', 'Author 4', 'cover4.jpg', '2004-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(5, 'Book 5', 'Author 5', 'cover5.jpg', '2005-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(6, 'Book 6', 'Author 6', 'cover6.jpg', '2006-01-01', 2, 'Physical', '2026-03-04', '2026-03-04'),
(7, 'Book 7', 'Author 7', 'cover7.jpg', '2007-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(8, 'Book 8', 'Author 8', 'cover8.jpg', '2008-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(9, 'Book 9', 'Author 9', 'cover9.jpg', '2009-01-01', 2, 'Digital', '2026-03-04', '2026-03-04'),
(10, 'Book 10', 'Author 10', 'cover10.jpg', '2010-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(11, 'Book 11', 'Author 11', 'cover11.jpg', '2011-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(12, 'Book 12', 'Author 12', 'cover12.jpg', '2012-01-01', 2, 'Physical', '2026-03-04', '2026-03-04'),
(13, 'Book 13', 'Author 13', 'cover13.jpg', '2013-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(14, 'Book 14', 'Author 14', 'cover14.jpg', '2014-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(15, 'Book 15', 'Author 15', 'cover15.jpg', '2015-01-01', 2, 'Digital', '2026-03-04', '2026-03-04'),
(16, 'Book 16', 'Author 16', 'cover16.jpg', '2016-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(17, 'Book 17', 'Author 17', 'cover17.jpg', '2017-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(18, 'Book 18', 'Author 18', 'cover18.jpg', '2018-01-01', 2, 'Physical', '2026-03-04', '2026-03-04'),
(19, 'Book 19', 'Author 19', 'cover19.jpg', '2019-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(20, 'Book 20', 'Author 20', 'cover20.jpg', '2020-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(21, 'Book 21', 'Author 21', 'cover21.jpg', '2021-01-01', 2, 'Digital', '2026-03-04', '2026-03-04'),
(22, 'Book 22', 'Author 22', 'cover22.jpg', '2022-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(23, 'Book 23', 'Author 23', 'cover23.jpg', '2023-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(24, 'Book 24', 'Author 24', 'cover24.jpg', '2024-01-01', 2, 'Physical', '2026-03-04', '2026-03-04'),
(25, 'Book 25', 'Author 25', 'cover25.jpg', '2010-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(26, 'Book 26', 'Author 26', 'cover26.jpg', '2011-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(27, 'Book 27', 'Author 27', 'cover27.jpg', '2012-01-01', 2, 'Digital', '2026-03-04', '2026-03-04'),
(28, 'Book 28', 'Author 28', 'cover28.jpg', '2013-01-01', 1, 'Physical', '2026-03-04', '2026-03-04'),
(29, 'Book 29', 'Author 29', 'cover29.jpg', '2014-01-01', 1, 'Digital', '2026-03-04', '2026-03-04'),
(30, 'Book 30', 'Author 30', 'cover30.jpg', '2015-01-01', 2, 'Physical', '2026-03-04', '2026-03-04');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loansID` int NOT NULL,
  `userID` int NOT NULL,
  `bookID` int NOT NULL,
  `dueDate` date NOT NULL,
  `Status` set('In progress','Finished') COLLATE utf8mb4_general_ci NOT NULL,
  `createdAt` date NOT NULL,
  `updatedAt` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`loansID`, `userID`, `bookID`, `dueDate`, `Status`, `createdAt`, `updatedAt`) VALUES
(1, 1, 2, '2026-02-10', 'In progress', '2026-03-04', '2026-03-04'),
(2, 2, 3, '2026-02-11', 'Finished', '2026-03-04', '2026-03-04'),
(3, 3, 4, '2026-02-12', 'In progress', '2026-03-04', '2026-03-04'),
(4, 4, 5, '2026-02-13', 'Finished', '2026-03-04', '2026-03-04'),
(5, 5, 6, '2026-02-14', 'In progress', '2026-03-04', '2026-03-04'),
(6, 6, 7, '2026-02-15', 'Finished', '2026-03-04', '2026-03-04'),
(7, 7, 8, '2026-02-16', 'In progress', '2026-03-04', '2026-03-04'),
(8, 8, 9, '2026-02-17', 'Finished', '2026-03-04', '2026-03-04'),
(9, 9, 10, '2026-02-18', 'In progress', '2026-03-04', '2026-03-04'),
(10, 10, 11, '2026-02-19', 'Finished', '2026-03-04', '2026-03-04'),
(11, 11, 12, '2026-02-20', 'In progress', '2026-03-04', '2026-03-04'),
(12, 12, 13, '2026-02-21', 'Finished', '2026-03-04', '2026-03-04'),
(13, 13, 14, '2026-02-22', 'In progress', '2026-03-04', '2026-03-04'),
(14, 14, 15, '2026-02-23', 'Finished', '2026-03-04', '2026-03-04'),
(15, 15, 16, '2026-02-24', 'In progress', '2026-03-04', '2026-03-04'),
(16, 16, 17, '2026-02-25', 'Finished', '2026-03-04', '2026-03-04'),
(17, 17, 18, '2026-02-26', 'In progress', '2026-03-04', '2026-03-04'),
(18, 18, 19, '2026-02-27', 'Finished', '2026-03-04', '2026-03-04'),
(19, 19, 20, '2026-02-28', 'In progress', '2026-03-04', '2026-03-04'),
(20, 20, 21, '2026-03-01', 'Finished', '2026-03-04', '2026-03-04'),
(21, 21, 22, '2026-03-02', 'In progress', '2026-03-04', '2026-03-04'),
(22, 22, 23, '2026-03-03', 'Finished', '2026-03-04', '2026-03-04'),
(23, 23, 24, '2026-03-04', 'In progress', '2026-03-04', '2026-03-04'),
(24, 24, 25, '2026-03-05', 'Finished', '2026-03-04', '2026-03-04'),
(25, 25, 26, '2026-03-06', 'In progress', '2026-03-04', '2026-03-04'),
(26, 26, 27, '2026-03-07', 'Finished', '2026-03-04', '2026-03-04'),
(27, 27, 28, '2026-03-08', 'In progress', '2026-03-04', '2026-03-04'),
(28, 28, 29, '2026-03-09', 'Finished', '2026-03-04', '2026-03-04'),
(29, 29, 30, '2026-03-10', 'In progress', '2026-03-04', '2026-03-04'),
(30, 30, 1, '2026-03-11', 'Finished', '2026-03-04', '2026-03-04');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `puechaseId` int NOT NULL,
  `userID` int NOT NULL,
  `bookID` int NOT NULL,
  `Price` decimal(6,2) NOT NULL,
  `purchaseAt` date NOT NULL,
  `createdAt` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`puechaseId`, `userID`, `bookID`, `Price`, `purchaseAt`, `createdAt`) VALUES
(1, 1, 1, 10.99, '2026-03-04', '2026-03-04'),
(2, 2, 2, 9.50, '2026-03-04', '2026-03-04'),
(3, 3, 3, 8.40, '2026-03-04', '2026-03-04'),
(4, 4, 4, 11.25, '2026-03-04', '2026-03-04'),
(5, 5, 5, 7.80, '2026-03-04', '2026-03-04'),
(6, 6, 6, 13.60, '2026-03-04', '2026-03-04'),
(7, 7, 7, 12.00, '2026-03-04', '2026-03-04'),
(8, 8, 8, 9.99, '2026-03-04', '2026-03-04'),
(9, 9, 9, 6.75, '2026-03-04', '2026-03-04'),
(10, 10, 10, 10.20, '2026-03-04', '2026-03-04'),
(11, 11, 11, 7.90, '2026-03-04', '2026-03-04'),
(12, 12, 12, 14.50, '2026-03-04', '2026-03-04'),
(13, 13, 13, 5.60, '2026-03-04', '2026-03-04'),
(14, 14, 14, 16.10, '2026-03-04', '2026-03-04'),
(15, 15, 15, 9.80, '2026-03-04', '2026-03-04'),
(16, 16, 16, 8.75, '2026-03-04', '2026-03-04'),
(17, 17, 17, 6.99, '2026-03-04', '2026-03-04'),
(18, 18, 18, 11.10, '2026-03-04', '2026-03-04'),
(19, 19, 19, 7.40, '2026-03-04', '2026-03-04'),
(20, 20, 20, 12.90, '2026-03-04', '2026-03-04'),
(21, 21, 21, 5.50, '2026-03-04', '2026-03-04'),
(22, 22, 22, 10.30, '2026-03-04', '2026-03-04'),
(23, 23, 23, 8.60, '2026-03-04', '2026-03-04'),
(24, 24, 24, 9.20, '2026-03-04', '2026-03-04'),
(25, 25, 25, 6.70, '2026-03-04', '2026-03-04'),
(26, 26, 26, 11.80, '2026-03-04', '2026-03-04'),
(27, 27, 27, 10.10, '2026-03-04', '2026-03-04'),
(28, 28, 28, 12.60, '2026-03-04', '2026-03-04'),
(29, 29, 29, 7.30, '2026-03-04', '2026-03-04'),
(30, 30, 30, 14.00, '2026-03-04', '2026-03-04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int NOT NULL,
  `userName` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `paswordHash` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `createdAt` int NOT NULL,
  `updateAt` int NOT NULL,
  `userType` set('regular','admin') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `userName`, `email`, `paswordHash`, `createdAt`, `updateAt`, `userType`) VALUES
(1, 'User01', 'user01@mail.com', 'hash01', 1772683760, 1772683760, ''),
(2, 'User02', 'user02@mail.com', 'hash02', 1772683760, 1772683760, ''),
(3, 'User03', 'user03@mail.com', 'hash03', 1772683760, 1772683760, ''),
(4, 'User04', 'user04@mail.com', 'hash04', 1772683760, 1772683760, ''),
(5, 'User05', 'user05@mail.com', 'hash05', 1772683760, 1772683760, ''),
(6, 'User06', 'user06@mail.com', 'hash06', 1772683760, 1772683760, ''),
(7, 'User07', 'user07@mail.com', 'hash07', 1772683760, 1772683760, ''),
(8, 'User08', 'user08@mail.com', 'hash08', 1772683760, 1772683760, ''),
(9, 'User09', 'user09@mail.com', 'hash09', 1772683760, 1772683760, ''),
(10, 'User10', 'user10@mail.com', 'hash10', 1772683760, 1772683760, ''),
(11, 'User11', 'user11@mail.com', 'hash11', 1772683760, 1772683760, ''),
(12, 'User12', 'user12@mail.com', 'hash12', 1772683760, 1772683760, ''),
(13, 'User13', 'user13@mail.com', 'hash13', 1772683760, 1772683760, ''),
(14, 'User14', 'user14@mail.com', 'hash14', 1772683760, 1772683760, ''),
(15, 'User15', 'user15@mail.com', 'hash15', 1772683760, 1772683760, ''),
(16, 'User16', 'user16@mail.com', 'hash16', 1772683760, 1772683760, ''),
(17, 'User17', 'user17@mail.com', 'hash17', 1772683760, 1772683760, ''),
(18, 'User18', 'user18@mail.com', 'hash18', 1772683760, 1772683760, ''),
(19, 'User19', 'user19@mail.com', 'hash19', 1772683760, 1772683760, ''),
(20, 'User20', 'user20@mail.com', 'hash20', 1772683760, 1772683760, ''),
(21, 'User21', 'user21@mail.com', 'hash21', 1772683760, 1772683760, ''),
(22, 'User22', 'user22@mail.com', 'hash22', 1772683760, 1772683760, ''),
(23, 'User23', 'user23@mail.com', 'hash23', 1772683760, 1772683760, ''),
(24, 'User24', 'user24@mail.com', 'hash24', 1772683760, 1772683760, ''),
(25, 'User25', 'user25@mail.com', 'hash25', 1772683760, 1772683760, ''),
(26, 'User26', 'user26@mail.com', 'hash26', 1772683760, 1772683760, ''),
(27, 'User27', 'user27@mail.com', 'hash27', 1772683760, 1772683760, ''),
(28, 'User28', 'user28@mail.com', 'hash28', 1772683760, 1772683760, ''),
(29, 'User29', 'user29@mail.com', 'hash29', 1772683760, 1772683760, ''),
(30, 'User30', 'user30@mail.com', 'hash30', 1772683760, 1772683760, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`bookID`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loansID`),
  ADD KEY `userID_FK_loans` (`userID`),
  ADD KEY `bookID_FK_loans` (`bookID`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`puechaseId`),
  ADD KEY `userID_FK` (`userID`),
  ADD KEY `bookID_FK` (`bookID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `bookID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loansID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `puechaseId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `bookID_FK_loans` FOREIGN KEY (`bookID`) REFERENCES `books` (`bookID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `userID_FK_loans` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `bookID_FK` FOREIGN KEY (`bookID`) REFERENCES `books` (`bookID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `userID_FK` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

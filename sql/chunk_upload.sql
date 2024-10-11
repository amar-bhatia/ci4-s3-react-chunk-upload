-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2024 at 07:32 AM
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
-- Database: `sprint_ds`
--

-- --------------------------------------------------------

--
-- Table structure for table `chunk_upload_files`
--

CREATE TABLE `chunk_upload_files` (
  `ChunkUploadFileID` int(11) NOT NULL,
  `ChunkUploadTrackingID` int(11) NOT NULL,
  `ETag` text NOT NULL,
  `PartNumber` int(11) NOT NULL,
  `ChunkSize` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chunk_upload_tracking`
--

CREATE TABLE `chunk_upload_tracking` (
  `ChunkUploadTrackingID` int(11) NOT NULL,
  `UploadID` text NOT NULL,
  `ProductID` int(11) NOT NULL,
  `FileName` text NOT NULL,
  `FilePath` text NOT NULL,
  `FileType` text NOT NULL,
  `MIMEType` varchar(50) NOT NULL,
  `TotalChunks` int(11) NOT NULL,
  `TotalFileSize` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chunk_upload_files`
--
ALTER TABLE `chunk_upload_files`
  ADD PRIMARY KEY (`ChunkUploadFileID`),
  ADD KEY `ChunkUploadTrackingID` (`ChunkUploadTrackingID`);

--
-- Indexes for table `chunk_upload_tracking`
--
ALTER TABLE `chunk_upload_tracking`
  ADD PRIMARY KEY (`ChunkUploadTrackingID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chunk_upload_files`
--
ALTER TABLE `chunk_upload_files`
  MODIFY `ChunkUploadFileID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chunk_upload_tracking`
--
ALTER TABLE `chunk_upload_tracking`
  MODIFY `ChunkUploadTrackingID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chunk_upload_files`
--
ALTER TABLE `chunk_upload_files`
  ADD CONSTRAINT `chunk_upload_files_ibfk_1` FOREIGN KEY (`ChunkUploadTrackingID`) REFERENCES `chunk_upload_tracking` (`ChunkUploadTrackingID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

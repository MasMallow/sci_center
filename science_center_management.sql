-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2024 at 04:07 PM
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
-- Database: `science_center_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `approve_to_reserve`
--

CREATE TABLE `approve_to_reserve` (
  `ID` int(50) NOT NULL,
  `serial_number` varchar(7) NOT NULL,
  `userID` int(5) NOT NULL,
  `name_user` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `list_name` varchar(255) NOT NULL,
  `sn_list` varchar(255) DEFAULT NULL,
  `reservation_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approver` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `approvaldatetime` datetime DEFAULT NULL,
  `situation` smallint(1) DEFAULT NULL,
  `Usage_item` tinyint(1) DEFAULT 0,
  `date_return` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crud`
--

CREATE TABLE `crud` (
  `ID` int(100) NOT NULL,
  `img_name` varchar(100) NOT NULL,
  `sci_name` varchar(255) NOT NULL COMMENT 'ชื่ออุปกรณ์',
  `serial_number` varchar(20) NOT NULL,
  `categories` set('วัสดุ','อุปกรณ์','เครื่องมือ') DEFAULT NULL COMMENT 'ประเภทเครื่องมือ',
  `amount` int(10) DEFAULT 0 COMMENT 'จำนวนสินค้าคงเหลือ',
  `uploaded_on` datetime NOT NULL COMMENT 'วันที่อัปโหลด',
  `availability` tinyint(1) NOT NULL COMMENT 'ความพร้อมการใช้งาน'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `crud`
--

INSERT INTO `crud` (`ID`, `img_name`, `sci_name`, `serial_number`, `categories`, `amount`, `uploaded_on`, `availability`) VALUES
(1, '1746883911.jpg', 'ตู้อบ', 'THLB519.0119', 'อุปกรณ์', 1, '2024-07-29 16:58:24', 1),
(2, '66a768141ddad.jpg', 'เครื่องแตกเซลล์โดยใช้ความดันสูง | High Pressure Cell Disrupter', '1246', 'อุปกรณ์', 21, '2024-07-29 16:59:48', 0),
(3, '1972546210.jpg', 'เครื่องกวนสารละลายแบบใช้ความร้อน | Magnetic Stirrer Hotplate', '61-11-080100-208-002', 'อุปกรณ์', 4, '2024-07-29 17:00:21', 0),
(4, '66a7687f1a50c.png', 'เครื่องเป่าแช่แข็ง | FREEZE DRYER', '190605002DZ12P054', 'วัสดุ', 29, '2024-07-29 17:01:35', 0),
(5, '66a7689f40f08.jpg', 'เครื่องหาค่าเพาะเชื้อของอาหาร', 'YS18M102236_DK', 'วัสดุ', 50, '2024-07-29 17:02:07', 0),
(6, '66a768bfbde72.jpg', 'เครื่องนึ่งฆ่าเชื้อความจุ 110 ลิตร | Autoclave', '31917031483', 'วัสดุ', 2, '2024-07-29 17:02:39', 0),
(8, '66a7693965423.jpg', 'เครื่องปรับแรงดันไฟฟ้า', 'UQT17050149', 'เครื่องมือ', 2, '2024-07-29 17:04:41', 0),
(9, '66a76961197ef.jpg', 'เครื่องวัดกรด-ด่าง | mV | ค่าการนำไฟฟ้า | TDS และอุณหูมิแบบตั้งโต๊ะ', '2604069', 'เครื่องมือ', 9, '2024-07-29 17:05:21', 0),
(10, '1036122678.jpg', 'เครื่องชั่งไฟฟ้าทศนิยม 4 ตำแหน่ง | 4-digit analytical balance', 'SWB35090474', 'เครื่องมือ', 2, '2024-07-29 17:04:07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `info_sciname`
--

CREATE TABLE `info_sciname` (
  `ID` int(20) NOT NULL,
  `sci_name` varchar(100) NOT NULL,
  `serial_number` varchar(20) NOT NULL,
  `installation_date` date DEFAULT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `company` varchar(30) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `contact_number` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `info_sciname`
--

INSERT INTO `info_sciname` (`ID`, `sci_name`, `serial_number`, `installation_date`, `last_maintenance_date`, `details`, `brand`, `model`, `company`, `contact`, `contact_number`) VALUES
(1, 'ตู้อบ', 'THLB519.0119', '2024-07-29', NULL, '--', 'MEMMERT', '--', 'บริษัท ไซแอนติฟิค โปรโมชั่น จำ', '--', '--'),
(2, 'เครื่องแตกเซลล์โดยใช้ความดันสูง | High Pressure Cell Disrupter', '1246', '2024-07-29', NULL, '--', 'Constant', 'BTH40/TS6/AA', '--', '--', '--'),
(3, 'เครื่องกวนสารละลายแบบใช้ความร้อน | Magnetic Stirrer Hotplate', '61-11-080100-208-002', '2024-07-29', NULL, '--', 'IKA,Germany', 'C-MAG-HS7', '--', '--', '--'),
(4, 'เครื่องเป่าแช่แข็ง | FREEZE DRYER', '190605002DZ12P054', '2024-07-29', NULL, 'แดงมาก', 'BIOBASE', 'BK-FD12P', 'BIOBASE BIODUSTRY(SHANDONG) CO', '--', '--'),
(5, 'เครื่องหาค่าเพาะเชื้อของอาหาร', 'YS18M102236_DK', '2024-07-29', NULL, '--', 'YSI', 'YSI 2900', 'บริษัท ไซแอนติฟิค โปรโมชั่น จำ', '--', '--'),
(6, 'เครื่องนึ่งฆ่าเชื้อความจุ 110 ลิตร | Autoclave', '31917031483', '2024-07-29', '2024-09-01', '--', 'HIRAYAMA', 'HVE-110', 'บริษัท ไซแอนติฟิก โปรโมชั่น จำ', '--', '--'),
(8, 'เครื่องปรับแรงดันไฟฟ้า', 'UQT17050149', '2024-07-29', NULL, '--', 'ENERTEK', 'SA-N-5000', 'บริษัท ไซแอนติฟิค โปรโมชั่น จำ', '--', '--'),
(9, 'เครื่องวัดกรด-ด่าง | mV | ค่าการนำไฟฟ้า | TDS และอุณหูมิแบบตั้งโต๊ะ', '2604069', '2024-07-29', '2024-09-01', '--', 'EUTECH', 'PC700', 'บริษัท ไซแอนติฟิค โปรโมชั่น จำ', '--', '--'),
(10, 'เครื่องชั่งไฟฟ้าทศนิยม 4 ตำแหน่ง | 4-digit analytical balance', 'SWB35090474', '2024-07-29', NULL, '--', 'Sartorius', 'BSA224S-CW', 'บริษัท ไซแอนติฟิค โปรโมชั่น จำ', '--', '--');

-- --------------------------------------------------------

--
-- Table structure for table `logs_maintenance`
--

CREATE TABLE `logs_maintenance` (
  `ID` int(20) NOT NULL,
  `serial_number` varchar(20) DEFAULT NULL,
  `sci_name` varchar(100) DEFAULT NULL,
  `categories` varchar(20) DEFAULT NULL,
  `start_maintenance` date DEFAULT NULL,
  `end_maintenance` date DEFAULT NULL,
  `name_staff` varchar(100) DEFAULT NULL,
  `note` varchar(200) DEFAULT NULL,
  `details_maintenance` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `logs_maintenance`
--

INSERT INTO `logs_maintenance` (`ID`, `serial_number`, `sci_name`, `categories`, `start_maintenance`, `end_maintenance`, `name_staff`, `note`, `details_maintenance`, `created_at`) VALUES
(1, '31917031483', 'เครื่องนึ่งฆ่าเชื้อความจุ 110 ลิตร | Autoclave', 'วัสดุ', '2024-09-03', '2024-09-01', '', '', '--', '2024-09-01 10:27:47'),
(2, '2604069', 'เครื่องวัดกรด-ด่าง | mV | ค่าการนำไฟฟ้า | TDS และอุณหูมิแบบตั้งโต๊ะ', 'เครื่องมือ', '2024-09-12', '2024-09-01', '', '', '--', '2024-09-01 10:27:52'),
(3, 'THLB519.0119', 'ตู้อบ', 'อุปกรณ์', '2024-09-02', '2024-09-06', '', '', NULL, '2024-09-01 10:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `logs_management`
--

CREATE TABLE `logs_management` (
  `ID` int(100) NOT NULL,
  `log_Name` varchar(200) NOT NULL,
  `log_Role` varchar(100) NOT NULL,
  `log_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `log_Status` set('Add','Edit','Delete') NOT NULL,
  `log_Content` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `logs_management`
--

INSERT INTO `logs_management` (`ID`, `log_Name`, `log_Role`, `log_Date`, `log_Status`, `log_Content`) VALUES
(1, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 09:58:24', 'Add', '{\"sci_name\":\"ตู้อบ\",\"serial_number\":\"THLB519.0119\"}'),
(2, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 09:59:48', 'Add', '{\"sci_name\":\"เครื่องแตกเซลล์โดยใช้ความดันสูง | High Pressure Cell Disrupter\",\"serial_number\":\"1246\"}'),
(3, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:00:21', 'Add', '{\"sci_name\":\"เครื่องกวนสารละลายแบบใช้ความร้อน | Magnetic Stirrer Hotplate\",\"serial_number\":\"61-11-080100-208-00259-0001\"}'),
(4, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:01:35', 'Add', '{\"sci_name\":\"เครื่องเป่าแช่แข็ง | FREEZE DRYER\",\"serial_number\":\"190605002DZ12P054\"}'),
(5, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:02:07', 'Add', '{\"sci_name\":\"เครื่องหาค่าเพาะเชื้อของอาหาร\",\"serial_number\":\"YS18M102236_DK\"}'),
(6, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:02:39', 'Add', '{\"sci_name\":\"เครื่องนึ่งฆ่าเชื้อความจุ 110 ลิตร | Autoclave\",\"serial_number\":\"31917031483\"}'),
(7, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:04:07', 'Add', '{\"sci_name\":\"เครื่องชั่งไฟฟ้าทศนิยม 4 ตำแหน่ง | 4-digit analytical balance\",\"serial_number\":\"SWB35090475\"}'),
(8, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:04:41', 'Add', '{\"sci_name\":\"เครื่องปรับแรงดันไฟฟ้า\",\"serial_number\":\"UQT17050149\"}'),
(9, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:05:21', 'Add', '{\"sci_name\":\"เครื่องวัดกรด-ด่าง,mV,ค่าการนำไฟฟ้า,TDS และอุณหูมิแบบตั้งโต๊ะ\",\"serial_number\":\"2604069\"}'),
(10, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:19:49', 'Edit', '{\"sci_name\":\"เครื่องวัดกรด-ด่าง | mV | ค่าการนำไฟฟ้า | TDS และอุณหูมิแบบตั้งโต๊ะ\",\"serial_number\":\"2604069\"}'),
(11, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-07-29 10:20:50', 'Edit', '{\"sci_name\":\"เครื่องวัดกรด-ด่าง | mV | ค่าการนำไฟฟ้า | TDS และอุณหูมิแบบตั้งโต๊ะ\",\"serial_number\":\"2604069\"}'),
(12, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-08-03 07:05:33', 'Edit', '{\"sci_name\":\"เครื่องเป่าแช่แข็ง | FREEZE DRYER\",\"serial_number\":\"190605002DZ12P054\"}'),
(13, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-08-03 07:05:45', 'Edit', '{\"sci_name\":\"เครื่องหาค่าเพาะเชื้อของอาหาร\",\"serial_number\":\"YS18M102236_DK\"}'),
(14, 'นายพิสิฐมม พุ่มกล่อม', 'อาจารย์', '2024-08-03 07:05:58', 'Edit', '{\"sci_name\":\"เครื่องวัดกรด-ด่าง | mV | ค่าการนำไฟฟ้า | TDS และอุณหูมิแบบตั้งโต๊ะ\",\"serial_number\":\"2604069\"}'),
(15, 'นายพิสิฐพงศ์ พุ่มกล่อม', 'อาจารย์', '2024-08-14 04:18:56', 'Delete', '{\"sci_name\":\"เครื่องชั่งไฟฟ้าทศนิยม 4 ตำแหน่ง | 4-digit analytical balance\",\"serial_number\":\"SWB35090475\"}'),
(16, 'นายพิสิฐพงศ์ พุ่มกล่อม', 'อาจารย์', '2024-08-30 08:49:18', 'Edit', '{\"sci_name\":\"ตู้อบ\",\"serial_number\":\"THLB519.0119\"}'),
(17, 'นายพิสิฐพงศ์ พุ่มกล่อม', 'อาจารย์', '2024-08-30 08:49:36', 'Edit', '{\"sci_name\":\"เครื่องชั่งไฟฟ้าทศนิยม 4 ตำแหน่ง | 4-digit analytical balance\",\"serial_number\":\"SWB35090474\"}'),
(18, 'นายพิสิฐพงศ์ พุ่มกล่อม', 'เจ้าหน้าที่', '2024-09-01 10:09:15', 'Edit', '{\"sci_name\":\"ตู้อบ\",\"serial_number\":\"THLB519.0119\"}'),
(19, 'นายพิสิฐพงศ์ พุ่มกล่อม', 'เจ้าหน้าที่', '2024-09-01 10:09:36', 'Edit', '{\"sci_name\":\"เครื่องกวนสารละลายแบบใช้ความร้อน | Magnetic Stirrer Hotplate\",\"serial_number\":\"61-11-080100-208-002\"}');

-- --------------------------------------------------------

--
-- Table structure for table `logs_user`
--

CREATE TABLE `logs_user` (
  `ID` int(100) NOT NULL,
  `authID` int(100) NOT NULL,
  `log_Name` varchar(255) NOT NULL,
  `log_Date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users_db`
--

CREATE TABLE `users_db` (
  `userID` int(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pre` set('นาย','นาง','นางสาว','ดร.','ผศ.ดร.','อ.') NOT NULL,
  `firstname` varchar(200) NOT NULL,
  `lastname` varchar(200) NOT NULL,
  `phone_number` varchar(10) NOT NULL,
  `email` varchar(200) NOT NULL,
  `role` set('อาจารย์','เจ้าหน้าที่','บุคลากร','') NOT NULL,
  `urole` set('staff','user') NOT NULL,
  `agency` varchar(100) NOT NULL,
  `status` set('w_approved','approved','n_approved') NOT NULL,
  `approved_by` varchar(200) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users_db`
--

INSERT INTO `users_db` (`userID`, `username`, `password`, `created_at`, `pre`, `firstname`, `lastname`, `phone_number`, `email`, `role`, `urole`, `agency`, `status`, `approved_by`, `approved_date`) VALUES
(29110, 'services', '$2y$10$LBzhTNqb/C.8rlfdTk33ouBYtIHbKAA/AdYdqkPRMW0BjkARqsTfi', '2024-09-01 10:26:26', 'นาย', 'พิสิฐพงศ์', 'พุ่มกล่อม', '0879031482', 'phisitphong007@gmail.com', 'เจ้าหน้าที่', 'staff', 'ศูนย์วิทยาศาสตร์', 'approved', 'นายพิสิฐพงศ์ พุ่มกล่อม', '2024-09-01 17:26:26'),
(59759, 'mcmas123', '$2y$10$XX84rtrPxpM.XCNfh8DrW.GF.r.XJFnuyGDWTFbIO9rTLpkCEAj2u', '2024-09-01 10:26:23', 'นาย', 'ภูวเดช', 'โอภาสธัญวุฒิ', '0945522865', 'Test001@gmail.com', 'อาจารย์', 'user', 'วิทย์', 'approved', 'นายพิสิฐพงศ์ พุ่มกล่อม', '2024-09-01 17:26:23'),
(86480, 'phisitphong2001', '$2y$10$BzpfHqYqvTP3BzYEh5pAXeNsX.qY4voJMiA8sgIlIj6W1Y.tqAgmm', '2024-09-01 10:27:04', 'ผศ.ดร.', 'พิสิฐพงศ์', 'พุ่มกล่อม', '0879031482', 'phisitphong123@gmail.com', 'อาจารย์', 'user', 'วิทย์', 'approved', 'นายพิสิฐพงศ์ พุ่มกล่อม', '2024-09-01 17:27:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approve_to_reserve`
--
ALTER TABLE `approve_to_reserve`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `crud`
--
ALTER TABLE `crud`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `info_sciname`
--
ALTER TABLE `info_sciname`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `logs_maintenance`
--
ALTER TABLE `logs_maintenance`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `logs_management`
--
ALTER TABLE `logs_management`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `logs_user`
--
ALTER TABLE `logs_user`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users_db`
--
ALTER TABLE `users_db`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approve_to_reserve`
--
ALTER TABLE `approve_to_reserve`
  MODIFY `ID` int(50) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crud`
--
ALTER TABLE `crud`
  MODIFY `ID` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `info_sciname`
--
ALTER TABLE `info_sciname`
  MODIFY `ID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `logs_maintenance`
--
ALTER TABLE `logs_maintenance`
  MODIFY `ID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `logs_management`
--
ALTER TABLE `logs_management`
  MODIFY `ID` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `logs_user`
--
ALTER TABLE `logs_user`
  MODIFY `ID` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_db`
--
ALTER TABLE `users_db`
  MODIFY `userID` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91706;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.2deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 17, 2026 at 12:42 AM
-- Server version: 8.4.8-0ubuntu0.25.10.1
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ewallet`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `bank_account_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_holder` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`bank_account_id`, `user_id`, `bank_name`, `account_number`, `account_holder`, `is_verified`, `is_default`, `created_at`) VALUES
('58e6a4da-5186-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Agribank', '0123456895', 'NGUYEN VAN E', 1, 1, '2026-05-17 07:21:28'),
('5e987484-5043-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Vietcombank', '1234567853', 'NGUYEN VAN E', 1, 1, '2026-05-15 16:49:30'),
('7c073d21-41d7-11f1-8cc2-106838369c1d', '85280f94-e880-42a9-840a-44d3a28ab92e', 'Vietcombank', '044688698665', 'NGUYEN VAN A', 1, 0, '2026-04-27 08:21:57'),
('99bf4611-4214-11f1-a680-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Vietcombank', '132568895566', 'NGUYEN VAN A', 1, 1, '2026-04-27 15:39:27'),
('eca0bc16-4fc2-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Vietcombank', '12323131237', 'NGUYEN VAN A', 1, 1, '2026-05-15 01:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `daily_limits_log`
--

CREATE TABLE `daily_limits_log` (
  `log_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `wallet_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_date` date NOT NULL,
  `total_spent` decimal(18,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_limits_log`
--

INSERT INTO `daily_limits_log` (`log_id`, `wallet_id`, `log_date`, `total_spent`) VALUES
('2442151c-4fc8-11f1-9d4d-106838369c1d', 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', '2026-05-14', 30000.00),
('4052b2a7-4fc9-11f1-9d4d-106838369c1d', 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', '2026-05-14', 50000.00),
('7cb9d4c8-5186-11f1-b77b-106838369c1d', '9714fd4f-b68d-4cb5-841d-4dfbfa3b2583', '2026-05-17', 525000.00),
('cee6db54-50ce-11f1-b59f-106838369c1d', '4512a740-8e3d-4859-bb4b-9957edeba4e0', '2026-05-16', 110000.00);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('transaction','security','system','promotion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `title`, `content`, `type`, `is_read`, `created_at`) VALUES
('00bfd9a9-4fc4-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605141837463259', 'transaction', 1, '2026-05-15 01:37:46'),
('0781b0c6-4fce-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605141949333829', 'transaction', 1, '2026-05-15 02:49:33'),
('0d48bf94-5127-11f1-a386-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 12:59 16/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-16 19:59:19'),
('16467311-5041-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đổi mật khẩu thành công', 'Mật khẩu tài khoản của bạn vừa được thay đổi lúc 09:33 15/05/2026. Nếu không phải bạn thực hiện, hãy liên hệ hỗ trợ ngay!', 'security', 1, '2026-05-15 16:33:10'),
('174dfc9c-5185-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:12 17/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-17 07:12:28'),
('195784e4-66e9-4e7b-b292-dc8496be060f', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Chào mừng đến E-Wallet!', 'Tài khoản đã được kích hoạt. Bắt đầu trải nghiệm ngay!', 'system', 1, '2026-05-15 01:22:53'),
('1ae0404d-50e3-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 04:52 16/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-16 11:52:56'),
('1c966987-49c4-11f1-a0dd-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605070323265303', 'transaction', 1, '2026-05-07 10:23:26'),
('238fd8f4-50d0-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160237102598', 'transaction', 1, '2026-05-16 09:37:10'),
('244379d7-4fc8-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605141907242750', 'transaction', 1, '2026-05-15 02:07:24'),
('26dd1a0e-4fc6-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605141853094315', 'transaction', 1, '2026-05-15 01:53:09'),
('2b0557f4-5041-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Rút tiền thành công', 'Bạn đã rút 50.000 ₫ về Vietcombank. Mã GD: 202605150933453624', 'transaction', 1, '2026-05-15 16:33:45'),
('364d1849-5043-11f1-9d92-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 09:48 15/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-15 16:48:22'),
('3969e98f-49c4-11f1-a0dd-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202605070324158033', 'transaction', 1, '2026-05-07 10:24:15'),
('3a8dd126-50e2-11f1-b59f-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 04:46 16/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-16 11:46:40'),
('3b08d434-5184-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 500.000 ₫. Mã GD: 202605170006194723', 'transaction', 1, '2026-05-17 07:06:19'),
('3b08f462-5184-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Hoàn tiền ưu đãi 5%', 'Bạn được hoàn 25.000 ₫ (5%) vào ví vì nạp từ 500.000 ₫!', 'promotion', 1, '2026-05-17 07:06:19'),
('3db38158-4210-11f1-a680-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Nạp tiền thành công', 'Ví của bạn đã được cộng 50.000 ₫. Mã GD: 202604270808142870', 'transaction', 0, '2026-04-27 15:08:14'),
('3f4f4c88-512d-11f1-a386-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Thanh toán thành công', 'Bạn đã nạp thành công 10.000 ₫ cho số điện thoại 0123456789. Mã GD: 202605161343408024', 'transaction', 1, '2026-05-16 20:43:40'),
('4052bb7b-4fc9-11f1-9d4d-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Chuyển tiền thành công', 'Bạn đã chuyển 50.000 ₫ đến Pham Van D. Mã GD: 202605141915217054', 'transaction', 0, '2026-05-15 02:15:21'),
('4052c693-4fc9-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Nhận tiền thành công', 'Bạn nhận được 50.000 ₫ từ Nguyễn Văn B. Lời nhắn: A. Mã GD: 202605141915217054', 'transaction', 1, '2026-05-15 02:15:21'),
('4182ec4e-5185-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:13 17/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-17 07:13:39'),
('428c84c3-50e3-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160454027942', 'transaction', 1, '2026-05-16 11:54:02'),
('4ad3f9b8-5184-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 500.000 ₫. Mã GD: 202605170006455867', 'transaction', 1, '2026-05-17 07:06:45'),
('4ad413c7-5184-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Hoàn tiền ưu đãi 5%', 'Bạn được hoàn 25.000 ₫ (5%) vào ví vì nạp từ 500.000 ₫!', 'promotion', 1, '2026-05-17 07:06:45'),
('4b0cc6de-5043-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 09:48 15/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-15 16:48:57'),
('4b4c398e-ac71-43ac-b70a-43b080f2e5e8', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Chào mừng đến E-Wallet!', 'Tài khoản đã được kích hoạt. Bắt đầu trải nghiệm ngay!', 'system', 1, '2026-05-17 07:13:19'),
('5446a1b2-5127-11f1-a386-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Thanh toán hóa đơn thành công', 'Bạn đã thanh toán Tiền điện (EVN Hà Nội) kỳ 04/2025 số tiền 350.000 ₫. Mã GD: 202605161301181581', 'transaction', 1, '2026-05-16 20:01:18'),
('5788e132-50e3-11f1-b59f-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 04:54 16/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-16 11:54:38'),
('58e7c586-5186-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Yêu cầu liên kết ngân hàng đang chờ duyệt', 'Tài khoản Agribank - 0123456895 đang chờ admin xác nhận. Bạn sẽ nhận thông báo khi được duyệt.', 'system', 1, '2026-05-17 07:21:28'),
('59383e57-5040-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605150927531001', 'transaction', 1, '2026-05-15 16:27:53'),
('5c6541b5-4fcd-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605141944465903', 'transaction', 1, '2026-05-15 02:44:46'),
('5c770544-50d2-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160253048096', 'transaction', 1, '2026-05-16 09:53:04'),
('5e995dda-5043-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Yêu cầu liên kết ngân hàng đang chờ duyệt', 'Tài khoản Vietcombank - 1234567853 đang chờ admin xác nhận. Bạn sẽ nhận thông báo khi được duyệt.', 'system', 1, '2026-05-15 16:49:30'),
('61c07f8d-50f8-11f1-b164-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 07:25 16/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-16 14:25:14'),
('64edbae0-5185-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Yêu cầu liên kết ngân hàng đang chờ duyệt', 'Tài khoản Vietcombank - 12345566355 đang chờ admin xác nhận. Bạn sẽ nhận thông báo khi được duyệt.', 'system', 1, '2026-05-17 07:14:39'),
('6d15a541-50ca-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 01:56 16/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-16 08:56:16'),
('6e1fb066-50e4-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 05:02 16/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-16 12:02:25'),
('6e4359b3-50f4-11f1-b164-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 06:56 16/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-16 13:56:57'),
('71a7d451-512d-11f1-a386-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202605161345044468', 'transaction', 1, '2026-05-16 20:45:04'),
('72e22ce0-5043-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Tài khoản ngân hàng đã được xác thực', 'Tài khoản Vietcombank - 1234567853 đã được xác thực thành công. Bạn có thể sử dụng để rút tiền và chuyển tiền.', 'system', 1, '2026-05-15 16:50:04'),
('75666d1f-50d1-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160246379962', 'transaction', 1, '2026-05-16 09:46:37'),
('77d57303-5185-11f1-b77b-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:15 17/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-17 07:15:10'),
('793711d2-50cf-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 50.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160232241244', 'transaction', 1, '2026-05-16 09:32:24'),
('7cba6956-5186-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Chuyển tiền thành công', 'Bạn đã chuyển 500.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605170022287664', 'transaction', 1, '2026-05-17 07:22:28'),
('857d077d-5185-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:15 17/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-17 07:15:33'),
('8af7a475-5185-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Nạp tiền thành công', 'Ví đã được cộng 500.000 ₫. Mã GD: 202605170015426711', 'transaction', 1, '2026-05-17 07:15:42'),
('8af8e983-5185-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Hoàn tiền ưu đãi 5%', 'Bạn được hoàn 25.000 ₫ (5%) vào ví vì nạp từ 500.000 ₫!', 'promotion', 1, '2026-05-17 07:15:42'),
('8ee4aba4-4fc6-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Rút tiền thành công', 'Bạn đã rút 50.000 ₫ về Vietcombank. Mã GD: 202605141856046747', 'transaction', 1, '2026-05-15 01:56:04'),
('9046c4ae-4fca-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605141924444150', 'transaction', 1, '2026-05-15 02:24:44'),
('9302be9d-4213-11f1-a680-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202604270832068622', 'transaction', 0, '2026-04-27 15:32:06'),
('9bf8443f-512d-11f1-a386-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Thanh toán thành công', 'Bạn đã nạp thành công 10.000 ₫ cho số điện thoại 0123456789. Mã GD: 202605161346152920', 'transaction', 1, '2026-05-16 20:46:15'),
('9eaddd14-50e4-11f1-b59f-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 05:03 16/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-16 12:03:47'),
('9f157bb9-4213-11f1-a680-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202604270832263451', 'transaction', 0, '2026-04-27 15:32:26'),
('a204a4b4-5186-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Chuyển tiền thành công', 'Bạn đã chuyển 25.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605170023314988', 'transaction', 1, '2026-05-17 07:23:31'),
('a4555f7d-5182-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 500.000 ₫. Mã GD: 202605162354568825', 'transaction', 1, '2026-05-17 06:54:56'),
('a455785e-5182-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Hoàn tiền ưu đãi 5%', 'Bạn được hoàn 25.000 ₫ (5%) vào ví vì nạp từ 500.000 ₫!', 'promotion', 1, '2026-05-17 06:54:56'),
('b035ad92-fb63-4be1-bf9a-bbb5fd0486af', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Chào mừng đến E-Wallet!', 'Tài khoản đã được kích hoạt. Bắt đầu trải nghiệm ngay!', 'system', 0, '2026-04-27 14:51:17'),
('b08de767-5e6f-4039-a39e-764edb288730', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chào mừng đến E-Wallet!', 'Tài khoản đã được kích hoạt. Bắt đầu trải nghiệm ngay!', 'system', 1, '2026-05-07 10:22:54'),
('b8bb2dde-4210-11f1-a680-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Nạp tiền thành công', 'Ví của bạn đã được cộng 100.000 ₫. Mã GD: 202604270811418782', 'transaction', 0, '2026-04-27 15:11:41'),
('baff1957-4214-11f1-a680-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Yêu cầu rút tiền đang xử lý', 'Yêu cầu rút 2.000.000 ₫ về Vietcombank đang được xử lý. Mã GD: 202604270840227489', 'transaction', 0, '2026-04-27 15:40:22'),
('bbc4a389-512d-11f1-a386-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Rút tiền thành công', 'Bạn đã rút 3.000.000 ₫ về Vietcombank. Mã GD: 202605161347091344', 'transaction', 1, '2026-05-16 20:47:09'),
('c959cad3-229a-48af-9041-379beb3dad3c', '85280f94-e880-42a9-840a-44d3a28ab92e', 'Chào mừng đến E-Wallet!', 'Tài khoản đã được kích hoạt. Bắt đầu trải nghiệm ngay!', 'system', 1, '2026-04-27 08:15:43'),
('ceb7ac94-49c5-11f1-a0dd-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202605070335354225', 'transaction', 1, '2026-05-07 10:35:35'),
('cee816d6-50ce-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160227384806', 'transaction', 1, '2026-05-16 09:27:38'),
('d2d86c04-50cf-11f1-b59f-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến NGUYEN VAN A - Vietcombank. Mã GD: 202605160234558848', 'transaction', 1, '2026-05-16 09:34:55'),
('d3a929a1-49c5-11f1-a0dd-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202605070335439542', 'transaction', 1, '2026-05-07 10:35:43'),
('e00f477c-5186-11f1-b77b-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:25 17/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-17 07:25:15'),
('e0494fb9-5040-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 50.000 ₫. Mã GD: 202605150931395289', 'transaction', 1, '2026-05-15 16:31:39'),
('e3eb06dd-49c5-11f1-a0dd-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Yêu cầu rút tiền đang xử lý', 'Yêu cầu rút 5.000.000 ₫ về Vietcombank đang được xử lý. Mã GD: 202605070336106067', 'transaction', 1, '2026-05-07 10:36:10'),
('e68a5d17-5186-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Tài khoản ngân hàng đã được xác thực', 'Tài khoản Agribank - 0123456895 đã được xác thực thành công. Bạn có thể sử dụng để rút tiền và chuyển tiền.', 'system', 1, '2026-05-17 07:25:26'),
('e7019140-5180-11f1-b77b-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 23:42 16/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-17 06:42:29'),
('ea455c6e-4fc8-11f1-9d4d-106838369c1d', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Chuyển tiền thành công', 'Bạn đã chuyển 10.000 ₫ đến Nguyễn Văn B. Mã GD: 202605141912569214', 'transaction', 1, '2026-05-15 02:12:56'),
('ea456935-4fc8-11f1-9d4d-106838369c1d', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Nhận tiền thành công', 'Bạn nhận được 10.000 ₫ từ Pham Van D. Lời nhắn: B. Mã GD: 202605141912569214', 'transaction', 0, '2026-05-15 02:12:56'),
('eb8c68b0-49c5-11f1-a0dd-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202605070336237306', 'transaction', 1, '2026-05-07 10:36:23'),
('ed39c368-5186-11f1-b77b-106838369c1d', 'af65f033-123c-4084-b505-d57c15b1ddfa', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:25 17/05/2026 từ IP: 127.0.0.1', 'security', 1, '2026-05-17 07:25:37'),
('f12c4f30-512d-11f1-a386-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 13:48 16/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-16 20:48:38'),
('f563d923-5043-11f1-9d92-106838369c1d', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nạp tiền thành công', 'Ví đã được cộng 2.000.000 ₫. Mã GD: 202605150953431560', 'transaction', 1, '2026-05-15 16:53:43'),
('fb5e6576-5045-11f1-9d92-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 10:08 15/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-15 17:08:12'),
('ff5936eb-5184-11f1-b77b-106838369c1d', '2737c436-4a23-11f1-a1d0-106838369c1d', 'Đăng nhập thành công', 'Tài khoản vừa đăng nhập lúc 00:11 17/05/2026 từ IP: 127.0.0.1', 'security', 0, '2026-05-17 07:11:48');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `otp_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('register','login','transaction','change_password') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `expired_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`otp_id`, `user_id`, `code`, `type`, `is_used`, `expired_at`, `created_at`) VALUES
('202604270819562808', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '538959', 'transaction', 0, '2026-04-27 08:24:56', '2026-04-27 15:19:56'),
('202604270820112583', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '675113', 'transaction', 0, '2026-04-27 08:25:11', '2026-04-27 15:20:11'),
('202604270820305793', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '925124', 'transaction', 0, '2026-04-27 08:25:30', '2026-04-27 15:20:30'),
('202604270820596169', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '715650', 'transaction', 0, '2026-04-27 08:25:59', '2026-04-27 15:20:59'),
('202604270822218576', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '157733', 'transaction', 0, '2026-04-27 08:27:21', '2026-04-27 15:22:21'),
('202604270824353655', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '179841', 'transaction', 0, '2026-04-27 08:29:35', '2026-04-27 15:24:35'),
('202604270827164694', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '412400', 'transaction', 0, '2026-04-27 08:32:16', '2026-04-27 15:27:16'),
('202604270830142262', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '522638', 'transaction', 0, '2026-04-27 08:35:14', '2026-04-27 15:30:14'),
('202604270832013566', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '705125', 'transaction', 1, '2026-04-27 08:37:01', '2026-04-27 15:32:01'),
('202604270832201619', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '896534', 'transaction', 1, '2026-04-27 08:37:20', '2026-04-27 15:32:20'),
('202605070323193253', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '169779', 'transaction', 1, '2026-05-07 03:28:19', '2026-05-07 10:23:19'),
('202605070324091454', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '466785', 'transaction', 1, '2026-05-07 03:29:09', '2026-05-07 10:24:09'),
('202605070335285775', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '664123', 'transaction', 1, '2026-05-07 03:40:28', '2026-05-07 10:35:28'),
('202605070335395252', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '658066', 'transaction', 1, '2026-05-07 03:40:39', '2026-05-07 10:35:39'),
('202605070336183242', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '443228', 'transaction', 1, '2026-05-07 03:41:18', '2026-05-07 10:36:18'),
('202605141837408242', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '492803', 'transaction', 1, '2026-05-14 18:42:40', '2026-05-15 01:37:40'),
('202605141853028200', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '140829', 'transaction', 1, '2026-05-14 18:58:02', '2026-05-15 01:53:02'),
('202605141855536995', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '218275', 'transaction', 1, '2026-05-14 19:00:53', '2026-05-15 01:55:53'),
('202605141907177694', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '591577', 'transaction', 1, '2026-05-14 19:12:17', '2026-05-15 02:07:17'),
('202605141912506295', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '779774', 'transaction', 1, '2026-05-14 19:17:50', '2026-05-15 02:12:50'),
('202605141915166504', '47e72fad-7eab-4dcf-9706-342bfa196e6b', '840758', 'transaction', 1, '2026-05-14 19:20:16', '2026-05-15 02:15:16'),
('202605141924411722', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '008769', 'transaction', 1, '2026-05-14 19:29:41', '2026-05-15 02:24:41'),
('202605141944429062', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '545043', 'transaction', 1, '2026-05-14 19:49:42', '2026-05-15 02:44:42'),
('202605141949299018', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', '771799', 'transaction', 1, '2026-05-14 19:54:29', '2026-05-15 02:49:29'),
('202605150925118254', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '399344', 'transaction', 0, '2026-05-15 09:30:11', '2026-05-15 16:25:11'),
('202605150925326900', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '623993', 'transaction', 0, '2026-05-15 09:30:32', '2026-05-15 16:25:32'),
('202605150927487861', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '182317', 'transaction', 1, '2026-05-15 09:32:48', '2026-05-15 16:27:48'),
('202605150931073267', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '183994', 'transaction', 0, '2026-05-15 09:36:07', '2026-05-15 16:31:07'),
('202605150931296079', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '453806', 'transaction', 1, '2026-05-15 09:36:29', '2026-05-15 16:31:29'),
('202605150933381292', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '191004', 'transaction', 1, '2026-05-15 09:38:38', '2026-05-15 16:33:38'),
('202605150953386607', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '461649', 'transaction', 1, '2026-05-15 09:58:38', '2026-05-15 16:53:38'),
('202605160224173369', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '042146', 'transaction', 0, '2026-05-16 02:29:17', '2026-05-16 09:24:17'),
('202605160224527162', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '348968', 'transaction', 0, '2026-05-16 02:29:52', '2026-05-16 09:24:52'),
('202605160227346938', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '532186', 'transaction', 1, '2026-05-16 02:32:34', '2026-05-16 09:27:34'),
('202605160232211484', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '331353', 'transaction', 1, '2026-05-16 02:37:21', '2026-05-16 09:32:21'),
('202605160234508414', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '634294', 'transaction', 1, '2026-05-16 02:39:50', '2026-05-16 09:34:50'),
('202605160237071742', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '959014', 'transaction', 1, '2026-05-16 02:42:07', '2026-05-16 09:37:07'),
('202605160246339911', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '180808', 'transaction', 1, '2026-05-16 02:51:33', '2026-05-16 09:46:33'),
('202605160252567460', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '683980', 'transaction', 1, '2026-05-16 02:57:56', '2026-05-16 09:52:56'),
('202605160453589345', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '660794', 'transaction', 1, '2026-05-16 04:58:58', '2026-05-16 11:53:58'),
('202605161301125913', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '000006', 'transaction', 1, '2026-05-16 13:06:12', '2026-05-16 20:01:12'),
('202605161331088697', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '078307', 'transaction', 0, '2026-05-16 13:36:08', '2026-05-16 20:31:08'),
('202605161332011520', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '891673', 'transaction', 0, '2026-05-16 13:37:01', '2026-05-16 20:32:01'),
('202605161334348691', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '570258', 'transaction', 0, '2026-05-16 13:39:34', '2026-05-16 20:34:34'),
('202605161339137608', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '520603', 'transaction', 0, '2026-05-16 13:44:13', '2026-05-16 20:39:13'),
('202605161343376194', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '375512', 'transaction', 1, '2026-05-16 13:48:37', '2026-05-16 20:43:37'),
('202605161345002248', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '178195', 'transaction', 1, '2026-05-16 13:50:00', '2026-05-16 20:45:00'),
('202605161346124928', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '604883', 'transaction', 1, '2026-05-16 13:51:12', '2026-05-16 20:46:12'),
('202605161347041172', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '806646', 'transaction', 1, '2026-05-16 13:52:04', '2026-05-16 20:47:04'),
('202605161348043912', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '479162', 'transaction', 0, '2026-05-16 13:53:04', '2026-05-16 20:48:04'),
('202605162352565284', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '648919', 'transaction', 0, '2026-05-16 23:57:56', '2026-05-17 06:52:56'),
('202605162353153731', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '489565', 'transaction', 0, '2026-05-16 23:58:15', '2026-05-17 06:53:15'),
('202605162354524144', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '867573', 'transaction', 1, '2026-05-16 23:59:52', '2026-05-17 06:54:52'),
('202605170006156850', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '250642', 'transaction', 1, '2026-05-17 00:11:15', '2026-05-17 07:06:15'),
('202605170006404574', '1f781ac6-abdd-4952-aab4-f95f6d021f84', '092020', 'transaction', 1, '2026-05-17 00:11:40', '2026-05-17 07:06:40'),
('202605170015393070', 'af65f033-123c-4084-b505-d57c15b1ddfa', '845572', 'transaction', 1, '2026-05-17 00:20:39', '2026-05-17 07:15:39'),
('202605170022206460', 'af65f033-123c-4084-b505-d57c15b1ddfa', '386457', 'transaction', 1, '2026-05-17 00:27:20', '2026-05-17 07:22:20'),
('202605170023273748', 'af65f033-123c-4084-b505-d57c15b1ddfa', '242910', 'transaction', 1, '2026-05-17 00:28:27', '2026-05-17 07:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `service_bills`
--

CREATE TABLE `service_bills` (
  `bill_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `customer_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` enum('electricity','water','internet','phone_topup') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_period` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `status` enum('unpaid','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  `transaction_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_bills`
--

INSERT INTO `service_bills` (`bill_id`, `customer_code`, `customer_name`, `service_type`, `provider_name`, `bill_period`, `amount`, `status`, `paid_at`, `transaction_id`, `created_at`) VALUES
('92e57de7-50f7-11f1-b164-106838369c1d', 'KH001', 'Nguyễn Văn A', 'electricity', 'EVN Hà Nội', '05/2026', 350000.00, 'paid', '2026-05-16 20:01:18', '202605161301181581', '2026-05-16 14:19:27'),
('92e5808b-50f7-11f1-b164-106838369c1d', 'KH001', 'Nguyễn Văn A', 'water', 'Cấp nước Hà Nội', '05/2026', 120000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e581a5-50f7-11f1-b164-106838369c1d', 'KH001', 'Nguyễn Văn A', 'internet', 'Viettel', '05/2026', 200000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e5824f-50f7-11f1-b164-106838369c1d', 'KH001', 'Nguyễn Văn A', 'electricity', 'EVN Hà Nội', '03/2025', 310000.00, 'paid', NULL, NULL, '2026-05-16 14:19:27'),
('92e5832d-50f7-11f1-b164-106838369c1d', 'KH001', 'Nguyễn Văn A', 'water', 'Cấp nước Hà Nội', '03/2025', 105000.00, 'paid', NULL, NULL, '2026-05-16 14:19:27'),
('92e6d94f-50f7-11f1-b164-106838369c1d', 'KH002', 'Trần Thị B', 'electricity', 'EVN TP.HCM', '05/2026', 420000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e6dbb6-50f7-11f1-b164-106838369c1d', 'KH002', 'Trần Thị B', 'water', 'Sawaco', '05/2026', 95000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e6dc8b-50f7-11f1-b164-106838369c1d', 'KH002', 'Trần Thị B', 'internet', 'FPT Telecom', '05/2026', 180000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e6dd39-50f7-11f1-b164-106838369c1d', 'KH002', 'Trần Thị B', 'electricity', 'EVN TP.HCM', '03/2025', 390000.00, 'paid', NULL, NULL, '2026-05-16 14:19:27'),
('92e6ddd0-50f7-11f1-b164-106838369c1d', 'KH002', 'Trần Thị B', 'water', 'Sawaco', '03/2025', 88000.00, 'paid', NULL, NULL, '2026-05-16 14:19:27'),
('92e8413e-50f7-11f1-b164-106838369c1d', 'KH003', 'Lê Văn C', 'electricity', 'EVN Miền Trung', '05/2026', 280000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e84571-50f7-11f1-b164-106838369c1d', 'KH003', 'Lê Văn C', 'water', 'Cấp nước Đà Nẵng', '05/2026', 75000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e84669-50f7-11f1-b164-106838369c1d', 'KH003', 'Lê Văn C', 'internet', 'VNPT', '05/2026', 165000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e84713-50f7-11f1-b164-106838369c1d', 'KH003', 'Lê Văn C', 'phone_topup', 'Viettel', '05/2026', 50000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e9abce-50f7-11f1-b164-106838369c1d', 'KH004', 'Phạm Thị D', 'electricity', 'EVN Hà Nội', '05/2026', 510000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e9ae1a-50f7-11f1-b164-106838369c1d', 'KH004', 'Phạm Thị D', 'water', 'Cấp nước Hà Nội', '05/2026', 145000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e9aee4-50f7-11f1-b164-106838369c1d', 'KH004', 'Phạm Thị D', 'internet', 'CMC Telecom', '05/2026', 220000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92e9af87-50f7-11f1-b164-106838369c1d', 'KH004', 'Phạm Thị D', 'phone_topup', 'Mobifone', '05/2026', 100000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92eaeefe-50f7-11f1-b164-106838369c1d', 'KH005', 'Hoàng Văn E', 'electricity', 'EVN TP.HCM', '05/2026', 375000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92eaf12f-50f7-11f1-b164-106838369c1d', 'KH005', 'Hoàng Văn E', 'water', 'Sawaco', '05/2026', 110000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27'),
('92eaf1f3-50f7-11f1-b164-106838369c1d', 'KH005', 'Hoàng Văn E', 'internet', 'Viettel', '05/2026', 199000.00, 'unpaid', NULL, NULL, '2026-05-16 14:19:27');

-- --------------------------------------------------------

--
-- Table structure for table `service_payments`
--

CREATE TABLE `service_payments` (
  `service_payment_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `transaction_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_type` enum('electricity','water','internet','phone_topup','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bill_period` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `sender_wallet_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_wallet_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL,
  `fee` decimal(18,2) NOT NULL DEFAULT '0.00',
  `type` enum('deposit','withdraw','transfer','payment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','success','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `sender_wallet_id`, `receiver_wallet_id`, `amount`, `fee`, `type`, `status`, `message`, `bank_info`, `reference_code`, `created_at`, `updated_at`) VALUES
('202604270116552872', NULL, '1e6e12ff-d607-43db-a7c3-e8160c1e7079', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270116552872', '2026-04-27 08:16:55', '2026-04-27 08:16:55'),
('202604270118221287', NULL, '1e6e12ff-d607-43db-a7c3-e8160c1e7079', 100000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270118221287', '2026-04-27 08:18:22', '2026-04-27 08:18:22'),
('202604270734096398', NULL, '1e6e12ff-d607-43db-a7c3-e8160c1e7079', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270734096398', '2026-04-27 14:34:09', '2026-04-27 14:34:09'),
('202604270751493976', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270751493976', '2026-04-27 14:51:49', '2026-04-27 14:51:49'),
('202604270753024257', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270753024257', '2026-04-27 14:53:02', '2026-04-27 14:53:02'),
('202604270753299406', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270753299406', '2026-04-27 14:53:29', '2026-04-27 14:53:29'),
('202604270756189790', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270756189790', '2026-04-27 14:56:18', '2026-04-27 14:56:18'),
('202604270756356667', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'pending', 'Nap tien vi dien tu', NULL, '202604270756356667', '2026-04-27 14:56:35', '2026-04-27 14:56:35'),
('202604270808142870', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202604270808142870', '2026-04-27 15:08:14', '2026-04-27 15:08:14'),
('202604270811418782', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 100000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202604270811418782', '2026-04-27 15:11:41', '2026-04-27 15:11:41'),
('202604270832068622', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202604270832068622', '2026-04-27 15:32:06', '2026-04-27 15:32:06'),
('202604270832263451', NULL, 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202604270832263451', '2026-04-27 15:32:26', '2026-04-27 15:32:26'),
('202604270840227489', 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', NULL, 2000000.00, 0.00, 'withdraw', 'pending', 'Rút về Vietcombank - 132568895566', NULL, '202604270840227489', '2026-04-27 15:40:22', '2026-04-27 15:40:22'),
('202605070323265303', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605070323265303', '2026-05-07 10:23:26', '2026-05-07 10:23:26'),
('202605070324158033', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605070324158033', '2026-05-07 10:24:15', '2026-05-07 10:24:15'),
('202605070335354225', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605070335354225', '2026-05-07 10:35:35', '2026-05-07 10:35:35'),
('202605070335439542', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605070335439542', '2026-05-07 10:35:43', '2026-05-07 10:35:43'),
('202605070336106067', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 5000000.00, 0.00, 'withdraw', 'pending', 'Rút về Vietcombank - 12345566355', NULL, '202605070336106067', '2026-05-07 10:36:10', '2026-05-07 10:36:10'),
('202605070336237306', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605070336237306', '2026-05-07 10:36:23', '2026-05-07 10:36:23'),
('202605141837463259', NULL, 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', 50000.00, 0.00, 'deposit', 'success', 'Chao ban', NULL, '202605141837463259', '2026-05-15 01:37:46', '2026-05-15 01:37:46'),
('202605141853094315', NULL, 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605141853094315', '2026-05-15 01:53:09', '2026-05-15 01:53:09'),
('202605141856046747', 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', NULL, 50000.00, 0.00, 'withdraw', 'success', 'Rút về Vietcombank - 12323131237', NULL, '202605141856046747', '2026-05-15 01:56:04', '2026-05-15 01:56:04'),
('202605141907242750', 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', NULL, 10000.00, 0.00, 'transfer', 'success', 'Chuyển đến NGUYEN VAN A - Vietcombank (0123456789) | A', NULL, '202605141907242750', '2026-05-15 02:07:24', '2026-05-15 02:07:24'),
('202605141912569214', 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 10000.00, 0.00, 'transfer', 'success', 'B', NULL, '202605141912569214', '2026-05-15 02:12:56', '2026-05-15 02:12:56'),
('202605141915217054', 'ceca9d29-a415-4f55-9a8d-0afa0af877f8', 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', 50000.00, 0.00, 'transfer', 'success', 'A', NULL, '202605141915217054', '2026-05-15 02:15:21', '2026-05-15 02:15:21'),
('202605141924444150', 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', NULL, 10000.00, 0.00, 'transfer', 'success', 'Chuyển đến NGUYEN VAN A - Vietcombank (12323131237) | D', NULL, '202605141924444150', '2026-05-15 02:24:44', '2026-05-15 02:24:44'),
('202605141944465903', NULL, 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605141944465903', '2026-05-15 02:44:46', '2026-05-15 02:44:46'),
('202605141949333829', NULL, 'b9d83b3a-34ec-4794-aec9-1f330caefd4e', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605141949333829', '2026-05-15 02:49:33', '2026-05-15 02:49:33'),
('202605150927531001', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605150927531001', '2026-05-15 16:27:53', '2026-05-15 16:27:53'),
('202605150931395289', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 50000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605150931395289', '2026-05-15 16:31:39', '2026-05-15 16:31:39'),
('202605150933453624', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 50000.00, 0.00, 'withdraw', 'success', 'Rút về Vietcombank - 12345566355', NULL, '202605150933453624', '2026-05-15 16:33:45', '2026-05-15 16:33:45'),
('202605150953431560', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605150953431560', '2026-05-15 16:53:43', '2026-05-15 16:53:43'),
('202605160227384806', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'transfer', 'success', 'Chuyển đến NGUYEN VAN A - Vietcombank (12323131237) | C', NULL, '202605160227384806', '2026-05-16 09:27:38', '2026-05-16 09:27:38'),
('202605160232241244', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 50000.00, 0.00, 'transfer', 'success', 'Chuyển đến NGUYEN VAN A - Vietcombank (12323131237) | A', NULL, '202605160232241244', '2026-05-16 09:32:24', '2026-05-16 09:32:24'),
('202605160234558848', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'transfer', 'success', 'Chuyển đến NGUYEN VAN A - Vietcombank (12323131237) | a', NULL, '202605160234558848', '2026-05-16 09:34:55', '2026-05-16 09:34:55'),
('202605160237102598', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'transfer', 'success', 'Chuyển đến NGUYEN VAN A - Vietcombank (12323131237) | a', NULL, '202605160237102598', '2026-05-16 09:37:10', '2026-05-16 09:37:10'),
('202605160246379962', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'transfer', 'success', 'A', 'NGUYEN VAN A - Vietcombank (12323131237)', '202605160246379962', '2026-05-16 09:46:37', '2026-05-16 09:46:37'),
('202605160253048096', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'transfer', 'success', 'B', 'NGUYEN VAN A - Vietcombank (12323131237)', '202605160253048096', '2026-05-16 09:53:04', '2026-05-16 09:53:04'),
('202605160454027942', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'transfer', 'success', 'C', 'NGUYEN VAN A - Vietcombank (044688698665)', '202605160454027942', '2026-05-16 11:54:02', '2026-05-16 11:54:02'),
('202605161301181581', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 350000.00, 0.00, 'payment', 'success', 'Thanh toán Tiền điện - EVN Hà Nội kỳ 04/2025', NULL, '202605161301181581', '2026-05-16 20:01:18', '2026-05-16 20:01:18'),
('202605161343408024', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'payment', 'success', 'Nạp tiền điện thoại cho số 0123456789', NULL, '202605161343408024', '2026-05-16 20:43:40', '2026-05-16 20:43:40'),
('202605161345044468', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 2000000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605161345044468', '2026-05-16 20:45:04', '2026-05-16 20:45:04'),
('202605161346152920', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 10000.00, 0.00, 'payment', 'success', 'Nạp tiền điện thoại cho số 0123456789', NULL, '202605161346152920', '2026-05-16 20:46:15', '2026-05-16 20:46:15'),
('202605161347091344', '4512a740-8e3d-4859-bb4b-9957edeba4e0', NULL, 3000000.00, 0.00, 'withdraw', 'success', 'Rút về Vietcombank - 1234567853', NULL, '202605161347091344', '2026-05-16 20:47:09', '2026-05-16 20:47:09'),
('202605162354565578', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 25000.00, 0.00, 'deposit', 'success', 'Hoàn tiền 5% ưu đãi tháng này', NULL, '202605162354565578', '2026-05-17 06:54:56', '2026-05-17 06:54:56'),
('202605162354568825', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 500000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605162354568825', '2026-05-17 06:54:56', '2026-05-17 06:54:56'),
('202605170006192184', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 25000.00, 0.00, 'deposit', 'success', 'Hoàn tiền 5% ưu đãi tháng này', NULL, '202605170006192184', '2026-05-17 07:06:19', '2026-05-17 07:06:19'),
('202605170006194723', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 500000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605170006194723', '2026-05-17 07:06:19', '2026-05-17 07:06:19'),
('202605170006452305', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 25000.00, 0.00, 'deposit', 'success', 'Hoàn tiền 5% ưu đãi tháng này', NULL, '202605170006452305', '2026-05-17 07:06:45', '2026-05-17 07:06:45'),
('202605170006455867', NULL, '4512a740-8e3d-4859-bb4b-9957edeba4e0', 500000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605170006455867', '2026-05-17 07:06:45', '2026-05-17 07:06:45'),
('202605170015425515', NULL, '9714fd4f-b68d-4cb5-841d-4dfbfa3b2583', 25000.00, 0.00, 'deposit', 'success', 'Hoàn tiền 5% ưu đãi tháng này', NULL, '202605170015425515', '2026-05-17 07:15:42', '2026-05-17 07:15:42'),
('202605170015426711', NULL, '9714fd4f-b68d-4cb5-841d-4dfbfa3b2583', 500000.00, 0.00, 'deposit', 'success', 'Nap tien vi dien tu', NULL, '202605170015426711', '2026-05-17 07:15:42', '2026-05-17 07:15:42'),
('202605170022287664', '9714fd4f-b68d-4cb5-841d-4dfbfa3b2583', NULL, 500000.00, 0.00, 'transfer', 'success', 'a', 'NGUYEN VAN A - Vietcombank (044688698665)', '202605170022287664', '2026-05-17 07:22:28', '2026-05-17 07:22:28'),
('202605170023314988', '9714fd4f-b68d-4cb5-841d-4dfbfa3b2583', NULL, 25000.00, 0.00, 'transfer', 'success', 'a', 'NGUYEN VAN A - Vietcombank (044688698665)', '202605170023314988', '2026-05-17 07:23:31', '2026-05-17 07:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','locked','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `phone_number`, `password_hash`, `avatar_url`, `status`, `role`, `created_at`, `updated_at`) VALUES
('1f781ac6-abdd-4952-aab4-f95f6d021f84', 'Nguyễn Văn C', 'C@gmail.com', '0123456788', '$2y$12$d4nxx3qqmv.NMM22vbDOF.ok/Z1hVspzI2SCy1ubC8Kbl1ENYvtwm', NULL, 'active', 'user', '2026-05-07 10:22:53', '2026-05-15 16:33:10'),
('2737c436-4a23-11f1-a1d0-106838369c1d', 'Quản Trị Viên', 'admin@ewallet.com', '0900000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'active', 'admin', '2026-05-07 21:43:46', '2026-05-07 21:43:46'),
('47e72fad-7eab-4dcf-9706-342bfa196e6b', 'Nguyễn Văn B', 'B@gmail.com', '0123456789', '$2y$12$axMFdG4y6eq1EMoBcqbnqu.07J3uFh6LrM5sBi/Va9g8367m4ItQK', NULL, 'active', 'user', '2026-04-27 14:51:17', '2026-04-27 14:51:17'),
('85280f94-e880-42a9-840a-44d3a28ab92e', 'Nguyễn Văn A', 'A@gmail.com', '0987654321', '$2y$12$Z3nuP/vO0rYnNIy3DQYle.ljd.IVmaKSJPcUYIJdniDJjar5.TVvi', NULL, 'active', 'user', '2026-04-27 08:15:43', '2026-04-27 08:15:43'),
('a6a75af7-d2e5-486c-9bc7-20cd652d1587', 'Pham Van D', 'D@gmail.com', '01233456856', '$2y$12$wxJsZKcbLEtRdGgJzUTlxOnEswxxqu5YwiX3ffn31TEL5YqTGofga', NULL, 'active', 'user', '2026-05-15 01:22:53', '2026-05-15 02:53:50'),
('af65f033-123c-4084-b505-d57c15b1ddfa', 'Nguyen Van E', 'e@gmail.com', '0123456895', '$2y$12$0Z388nOkTWFNdXG3y7ue.epCIEAZINZqpQE4qibSr5opHKSFZokmG', NULL, 'active', 'user', '2026-05-17 07:13:19', '2026-05-17 07:13:19');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `wallet_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT (uuid()),
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `daily_limit` decimal(18,2) NOT NULL DEFAULT '50000000.00',
  `pin_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','locked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`wallet_id`, `user_id`, `balance`, `currency`, `daily_limit`, `pin_hash`, `status`, `created_at`, `updated_at`) VALUES
('1e6e12ff-d607-43db-a7c3-e8160c1e7079', '85280f94-e880-42a9-840a-44d3a28ab92e', 0.00, 'VND', 50000000.00, NULL, 'active', '2026-04-27 08:15:43', '2026-04-27 08:15:43'),
('4512a740-8e3d-4859-bb4b-9957edeba4e0', '1f781ac6-abdd-4952-aab4-f95f6d021f84', 5195000.00, 'VND', 50000000.00, '$2y$12$Pax58SHF/erSZkqcTI1iUeH59SJuY5VlVCNMOx4CYZ3SCqfa4Gu.2', 'active', '2026-05-07 10:22:54', '2026-05-17 07:06:45'),
('9714fd4f-b68d-4cb5-841d-4dfbfa3b2583', 'af65f033-123c-4084-b505-d57c15b1ddfa', 0.00, 'VND', 50000000.00, NULL, 'active', '2026-05-17 07:13:19', '2026-05-17 07:23:31'),
('b9d83b3a-34ec-4794-aec9-1f330caefd4e', 'a6a75af7-d2e5-486c-9bc7-20cd652d1587', 170000.00, 'VND', 50000000.00, NULL, 'active', '2026-05-15 01:22:53', '2026-05-15 02:49:33'),
('ceca9d29-a415-4f55-9a8d-0afa0af877f8', '47e72fad-7eab-4dcf-9706-342bfa196e6b', 160000.00, 'VND', 50000000.00, '$2y$12$Djlq.gkG6FjD9S21txio8ex0ybvETuntahOLjXTvtWc7PY/1WdZD2', 'active', '2026-04-27 14:51:17', '2026-05-15 02:15:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`bank_account_id`),
  ADD UNIQUE KEY `uq_bank_account` (`user_id`,`account_number`);

--
-- Indexes for table `daily_limits_log`
--
ALTER TABLE `daily_limits_log`
  ADD PRIMARY KEY (`log_id`),
  ADD UNIQUE KEY `uq_limit_date` (`wallet_id`,`log_date`),
  ADD KEY `idx_daily_limits_wallet` (`wallet_id`,`log_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_notifications_user` (`user_id`,`is_read`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `idx_otps_user_type` (`user_id`,`type`,`is_used`);

--
-- Indexes for table `service_bills`
--
ALTER TABLE `service_bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `idx_customer_code` (`customer_code`),
  ADD KEY `idx_service_type` (`service_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `service_payments`
--
ALTER TABLE `service_payments`
  ADD PRIMARY KEY (`service_payment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `reference_code` (`reference_code`),
  ADD KEY `idx_transactions_sender` (`sender_wallet_id`),
  ADD KEY `idx_transactions_receiver` (`receiver_wallet_id`),
  ADD KEY `idx_transactions_status` (`status`),
  ADD KEY `idx_transactions_date` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`wallet_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `fk_bank_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_limits_log`
--
ALTER TABLE `daily_limits_log`
  ADD CONSTRAINT `fk_limit_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`wallet_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `otps`
--
ALTER TABLE `otps`
  ADD CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_payments`
--
ALTER TABLE `service_payments`
  ADD CONSTRAINT `fk_sp_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_receiver` FOREIGN KEY (`receiver_wallet_id`) REFERENCES `wallets` (`wallet_id`),
  ADD CONSTRAINT `fk_tx_sender` FOREIGN KEY (`sender_wallet_id`) REFERENCES `wallets` (`wallet_id`);

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

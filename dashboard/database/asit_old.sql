-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2025 at 01:45 PM
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
-- Database: `asit_old`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_info`
--

CREATE TABLE `admin_info` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin') DEFAULT 'admin',
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_info`
--

INSERT INTO `admin_info` (`id`, `name`, `email`, `password`, `profile_photo`, `created_at`, `role`, `phone`) VALUES
(1, 'Eklas', 'Kh.Eklas502@gmail.com', '21232f297a57a5a743894a0e4a801fc3', '1713945489782.jpg', '2024-10-09 06:01:50', 'admin', '03256465246'),
(2, 'Admin', 'admin@gmail.com', '21232f297a57a5a743894a0e4a801fc3', NULL, '2024-10-09 06:41:20', 'admin', NULL),
(3, 'Admin Lite', 'adminlite@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'IMG_20241007_202157863_HDR.jpg', '2024-11-04 07:36:50', 'admin', '234123424');

-- --------------------------------------------------------

--
-- Table structure for table `lead_data`
--

CREATE TABLE `lead_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `task_date` date NOT NULL,
  `account_email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `tinder_username` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `numbers` varchar(255) DEFAULT NULL,
  `lat_long` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `recovery` varchar(255) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lead_data`
--

INSERT INTO `lead_data` (`id`, `user_id`, `task_date`, `account_email`, `password`, `tinder_username`, `token`, `numbers`, `lat_long`, `comments`, `created_at`, `recovery`, `admin_id`) VALUES
(16, 11, '2024-10-10', 'kh.Eklas502@gmail.com', 'iaojfiosj', 'Emmy3', '973924293-ujaduhfuahf-209837482973:\">', '65126517261', '82374274,-837482748', 'demo user test', '2024-10-09 19:21:21', 'kajsdfjsadi@gmail.com', NULL),
(18, 11, '2024-10-10', 'demotest@gmail.com', '1234', 'demo test', 'uuuuuuu', '888888888', '888,-888888', 'demo test', '2024-10-10 08:41:29', 'gggggg', NULL),
(19, 11, '2024-10-10', 'demotest1@gmail.com', '11111', 'bbbbb1', 'jjjjjj', '111111111111', '11111,-111111', 'test', '2024-10-10 08:42:26', 'demo@gmail.com', NULL),
(20, 11, '2024-10-09', 'new@gmail.com', '111112222', 'olivia3454', 'olivia45sdf', '2342342354', '32344,-232425', 'another test', '2024-10-09 08:46:25', 'new1r@gmail.com', NULL),
(25, 11, '2024-11-01', 'kh.Eklas502@gmail.com', 'iaojfiosj', 'Eva', '9877283647yhuhbfhw374267482ggyugf78___', '89378427847283', '82374274,-837482748', 'we', '2024-11-01 17:46:27', 'retertetrwe', NULL),
(26, 1, '2024-11-02', 'kh.Eklas502@gmail.com', 'dffsasdfaf', 'eklas test', '675765464564-ioajuhauha-7a78a', '+07128370131292', '76546', 'sd', '2024-11-01 18:03:37', 'kajsdfjsadi@gmail.com', NULL),
(27, 2, '2024-11-02', 'admin@gmail.com', 'huihuindsf', 'testuser', 'newtesttoken', '123456779', '2782y7438-827342y8', 'test', '2024-11-01 18:08:22', 'admin@edumail.com', NULL),
(28, 11, '2024-11-02', 'user@gmail.com', 'user@..', 'user32', '675765464564-ioajuhauha-7a78a', '232144134123', '3241243,-134124312', 'user test lead data', '2024-11-01 18:11:26', 'user@edumail.com', NULL),
(29, 1, '2024-11-03', 'admin@gmail.com', '11111', 'Eva', '9877283647yhuhbfhw374267482ggyugf78___', '5675674415', '82374274,-837482748', 'test', '2024-11-03 05:34:16', 'testrec@gmail.com', NULL),
(30, 1, '2024-11-03', 'adsfasdf@gmail.com', 'jasdhuf', 'edq', 'adsfafaf', '234r2332', '23234,-234234', 'hello', '2024-11-03 05:39:52', 'adfadf', NULL),
(31, 3, '2024-11-05', 'nananana@GMAIL.COM', '11111', 'linda', '973924293-ujaduhfuahf-209837482973:\">', '888888', '78675657687t78aaaA', 'AA', '2024-11-05 17:31:13', 'testrec@gmail.com', NULL),
(32, 1, '2024-11-07', 'admin@gmail.com', 'wadff', 'wdfwf', 'werwrew', '34334532', '243q525rwe', 'adfsaffsa', '2024-11-07 06:31:56', 'kajsdfjsadi@gmail.com', NULL),
(33, 1, '2024-11-07', 'admin@gmail.com', 'adsfsaf', 'asfasfasdf', 'adsfafd', 'asdfafas', 'sadfsafd', 'asfasfsfs', '2024-11-07 06:38:48', 'awefadf', NULL),
(34, 1, '2024-11-07', 'admin@gmail.com', 'adsfsaf', 'asfasfasdf', 'adsfafd', 'asdfafas', 'sadfsafd', 'asfasfsfs', '2024-11-07 07:07:22', 'awefadf', NULL),
(35, 15, '2024-11-07', 'emmy79661@gmail.com', 'aass1122', 'elisahtfgt', '306e322e-ab09-478d-9fd1-148bfcd82dd5', '447375322640', '51.3997 / -0.2515 ', '', '2024-11-07 14:01:15', 'gkmr5157@gmail.com', NULL),
(36, 1, '2024-11-14', 'admin@gmail.com', 'iaojfiosj', 'jsfjgnjnfgsjnfgjkdsf', '973924293-ujaduhfuahf-209837482973:\">', '65126517261', '82374274,-837482748', '', '2024-11-14 17:48:54', 'dkjsakl@gmail.com', NULL),
(37, 11, '2024-11-15', 'ilasdhjfnakjfn@gmail.com', 'uafkjajkfbs', 'ailwejfioanfd', '2389y492y47', '2u834y892y349', '32984789247893', 'no', '2024-11-15 12:17:58', 'kaujwfdnajkf', NULL),
(38, 11, '2024-11-15', 'lkajksdfm@gmail.com', 'aklksmdkamsf', 'akooinfaios', 'wijeoiaj', 'aijdoifjwoe', 'wjerijweoir', 'hi', '2024-11-15 12:58:38', 'wkdfke', NULL),
(39, 1, '2024-11-16', 'ergergjkeng@gmail.com', 'jkawjkdfbakhjsdfb', 'akjwenfjksdanf', 'hadjkfnjkf', 'adfsdf', '23423842', 'adas', '2024-11-16 14:31:06', 'wefre', NULL),
(40, 1, '2024-11-16', 'admin@gmail.com', 'iaojfiosj', 'afsafsa3', '23424', '2342342', '34324', 'we', '2024-11-16 14:46:34', '2342', NULL),
(41, 1, '2024-11-16', 'admin@gmail.com', 'iaojfiosj', 'afsafsa3', '23424', '2342342', '34324', 'we', '2024-11-16 14:46:38', '2342', NULL),
(42, 1, '2024-11-16', 'admin@gmail.com', 'iaojfiosj', 'afsafsa3', '23424', '2342342', '34324', 'we', '2024-11-16 14:46:43', '2342', NULL),
(43, 1, '2024-11-16', 'kh.Eklas502@gmail.com', 'ikjds', 'wijdi', 'lkndfkansdk', 'wioejdiowjd', 'wpioejiw', 'wijdiosj', '2024-11-16 14:48:47', 'aiojdiajnsdf', NULL),
(44, 1, '2024-11-16', 'kh.Eklas502@gmail.com', 'wewe', 'werwew', '333', 'sda33', 'safdefsfs', 'adas', '2024-11-16 15:03:10', '333', NULL),
(45, 1, '2024-11-17', 'kh.Eklas502@gmail.com', 'adfsdf', 'asdfasdf', 'asdfsdfasd', 'adsfasf', 'adsfasdf', 'sdaf', '2024-11-17 07:30:31', 'adsfasdf', NULL),
(46, 1, '2024-11-17', 'awefafasdf2@gmail.com', 'asdfasd', 'asdafsdaf', 'adsfs', 'fasd', 'asdfadf', 'asdasfd', '2024-11-17 07:35:43', 'adafas', NULL),
(47, 11, '2024-11-18', 'kh.Eklas502@gmail.com', 'defdsfsd', 'adfasdf', 'sadfsafasf', 'wer3242', 'rwewr32', 'adfasdfasd', '2024-11-18 13:33:13', 'adfasd', NULL),
(48, 14, '2024-11-18', 'dgjkkj@gmail.com', 'fghfhgd', 'szfdsfdgsfgsf', 'sgfsdfgsdfg', '4356563', 'sfdgfdsg435', 'fgh', '2024-11-18 13:56:02', 'sdfgsfdg', NULL),
(49, 18, '2024-11-18', 'adhbafbhf@gmail.com', 'adbjf iahidfas a', 'adfsfasd', '342absdb a', '3452234', '34232', 'dr', '2024-11-18 14:02:54', '343rtg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `live_tokens`
--

CREATE TABLE `live_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) NOT NULL,
  `live_token` longtext NOT NULL,
  `user_type` enum('admin','user') NOT NULL,
  `insert_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `live_tokens`
--

INSERT INTO `live_tokens` (`id`, `user_id`, `admin_id`, `user_name`, `live_token`, `user_type`, `insert_time`) VALUES
(39, 1, NULL, 'Eklas', '0b623263-570c-47e7-b995-c48ffb6df79f', 'admin', '2024-10-31 18:00:43'),
(40, 1, NULL, 'Eklas', 'c9391ac6-1ade-4873-ac54-f5e2a3b2dafc', 'admin', '2024-10-30 18:00:43'),
(41, 1, NULL, 'Eklas', 'kjjadhshs-ahdjfajs-ajudsfj', 'admin', '2024-10-31 18:01:01'),
(42, 1, NULL, 'Eklas', 'juahsdujf-uisahdfiu-ajkdsfj', 'admin', '2024-10-31 18:01:01'),
(43, 1, NULL, 'Eklas', 'jkadjkfbja-aujsdfjs', 'admin', '2024-10-31 18:01:37'),
(44, 1, NULL, 'Eklas', 'sdsfg', 'admin', '2024-10-31 18:05:43'),
(45, 2, NULL, 'Admin', '8ae70e2e-47b8-4d1e-8aa4-d342c9ca471f', 'admin', '2024-10-31 18:22:49'),
(46, 2, NULL, 'Admin', '80a64c32-5143-40b9-9f85-cc17ea7185be', 'admin', '2024-10-31 18:22:49'),
(47, 2, NULL, 'Admin', '6480cd16-558a-4a94-ae30-b15fe10adee0', 'admin', '2024-10-31 18:22:49'),
(48, 2, NULL, 'Admin', '245be52f-11a2-4655-9759-580a421ebe8d', 'admin', '2024-10-31 18:22:49'),
(49, 2, NULL, 'Admin', '26b042bf-e494-49c7-85a7-5c620313fede', 'admin', '2024-10-31 18:22:49'),
(50, 2, NULL, 'Admin', '24cc34f6-5399-4707-b52b-b14983919e82', 'admin', '2024-10-31 18:22:49'),
(51, 2, NULL, 'Admin', '1eb033dd-501f-4c97-af9e-f72e2cf850dc', 'admin', '2024-10-31 18:22:49'),
(52, 2, NULL, 'Admin', '386a4e43-4cff-4530-9b6d-b94004b74a90', 'admin', '2024-10-31 18:22:49'),
(53, 2, NULL, 'Admin', '1d33267e-54f9-48e3-90be-0c54315cf21b', 'admin', '2024-10-31 18:22:49'),
(54, 2, NULL, 'Admin', '55e3a6eb-7d8d-49d0-a133-c9295e88aae3', 'admin', '2024-10-31 18:22:49'),
(55, 2, NULL, 'Admin', '5077ed0e-68da-4a01-82eb-3dd80033b28b', 'admin', '2024-10-31 18:22:49'),
(56, 2, NULL, 'Admin', 'f8740fa6-d704-4c5c-abfb-2c658b774341', 'admin', '2024-10-31 18:22:49'),
(57, 2, NULL, 'Admin', '2f9b8358-1ace-408a-b32b-96c8c6e59004', 'admin', '2024-10-31 18:22:49'),
(58, 2, NULL, 'Admin', '0b623263-570c-47e7-b995-c48ffb6df79f', 'admin', '2024-10-31 18:22:49'),
(59, 2, NULL, 'Admin', 'c9391ac6-1ade-4873-ac54-f5e2a3b2dafc', 'admin', '2024-10-31 18:22:49'),
(60, 2, NULL, 'Admin', '0b623263-570c-47e7-b995-c48ffb6df79f', 'admin', '2024-10-31 18:22:49'),
(61, 2, NULL, 'Admin', 'c9391ac6-1ade-4873-ac54-f5e2a3b2dafc', 'admin', '2024-10-31 18:22:49'),
(62, 11, NULL, 'Demo User', '8ae70e2e-47b8-4d1e-8aa4-d342c9ca471f', 'user', '2024-10-31 18:24:07'),
(63, 11, NULL, 'Demo User', '80a64c32-5143-40b9-9f85-cc17ea7185be', 'user', '2024-10-31 18:24:07'),
(64, 11, NULL, 'Demo User', '6480cd16-558a-4a94-ae30-b15fe10adee0', 'user', '2024-10-31 18:24:07'),
(65, 11, NULL, 'Demo User', '245be52f-11a2-4655-9759-580a421ebe8d', 'user', '2024-10-31 18:24:07'),
(66, 11, NULL, 'Demo User', '26b042bf-e494-49c7-85a7-5c620313fede', 'user', '2024-10-31 18:24:07'),
(67, 11, NULL, 'Demo User', '24cc34f6-5399-4707-b52b-b14983919e82', 'user', '2024-10-31 18:24:07'),
(68, 11, NULL, 'Demo User', '1eb033dd-501f-4c97-af9e-f72e2cf850dc', 'user', '2024-10-31 18:24:07'),
(69, 11, NULL, 'Demo User', '386a4e43-4cff-4530-9b6d-b94004b74a90', 'user', '2024-10-31 18:24:07'),
(72, 11, NULL, 'Demo User', 'c9391ac6-1ade-4873-ac54-f5e2a3b2dafc', 'user', '2024-10-31 18:24:48'),
(73, 13, NULL, 'SoHaG', '8ae70e2e-47b8-4d1e-8aa4-d342c9ca471f', 'user', '2024-10-31 18:25:40'),
(74, 13, NULL, 'SoHaG', '80a64c32-5143-40b9-9f85-cc17ea7185be', 'user', '2024-10-31 18:25:40'),
(75, 13, NULL, 'SoHaG', '6480cd16-558a-4a94-ae30-b15fe10adee0', 'user', '2024-10-31 18:25:40'),
(81, 1, NULL, 'Eklas', '2f9b8358-1ace-408a-b32b-96c8c6e59004', 'admin', '2024-10-31 18:29:54'),
(82, 1, NULL, 'Eklas', '386a4e43-4cff-4530-9b6d-b94004b74a80', 'admin', '2024-10-31 19:14:23'),
(83, 1, NULL, 'Eklas', '386a4e43-4cff-4530-9b6d-b94004b74a90', 'admin', '2024-10-31 19:20:19'),
(84, 1, NULL, 'Eklas', 'newtoken', 'admin', '2024-10-31 19:48:11'),
(85, 14, NULL, 'new user', 'f8740fa6-d704-4c5c-abfb-2c658b774341', 'user', '2024-10-31 19:55:57'),
(86, 14, NULL, 'new user', '2f9b8358-1ace-408a-b32b-96c8c6e59004', 'user', '2024-10-31 19:55:58'),
(87, 14, NULL, 'new user', '0b623263-570c-47e7-b995-c48ffb6df79f', 'user', '2024-10-31 19:55:58'),
(88, 1, NULL, 'Eklas', 'hello world!', 'admin', '2024-11-01 06:18:27'),
(89, 1, NULL, 'Eklas', 'eat.sleep.code.reapet', 'admin', '2024-11-01 06:23:17'),
(90, 1, NULL, 'Eklas', 'new', 'admin', '2024-11-01 08:39:55'),
(91, 1, NULL, 'Eklas', 'today token 2nov', 'admin', '2024-11-02 17:12:37'),
(92, 1, NULL, 'Eklas', 'UIIUDadiu', 'admin', '2024-11-02 17:15:01'),
(93, 1, NULL, 'Eklas', 'iuahsuidhAHUID', 'admin', '2024-11-02 17:15:01'),
(94, 1, NULL, 'Eklas', 'UOahnsdoina', 'admin', '2024-11-02 17:15:01'),
(95, 1, NULL, 'Eklas', 'ioHASIODAOI', 'admin', '2024-11-02 17:15:01'),
(96, 1, NULL, 'Eklas', 'KLnasdaKSDI', 'admin', '2024-11-02 17:15:01'),
(97, 1, NULL, 'Eklas', 'oseirjgiosjfi', 'admin', '2024-11-03 04:17:27'),
(98, 1, NULL, 'Eklas', 'wueiwefuiwui', 'admin', '2024-11-03 04:25:12'),
(99, 1, NULL, 'Eklas', 'iowehfuweahnf', 'admin', '2024-11-03 04:25:12'),
(100, 1, NULL, 'Eklas', 'oiawhsdoifwoih', 'admin', '2024-11-03 04:25:12'),
(101, 1, NULL, 'Eklas', 'awsdfuio', 'admin', '2024-11-03 04:25:12'),
(102, 3, NULL, 'Admin Lite', 'hello ahdiunduians', 'admin', '2024-11-05 08:47:13'),
(103, 1, NULL, 'Eklas', 'ouisahdfouisahudfio', 'admin', '2024-11-07 06:29:11'),
(104, 15, NULL, 'Mosta Fijur Rahman Rocky ', '306e322e-ab09-478d-9fd1-148bfcd82dd5', 'user', '2024-11-07 14:06:56'),
(105, 15, NULL, 'Mosta Fijur Rahman Rocky ', '4145ae54-9745-4fb7-868b-b4e7c17258e1', 'user', '2024-11-07 14:06:56'),
(106, 15, NULL, 'Mosta Fijur Rahman Rocky ', '9832e1f7-190b-4390-a594-03f48cbe531a', 'user', '2024-11-07 14:06:56'),
(107, 15, NULL, 'Mosta Fijur Rahman Rocky ', 'd08a2bd3-908f-4b9d-8a2e-a663dba360a1', 'user', '2024-11-07 14:06:56'),
(108, 15, NULL, 'Mosta Fijur Rahman Rocky ', '88b97154-7d8f-4689-9eeb-93fabf806772', 'user', '2024-11-07 14:06:56'),
(109, 15, NULL, 'Mosta Fijur Rahman Rocky ', '4b64ca3c-972d-4288-98b7-185dd4fa1e86', 'user', '2024-11-07 14:06:56'),
(110, 1, NULL, 'Eklas', 'ajdnfasnfansdfnadfnaslkfd', 'admin', '2024-11-08 08:20:01'),
(111, 1, NULL, 'Eklas', 'uoiyuityufytdtydtydrtd', 'admin', '2024-11-14 08:13:31'),
(112, 1, NULL, 'Eklas', 'iygyuffyuyggiuoihsoifhros4', 'admin', '2024-11-14 08:14:16'),
(113, 1, NULL, 'Eklas', 'iygyuffyuyggiuoihsoifhros5', 'admin', '2024-11-14 08:14:16'),
(114, 1, NULL, 'Eklas', 'iygyuffyuyggiuoihsoifhros6', 'admin', '2024-11-14 08:14:16'),
(115, 1, NULL, 'Eklas', 'iygyuffyuyggiuoihsoifhrfgros4', 'admin', '2024-11-14 08:14:16'),
(116, 1, NULL, 'Eklas', 'iygyuffyuyggiuoihsoifthros4', 'admin', '2024-11-14 08:14:16'),
(117, 1, NULL, 'Eklas', 'hfadfasfan', 'admin', '2024-11-14 16:40:15'),
(118, 1, NULL, 'Eklas', 'ajsdifjasij', 'admin', '2024-11-14 16:40:22'),
(119, 1, NULL, 'Eklas', 'jlnajdnjfa', 'admin', '2024-11-14 16:42:23'),
(120, 11, NULL, 'Demo User updated', 'hellojafhjbakjhfbawes', 'user', '2024-11-15 12:03:47'),
(121, 11, NULL, 'Demo User updated', 'asgsagadsgsdg', 'user', '2024-11-15 12:03:47'),
(122, 11, NULL, 'Demo User updated', 'agfargargrsdgsd', 'user', '2024-11-15 12:03:47'),
(123, 11, NULL, 'Demo User updated', 'ajkdmkasgkmaks', 'user', '2024-11-15 12:54:14'),
(124, 11, NULL, 'Demo User updated', 'adlkfafasnlfndsalkf', 'user', '2024-11-15 13:51:18'),
(125, 1, NULL, 'Eklas', 'siojiogfsjogfnsfdngsdf', 'admin', '2024-11-16 14:30:33'),
(126, 1, NULL, 'Eklas', 'uahsdkjhfsajbvd', 'admin', '2024-11-16 16:41:20'),
(127, 1, NULL, 'Eklas', 'juasdjknhfaskjnf', 'admin', '2024-11-16 16:41:20'),
(128, 1, NULL, 'Eklas', 'jkaskjdnkjfasn', 'admin', '2024-11-16 16:41:20'),
(129, 1, NULL, 'Eklas', 'hii', 'admin', '2024-11-16 18:29:33'),
(130, 1, NULL, 'Eklas', 'inajnajnanakjoa', 'admin', '2024-11-16 18:30:01'),
(131, 1, NULL, 'Eklas', 'ujianiuanjnajna', 'admin', '2024-11-16 18:30:01'),
(132, 1, NULL, 'Eklas', 'auhiuajiajnuianiua', 'admin', '2024-11-16 18:30:01'),
(133, 1, NULL, 'Eklas', 'iuaiojaiaina', 'admin', '2024-11-16 18:30:01'),
(134, 15, NULL, 'Mosta Fijur Rahman Rocky ', '7c43052f-1231-4078-a833-8396cfa4aa3d', 'user', '2024-11-17 12:44:32'),
(135, 15, NULL, 'Mosta Fijur Rahman Rocky ', '7f8b6c7f-53fc-41b0-a141-0ccd5611947e', 'user', '2024-11-17 12:45:07'),
(136, 15, NULL, 'Mosta Fijur Rahman Rocky ', '8f3b72e8-e9c1-4185-aaa3-bd7328357c3d', 'user', '2024-11-17 12:45:07'),
(137, 15, NULL, 'Mosta Fijur Rahman Rocky ', 'dcd84fcb-1bb4-4dcb-85d6-bff374124ba1', 'user', '2024-11-17 12:45:07'),
(138, 15, NULL, 'Mosta Fijur Rahman Rocky ', 'f044ca7a-69bb-48fc-9796-420d4090be6e', 'user', '2024-11-17 12:45:07'),
(139, 15, NULL, 'Mosta Fijur Rahman Rocky ', '27aef6ca-4a6b-40d7-af57-2beff760ee1a', 'user', '2024-11-17 12:45:07'),
(140, 15, NULL, 'Mosta Fijur Rahman Rocky ', '6f11c2b9-731e-402b-ae68-d25f41d4d7e5', 'user', '2024-11-17 12:45:07'),
(141, 15, NULL, 'Mosta Fijur Rahman Rocky ', '6b899836-766a-4f90-8024-88908d0fa661', 'user', '2024-11-17 12:45:46'),
(142, 15, NULL, 'Mosta Fijur Rahman Rocky ', '7d01a3d3-73c7-4bc7-9459-a42f5dd3d129', 'user', '2024-11-17 12:45:46'),
(143, 15, NULL, 'Mosta Fijur Rahman Rocky ', '04245d67-3cb5-4a44-bd17-7c16252a357c', 'user', '2024-11-17 12:45:46'),
(144, 15, NULL, 'Mosta Fijur Rahman Rocky ', '714ba501-b1ce-4357-ba6e-9cb551737995', 'user', '2024-11-17 12:45:46'),
(145, 15, NULL, 'Mosta Fijur Rahman Rocky ', 'eed1b04c-7f06-49aa-8aa7-21ff9579ea15', 'user', '2024-11-17 12:45:46'),
(146, 15, NULL, 'Mosta Fijur Rahman Rocky ', '0b7e6dc9-80cf-44ae-b362-d7d1eb6311d5', 'user', '2024-11-17 12:45:46'),
(147, 15, NULL, 'Mosta Fijur Rahman Rocky ', '32cd3eb3-2b34-4aef-8b7f-71ec7f013474', 'user', '2024-11-17 12:45:46'),
(148, 15, NULL, 'Mosta Fijur Rahman Rocky ', '9d593add-8973-4148-8778-0f0c97c0b350', 'user', '2024-11-17 12:45:46'),
(149, 15, NULL, 'Mosta Fijur Rahman Rocky ', '11dcb507-717d-40d6-9b65-a83bce294e38', 'user', '2024-11-17 12:45:46'),
(150, 11, NULL, 'Demo  test', 'adsfasfhadfkjhjkasjkfas', 'user', '2024-11-18 13:50:07'),
(151, 14, NULL, 'new user', 'afsadfsadfasfasfasdfasfsad', 'user', '2024-11-18 13:52:05'),
(152, 14, NULL, 'new user', 'hjhfjalkdhafhjwaksdf', 'user', '2024-11-18 13:59:56'),
(153, 14, NULL, 'new user', 'gffdxfgsdfsdgsdfdsg', 'user', '2024-11-18 14:00:22'),
(154, 18, NULL, 'rocky', 'kljhadf hg hadbfhasdf jhadskjf', 'user', '2024-11-18 14:02:12'),
(155, 18, NULL, 'rocky', 'sdfgdsfgdg', 'user', '2024-11-18 14:03:17'),
(156, 1, NULL, 'Eklas', '7ggEJwf6Ptz6lW_wGhh0JDzeACNhsEiIACfKELUaooU=,A17F4A77-8C80-40B0-AD3C-18505B648793,50955E6B-E877-4EE1-9059-4A86295FAE1A,iPhone 8,\"iPhone10,1\",16.7.11,CFNetwork/1410.1 Darwin/22.6.0,632827d57a8eb8e2f25d9653b6cf45810120b00e,eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1IjozMTI5OTY3NjAsInYiOjEsImUiOjE3NTUzODM4MTV9.hSmExDWFmunDf478zmMkkder-YahUNBShV4Np4Xxfac,33.959076,-83.385010', 'admin', '2025-08-11 11:40:27');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `title` varchar(255) DEFAULT NULL,
  `status` enum('unread','read') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `created_at`, `title`, `status`) VALUES
(38, 'User : Anik@11111\r\npass: mrsanik@111', '2024-11-15 00:01:32', 'proxy panel', 'read'),
(39, 'fgsfgd', '2024-11-15 00:20:51', 'sgsdfgsdfg', 'read'),
(40, 'fgsfgd', '2024-11-15 00:22:06', 'sgsdfgsdfg', 'read'),
(41, 'aksdmfksmdfk', '2024-11-15 00:22:17', 'dkfmakdf', 'read'),
(42, 'ajodnoasnd', '2024-11-15 00:27:56', 'jandkjfnsdak', 'read'),
(43, 'ds', '2024-11-15 00:28:19', 'asads', 'read'),
(44, 'ds', '2024-11-15 00:29:17', 'asads', 'read'),
(45, 'Bit browser \r\nKernel version 128\r\niPhone 16\r\nBrowser version 128\r\n\r\nPrecision 5000\r\n\r\nTinder.com\r\nLogin in FB \r\nNumber vf\r\n\r\nname \r\n(age 04.05.1995\r\nman not show \r\nstright not show \r\nwomen )\r\n\r\nlooking for 5 no.\r\nnetflix trap music outdoors tiktok music \r\ndouble click\r\nbg ashle all allow \r\nsetting a jeye location check \r\n\r\nlanguage english \r\nlife style ;cat. not for me. non smoker .cfien.vegan.of the grid.now\r\nHeight   5  done \r\nadd city \r\nanthom  last night \r\nSet username \r\nDone \r\npreview done \r\nlog out \r\n2 min por login', '2024-11-15 15:12:18', 'new Method', 'read'),
(46, '<p><strong>Bit browser </strong><br />\nKernel version 128<br />\niPhone 16<br />\nBrowser version 128</p>\n\n<p>Precision 5000</p>\n\n<p>Tinder.com<br />\nLogin in FB <br />\nNumber vf</p>\n\n<p>name <br />\n(age 04.05.1995<br />\nman not show <br />\nstright not show <br />\nwomen )</p>\n\n<p>looking for 5 no.<br />\nnetflix trap music outdoors tiktok music <br />\ndouble click<br />\nbg ashle all allow <br />\nsetting a jeye location check </p>\n\n<p>language english <br />\nlife style ;cat. not for me. non smoker .cfien.vegan.of the grid.now<br />\nHeight   5  done <br />\nadd city <br />\nanthom  last night <br />\nSet username <br />\nDone <br />\npreview done <br />\nlog out <br />\n2 min por login</p>\n', '2024-11-15 15:47:17', 'new Method 2', 'read'),
(47, 'hello world', '2024-11-16 17:16:49', 'new noti', 'read'),
(48, 'good evening', '2024-11-16 17:43:03', 'hello', 'read'),
(49, 'jkljkjn', '2024-11-16 18:28:58', 'kjlkmk', 'read'),
(50, 'awemflksmdlfmslk', '2024-11-16 18:34:47', 'osrklmserklgms', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `notificationviews`
--

CREATE TABLE `notificationviews` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `viewed` tinyint(1) DEFAULT 0,
  `viewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `token_username` varchar(255) NOT NULL,
  `token` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `profile_photo`, `created_at`, `role`) VALUES
(11, 'Demo  test', 'demo@gmail.com', '01782753617', 'ee11cbb19052e40b07aac0ca060c23ee', '1713945489782.jpg', '2024-10-09 19:20:10', 'user'),
(13, 'SoHaG', 'sohag@gmail.com', '1781753617', 'ee11cbb19052e40b07aac0ca060c23ee', '', '2024-10-30 14:45:27', 'user'),
(14, 'new user', 'newuser@gmail.com', '01781753617', 'ee11cbb19052e40b07aac0ca060c23ee', '1713945489782.jpg', '2024-10-30 19:48:02', 'user'),
(15, 'Mosta Fijur Rahman Rocky ', 'rockys621997@gmail.com', '01619108657', '663a2a4f117ffa074fe4646c2c456e2e', '462849042_2845883425561947_2571495265316638121_n.jpg', '2024-11-07 13:58:01', 'user'),
(16, 'Sohan', 'sohan@gmail.com', '1233456779', '6349732148eac85623e848685c42bf58', '617c0be2-8437-4a85-8ccc-dd7940c9af67.jpg', '2024-11-14 15:01:52', 'user'),
(18, 'rocky', 'admin@gmail.com', '1781753617', '21232f297a57a5a743894a0e4a801fc3', 'rocky.jpg', '2024-11-14 15:23:53', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_info`
--
ALTER TABLE `admin_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `lead_data`
--
ALTER TABLE `lead_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `live_tokens`
--
ALTER TABLE `live_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notificationviews`
--
ALTER TABLE `notificationviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_info`
--
ALTER TABLE `admin_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lead_data`
--
ALTER TABLE `lead_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `live_tokens`
--
ALTER TABLE `live_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `notificationviews`
--
ALTER TABLE `notificationviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notificationviews`
--
ALTER TABLE `notificationviews`
  ADD CONSTRAINT `notificationviews_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`),
  ADD CONSTRAINT `notificationviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

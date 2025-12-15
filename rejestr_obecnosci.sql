-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2025 at 12:04 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rejestr_obecnosci`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `password_changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `password_changed_at`) VALUES
(1, 'admin', '$2y$10$62cUQwsHcCk5nLDtEhjFmee/.YLeHy8C3Zkv5l4TZqAaj.QtREXWC', '2025-12-15 09:01:43');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `time`) VALUES
(1, 1, '2025-12-03', '2025-12-08 08:31:16'),
(4, 1, '2025-12-05', '2025-12-08 07:46:53'),
(5, 1, '2025-12-04', '2025-12-08 00:00:00'),
(6, 1, '2025-12-08', '2025-12-08 07:43:03'),
(7, 1, '2025-12-02', '2025-12-08 00:00:00'),
(15, 1, '2025-12-09', '2025-12-09 08:45:05'),
(16, 1, '2025-12-10', '2025-12-10 10:30:20'),
(19, 1, '2025-12-11', '2025-12-11 11:15:22'),
(20, 1, '2025-12-12', '2025-12-12 07:44:14'),
(21, 1, '2025-12-15', '2025-12-15 07:38:33');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `full_name`) VALUES
(1, 'Piotr Cieślik');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ewidencja`
--

CREATE TABLE `ewidencja` (
  `id` int(11) NOT NULL,
  `imie_nazwisko` varchar(255) NOT NULL,
  `data` date NOT NULL,
  `godzina_wyjscia` time NOT NULL,
  `godzina_przyjscia` time NOT NULL,
  `cel` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ewidencja`
--

INSERT INTO `ewidencja` (`id`, `imie_nazwisko`, `data`, `godzina_wyjscia`, `godzina_przyjscia`, `cel`, `created_at`) VALUES
(1, 'Piotr Cieślik', '2025-09-02', '15:00:00', '15:30:00', 'poczta', '2025-09-02 09:06:19'),
(2, 'Piotr Cieślik', '2025-09-04', '14:00:00', '15:30:00', 'PUP', '2025-09-05 04:10:40'),
(3, 'Piotr Cieślik', '2025-09-10', '15:00:00', '15:30:00', 'poczta', '2025-09-10 10:41:51'),
(4, 'Piotr Cieślik', '2025-09-15', '15:00:00', '15:30:00', 'poczta', '2025-09-15 08:04:28'),
(5, 'Piotr Cieślik', '2025-09-26', '15:00:00', '15:30:00', 'poczta', '2025-09-29 04:25:43'),
(6, 'Piotr Cieślik', '2025-11-18', '15:00:00', '15:30:00', 'poczta', '2025-11-19 06:44:59'),
(7, 'Piotr Cieślik', '2025-11-24', '15:00:00', '15:30:00', 'poczta', '2025-11-24 13:43:27'),
(8, 'Piotr Cieślik', '2025-11-25', '15:00:00', '15:30:00', 'poczta', '2025-11-26 08:32:54'),
(11, 'Piotr Cieślik', '2025-12-08', '15:00:00', '15:30:00', 'poczta', '2025-12-08 13:15:24'),
(12, 'Piotr Cieślik', '2025-12-09', '15:00:00', '15:30:00', 'poczta', '2025-12-10 13:12:39'),
(13, 'Piotr Cieślik', '2025-12-10', '15:00:00', '15:30:00', 'poczta', '2025-12-10 13:13:06'),
(14, 'Piotr Cieślik', '2025-12-15', '15:00:00', '15:30:00', 'poczta', '2025-12-15 11:03:55');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `code` varchar(10) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `date`, `code`, `description`) VALUES
(1, '2025-12-24', 'Ś', 'Wigilia'),
(4, '2025-12-25', 'Ś', 'Boże narodzenie '),
(5, '2025-12-26', 'Ś', 'Boże narodzenie '),
(6, '2026-01-01', 'Ś', 'Nowy rok'),
(7, '2026-01-06', 'Ś', 'Świeto Trzech Króli');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeksy dla tabeli `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indeksy dla tabeli `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `full_name` (`full_name`);

--
-- Indeksy dla tabeli `ewidencja`
--
ALTER TABLE `ewidencja`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ewidencja`
--
ALTER TABLE `ewidencja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

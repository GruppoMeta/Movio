-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Creato il: Apr 19, 2018 alle 13:48
-- Versione del server: 5.5.42
-- Versione PHP: 5.4.42

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Struttura della tabella `mobilelocation_tbl`
--

CREATE TABLE `mobilelocation_tbl` (
  `location_id` int(10) unsigned NOT NULL,
  `location_FK_content_id` int(10) unsigned NOT NULL,
  `location_lat` varchar(50) NOT NULL,
  `location_lng` varchar(50) NOT NULL,
  `location_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `mobilelocation_tbl`
--
ALTER TABLE `mobilelocation_tbl`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `location_FK_content_id` (`location_FK_content_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `mobilelocation_tbl`
--
ALTER TABLE `mobilelocation_tbl`
  MODIFY `location_id` int(10) unsigned NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

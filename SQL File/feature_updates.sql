-- ============================================================
-- Feature additions for Car Rental Portal:
-- Online Payment (simulated), Reviews/Ratings, Invoice,
-- Booking Confirmation Notifications, Wishlist
-- Run this AFTER importing the original carrental.sql
-- ============================================================

-- 1. Extend tblbooking with payment tracking columns
ALTER TABLE `tblbooking`
  ADD COLUMN `PaymentStatus` INT(11) DEFAULT 0 AFTER `Status`,
  ADD COLUMN `TransactionId` VARCHAR(50) DEFAULT NULL AFTER `PaymentStatus`,
  ADD COLUMN `PaymentMethod` VARCHAR(50) DEFAULT NULL AFTER `TransactionId`,
  ADD COLUMN `TotalAmount` INT(11) DEFAULT NULL AFTER `PaymentMethod`;
-- PaymentStatus: 0 = Unpaid, 1 = Paid

-- 2. Reviews & ratings, one row per review, tied to a vehicle
CREATE TABLE `tblreviews` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `VehicleId` INT(11) NOT NULL,
  `userEmail` VARCHAR(100) NOT NULL,
  `Rating` INT(11) NOT NULL,
  `ReviewText` TEXT DEFAULT NULL,
  `PostingDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- 3. Wishlist: which vehicles a user has saved
CREATE TABLE `tblwishlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `userEmail` VARCHAR(100) NOT NULL,
  `VehicleId` INT(11) NOT NULL,
  `PostingDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_vehicle` (`userEmail`,`VehicleId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- 4. Simulated Email/SMS confirmation log
--    (Local XAMPP has no real mail/SMS gateway configured, so every
--    "sent" confirmation is logged here and shown to the user as proof
--    of what would have been sent in production.)
CREATE TABLE `tblnotifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `userEmail` VARCHAR(100) NOT NULL,
  `BookingNumber` BIGINT(12) DEFAULT NULL,
  `NotifyType` VARCHAR(20) NOT NULL,
  `Message` TEXT DEFAULT NULL,
  `SentDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

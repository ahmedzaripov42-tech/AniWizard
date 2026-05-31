-- NightOwl Bot Database Schema
-- Vercel deploy uchun tashqi MySQL/MariaDB ga import qiling

CREATE DATABASE IF NOT EXISTS wizard_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wizard_bot;

CREATE TABLE IF NOT EXISTS `user_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Oddiy',
  `refid` bigint(20) DEFAULT NULL,
  `sana` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kabinet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `pul` int(11) DEFAULT 0,
  `pul2` int(11) DEFAULT 0,
  `odam` int(11) DEFAULT 0,
  `ban` varchar(20) DEFAULT 'unban',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channelId` varchar(100) DEFAULT NULL,
  `channelType` varchar(20) DEFAULT 'lock',
  `channelLink` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `joinRequests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channelId` varchar(100) DEFAULT NULL,
  `userId` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `animelar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `qismi` varchar(50) DEFAULT NULL,
  `davlat` varchar(100) DEFAULT NULL,
  `tili` varchar(50) DEFAULT NULL,
  `yili` varchar(20) DEFAULT NULL,
  `janri` varchar(255) DEFAULT NULL,
  `rams` varchar(500) DEFAULT NULL,
  `qidiruv` int(11) DEFAULT 0,
  `sana` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `anime_datas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anime_id` int(11) DEFAULT NULL,
  `qism` int(11) DEFAULT NULL,
  `file_id` varchar(500) DEFAULT NULL,
  `media_type` varchar(50) DEFAULT 'video',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

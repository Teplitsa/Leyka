-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Окт 30 2018 г., 15:40
-- Версия сервера: 10.0.36-MariaDB-0ubuntu0.16.04.1
-- Версия PHP: 7.0.32-0ubuntu0.16.04.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- База данных: `miloserdie_prev`
--

-- --------------------------------------------------------

--
-- Структура таблицы `mlsd_leyka_donations`
--

-- DROP TABLE IF EXISTS `mlsd_leyka_donations`;
CREATE TABLE `mlsd_leyka_donations` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment_type` varchar(20) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `gateway_id` varchar(30) NOT NULL,
  `pm_id` varchar(30) NOT NULL,
  `currency_id` varchar(10) NOT NULL,
  `amount` float NOT NULL,
  `amount_total` float NOT NULL,
  `amount_in_main_currency` float NOT NULL,
  `amount_total_in_main_currency` float NOT NULL,
  `donor_name` varchar(100) NOT NULL,
  `donor_email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `mlsd_leyka_donations_meta`
--

-- DROP TABLE IF EXISTS `mlsd_leyka_donations_meta`;
CREATE TABLE `mlsd_leyka_donations_meta` (
  `meta_id` bigint(20) UNSIGNED NOT NULL,
  `donation_id` bigint(20) UNSIGNED NOT NULL,
  `meta_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `mlsd_leyka_donations`
--
ALTER TABLE `mlsd_leyka_donations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Индексы таблицы `mlsd_leyka_donations_meta`
--
ALTER TABLE `mlsd_leyka_donations_meta`
  ADD PRIMARY KEY (`meta_id`),
  ADD KEY `donation_id_fk` (`donation_id`),
  ADD KEY `meta_key_index` (`meta_key`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `mlsd_leyka_donations_meta`
--
ALTER TABLE `mlsd_leyka_donations_meta`
  MODIFY `meta_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `mlsd_leyka_donations`
--
ALTER TABLE `mlsd_leyka_donations`
  ADD CONSTRAINT `mlsd_leyka_donations_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `mlsd_posts` (`ID`);

--
-- Ограничения внешнего ключа таблицы `mlsd_leyka_donations_meta`
--
ALTER TABLE `mlsd_leyka_donations_meta`
  ADD CONSTRAINT `mlsd_leyka_donations_meta_ibfk_1` FOREIGN KEY (`donation_id`) REFERENCES `mlsd_leyka_donations` (`ID`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
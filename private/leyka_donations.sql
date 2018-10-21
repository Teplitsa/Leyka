-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Окт 21 2018 г., 19:42
-- Версия сервера: 10.1.16-MariaDB
-- Версия PHP: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- База данных: `leyka_local`
--

-- --------------------------------------------------------

--
-- Структура таблицы `qebgwmuu_leyka_donations`
--

DROP TABLE IF EXISTS `qebgwmuu_leyka_donations`;
CREATE TABLE `qebgwmuu_leyka_donations` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL,
  `status_log` text,
  `payment_type` varchar(20) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `date_funded` datetime DEFAULT NULL,
  `date_refunded` datetime DEFAULT NULL,
  `gateway_id` varchar(30) NOT NULL,
  `pm_id` varchar(30) NOT NULL,
  `currency_id` varchar(10) NOT NULL,
  `amount` float NOT NULL,
  `amount_total` float NOT NULL,
  `amount_in_main_currency` float NOT NULL,
  `amount_total_in_main_currency` float NOT NULL,
  `donor_name` varchar(100) NOT NULL,
  `donor_email` varchar(100) NOT NULL,
  `donor_email_date` datetime DEFAULT NULL,
  `donor_comment` text,
  `donor_subscribed` tinyint(1) NOT NULL DEFAULT '0',
  `donor_subscription_email` varchar(100) DEFAULT NULL,
  `manangers_emails_date` datetime DEFAULT NULL,
  `gateway_response` text,
  `init_recurring_donation_id` bigint(20) DEFAULT NULL,
  `recurring_active` tinyint(1) NOT NULL DEFAULT '0',
  `recurring_cancel_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `qebgwmuu_leyka_donations`
--

INSERT INTO `qebgwmuu_leyka_donations` (`ID`, `campaign_id`, `status`, `status_log`, `payment_type`, `date_created`, `date_funded`, `date_refunded`, `gateway_id`, `pm_id`, `currency_id`, `amount`, `amount_total`, `amount_in_main_currency`, `amount_total_in_main_currency`, `donor_name`, `donor_email`, `donor_email_date`, `donor_comment`, `donor_subscribed`, `donor_subscription_email`, `manangers_emails_date`, `gateway_response`, `init_recurring_donation_id`, `recurring_active`, `recurring_cancel_date`) VALUES
(13, 54, 'funded', '', 'single', '2018-10-21 20:41:28', '2018-10-21 00:00:00', NULL, 'yandex', 'yandex_card', 'RUB', 10, 8.9, 10, 8.9, 'Лео', 'ahaenor@gmail.com', NULL, NULL, 0, NULL, '2018-10-21 00:00:00', NULL, 0, 0, NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `qebgwmuu_leyka_donations`
--
ALTER TABLE `qebgwmuu_leyka_donations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `campaign_id` (`campaign_id`),
  ADD KEY `init_recurring_donation_id` (`init_recurring_donation_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `qebgwmuu_leyka_donations`
--
ALTER TABLE `qebgwmuu_leyka_donations`
  MODIFY `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `qebgwmuu_leyka_donations`
--
ALTER TABLE `qebgwmuu_leyka_donations`
  ADD CONSTRAINT `qebgwmuu_leyka_donations_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `qebgwmuu_posts` (`ID`);
COMMIT;
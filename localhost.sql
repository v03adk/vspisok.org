-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny4
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июл 21 2010 г., 15:54
-- Версия сервера: 5.0.51
-- Версия PHP: 5.2.6-1+lenny8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `test_vspisok`
--
CREATE DATABASE `vspisok` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `vspisok`;

-- --------------------------------------------------------

--
-- Структура таблицы `aux_feedback`
--

CREATE TABLE IF NOT EXISTS `aux_feedback` (
  `id` int(11) NOT NULL auto_increment,
  `mes` varchar(2048) NOT NULL,
  `email` varchar(256) NOT NULL,
  `crdate` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='обратная связь' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `aux_feedback`
--


-- --------------------------------------------------------

--
-- Структура таблицы `aux_notice_templates`
--

CREATE TABLE IF NOT EXISTS `aux_notice_templates` (
  `id` int(11) NOT NULL auto_increment,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `aux_notice_templates`
--

INSERT INTO `aux_notice_templates` (`id`, `subject`, `body`) VALUES
(1, 'Активация аккаунта на сайте vspisok.org', '<p>\r\nНа сайте <a href="http://vspisok.org">http://vspisok.org</a> ваш электронный адрес был указан для регистрации. Если вы действительно регистрировались, то пройдите по ссылке для подтверждения регистрации. Если нет, то просто проигнорируйте это письмо.<br /><br />\r\nСсылка для активации : <a href="{link}">{link}\r\n</p>'),
(2, 'Восстановление пароля на сайте vspisok.org', '<p>\r\nВы запросили восстановление пароля на сайте <a href="http://vspisok.org">http://vspisok.org/</a>. Для восстановления пройдите по ссылке. Если вы не запрашивали, просто проигнорируйте это письмо.<br /><br />\r\n\r\nСсылка для восстановления пароля : <a href="{link}">{link}</a>\r\n</p>'),
(3, 'Ваш новый пароль на сайте vspisok.org', 'Ваш новый пароль на сайте <a href="http://vspisok.org">http://vspisok.org</a> : {password}'),
(4, 'Доступ к списку на сайте vspisok.org', '<p>\r\nНа сайте <a href="http://vspisok.org">http://vspisok.org</a> был создан список дел или покупок. Кто-то из ваших друзей или знакомых предоставил Вам к нему доступ. <br />\r\nПройдите по ссылке для просмотра : <a href="http://vspisok.org/sp/show/{url}">http://vspisok.org/sp/show/{url}</a><br />\r\nПароль : {password}\r\n</p>');

-- --------------------------------------------------------

--
-- Структура таблицы `sys_banlist`
--

CREATE TABLE IF NOT EXISTS `sys_banlist` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(64) NOT NULL,
  `tstamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `reason` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Бан лист' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `sys_banlist`
--


-- --------------------------------------------------------

--
-- Структура таблицы `sys_menu`
--

CREATE TABLE IF NOT EXISTS `sys_menu` (
  `id` int(11) NOT NULL auto_increment,
  `link` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_public` tinyint(2) NOT NULL,
  `ord` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Дамп данных таблицы `sys_menu`
--

INSERT INTO `sys_menu` (`id`, `link`, `name`, `is_public`, `ord`) VALUES
(1, '', 'Главная', 1, 1),
(2, 'sp/', 'Мои списки', 0, 2),
(3, 'about/', 'О проекте', 1, 3),
(4, 'feedback/', 'Обратная связь', 1, 4),
(5, 'settings/', 'Настройки', 0, 5);

-- --------------------------------------------------------

--
-- Структура таблицы `sys_users`
--

CREATE TABLE IF NOT EXISTS `sys_users` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  `crdate` timestamp NULL default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Дамп данных таблицы `sys_users`
--

-- --------------------------------------------------------

--
-- Структура таблицы `sys_users_activation`
--

CREATE TABLE IF NOT EXISTS `sys_users_activation` (
  `sys_users_id` int(11) NOT NULL,
  `hash` varchar(40) NOT NULL,
  KEY `hash` (`hash`),
  KEY `sys_users_id` (`sys_users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `sys_users_activation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `sys_users_password_recover`
--

CREATE TABLE IF NOT EXISTS `sys_users_password_recover` (
  `sys_users_id` int(11) NOT NULL,
  `hash` varchar(40) NOT NULL,
  KEY `hash` (`hash`),
  KEY `sys_users_id` (`sys_users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `sys_users_password_recover`
--


-- --------------------------------------------------------

--
-- Структура таблицы `sys_users_temp`
--

CREATE TABLE IF NOT EXISTS `sys_users_temp` (
  `id` int(11) NOT NULL auto_increment,
  `email` varchar(256) NOT NULL,
  `password` varchar(40) NOT NULL,
  `crdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `sys_users_temp`
--


-- --------------------------------------------------------

--
-- Структура таблицы `tasks_elems`
--

CREATE TABLE IF NOT EXISTS `tasks_elems` (
  `id` int(11) NOT NULL auto_increment,
  `url` char(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(1024) NOT NULL,
  `ord` tinyint(3) NOT NULL,
  `status` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `tasks_elems`
--


-- --------------------------------------------------------

--
-- Структура таблицы `tasks_title`
--

CREATE TABLE IF NOT EXISTS `tasks_title` (
  `sys_users_id` int(11) NOT NULL,
  `url` char(5) NOT NULL,
  `is_public` tinyint(2) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `password` varchar(40) default NULL,
  `expire` int(11) NOT NULL default '1979189345',
  PRIMARY KEY  (`sys_users_id`,`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Дамп данных таблицы `tasks_title`
--


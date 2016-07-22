-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июл 22 2016 г., 07:58
-- Версия сервера: 10.1.13-MariaDB-1~jessie
-- Версия PHP: 5.6.19-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `u563_library`
--

-- --------------------------------------------------------

--
-- Структура таблицы `books`
--

CREATE TABLE IF NOT EXISTS `books` (
`id` int(10) unsigned NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `title_info` varchar(255) DEFAULT NULL,
  `body` text,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `bookshelves`
--

CREATE TABLE IF NOT EXISTS `bookshelves` (
`id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin,
  `cover` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Структура таблицы `books_helves`
--

CREATE TABLE IF NOT EXISTS `books_helves` (
`id` int(11) NOT NULL,
  `bookshelf` int(11) NOT NULL,
  `book` int(11) NOT NULL,
  `comment` text COLLATE utf8_bin NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=111 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



--
-- Индексы таблицы `books`
--
ALTER TABLE `books`
 ADD PRIMARY KEY (`id`), ADD KEY `body` (`body`(255)), ADD KEY `title` (`title`), ADD KEY `title_info` (`title_info`), ADD KEY `department` (`department`), ADD KEY `author` (`author`), ADD KEY `full_name` (`full_name`), ADD KEY `title_3` (`title`), ADD KEY `title_info_2` (`title_info`), ADD KEY `title_4` (`title`), ADD KEY `department_2` (`department`), ADD KEY `author_2` (`author`), ADD KEY `full_name_2` (`full_name`), ADD FULLTEXT KEY `title_2` (`title`), ADD FULLTEXT KEY `body_2` (`body`), ADD FULLTEXT KEY `body_3` (`body`), ADD FULLTEXT KEY `title_5` (`title`), ADD FULLTEXT KEY `author_3` (`author`);

--
-- Индексы таблицы `bookshelves`
--
ALTER TABLE `bookshelves`
 ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`), ADD KEY `featured` (`featured`);

--
-- Индексы таблицы `books_helves`
--
ALTER TABLE `books_helves`
 ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `books`
--
ALTER TABLE `books`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT для таблицы `bookshelves`
--
ALTER TABLE `bookshelves`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT для таблицы `books_helves`
--
ALTER TABLE `books_helves`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

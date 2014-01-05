-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 23 Mar 2013, 16:27
-- Wersja serwera: 5.5.16
-- Wersja PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `gamingbets`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `admins`
--

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `add` date NOT NULL,
  `headAdmin` enum('true','false') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `admins`
--

INSERT INTO `admins` (`id`, `userId`, `add`, `headAdmin`) VALUES
(1, 1, '2013-02-04', 'true');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `bets`
--

CREATE TABLE IF NOT EXISTS `bets` (
  `id` int(111) NOT NULL AUTO_INCREMENT,
  `matchId` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  `score-1` double NOT NULL,
  `score-2` double NOT NULL,
  `score-3` double NOT NULL,
  `active` enum('true','false') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `bets`
--

INSERT INTO `bets` (`id`, `matchId`, `type`, `score-1`, `score-2`, `score-3`, `active`) VALUES
(1, 1, 'win', 3.01, 1.11, 0, 'true'),
(2, 2, 'win', 1.01, 9, 0, 'true');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shortCode` varchar(2) NOT NULL,
  `longCode` varchar(3) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=247 ;

--
-- Zrzut danych tabeli `countries`
--

INSERT INTO `countries` (`id`, `shortCode`, `longCode`, `name`) VALUES
(1, 'AD', 'AND', 'Andora'),
(2, 'AE', 'ARE', 'Zjednoczone Emiraty Arabskie'),
(3, 'AF', 'AFG', 'Afganistan'),
(4, 'AG', 'ATG', 'Antigua i Barbuda'),
(5, 'AI', 'AIA', 'Anguilla'),
(6, 'AL', 'ALB', 'Albania'),
(7, 'AM', 'ARM', 'Armenia'),
(8, 'AN', 'ANT', 'Antyle Holenderskie'),
(9, 'AO', 'AGO', 'Angola'),
(10, 'AQ', 'ATA', 'Antarktyda'),
(11, 'AR', 'ARG', 'Argentyna'),
(12, 'AS', 'ASM', 'Samoa Amerykańskie'),
(13, 'AT', 'AUT', 'Austria'),
(14, 'AU', 'AUS', 'Australia'),
(15, 'AW', 'ABW', 'Aruba'),
(16, 'AX', 'ALA', 'Wyspy Alandzkie'),
(17, 'AZ', 'AZE', 'Azerbejdżan'),
(18, 'BA', 'BIH', 'Bośnia i Hercegowina'),
(19, 'BB', 'BRB', 'Barbados'),
(20, 'BD', 'BGD', 'Bangladesz'),
(21, 'BE', 'BEL', 'Belgia'),
(22, 'BF', 'BFA', 'Burkina Faso'),
(23, 'BG', 'BGR', 'Bułgaria'),
(24, 'BH', 'BHR', 'Bahrajn'),
(25, 'BI', 'BDI', 'Burundi'),
(26, 'BJ', 'BEN', 'Benin'),
(27, 'BL', 'BLM', 'Saint-Barthélemy'),
(28, 'BM', 'BMU', 'Bermudy'),
(29, 'BN', 'BRN', 'Brunei'),
(30, 'BO', 'BOL', 'Boliwia'),
(31, 'BR', 'BRA', 'Brazylia'),
(32, 'BS', 'BHS', 'Bahamy'),
(33, 'BT', 'BTN', 'Bhutan'),
(34, 'BV', 'BVT', 'Wyspa Bouveta'),
(35, 'BW', 'BWA', 'Botswana'),
(36, 'BY', 'BLR', 'Białoruś'),
(37, 'BZ', 'BLZ', 'Belize'),
(38, 'CA', 'CAN', 'Kanada'),
(39, 'CC', 'CCK', 'Wyspy Kokosowe'),
(40, 'CD', 'COD', 'Demokratyczna Republika Konga'),
(41, 'CF', 'CAF', 'Republika Środkowoafrykańska'),
(42, 'CG', 'COG', 'Kongo'),
(43, 'CH', 'CHE', 'Szwajcaria'),
(44, 'CI', 'CIV', 'Wybrzeże Kości Słoniowej'),
(45, 'CK', 'COK', 'Wyspy Cooka'),
(46, 'CL', 'CHL', 'Chile'),
(47, 'CM', 'CMR', 'Kamerun'),
(48, 'CN', 'CHN', 'Chiny'),
(49, 'CO', 'COL', 'Kolumbia'),
(50, 'CR', 'CRI', 'Kostaryka'),
(51, 'CU', 'CUB', 'Kuba'),
(52, 'CV', 'CPV', 'Republika Zielonego Przylądka'),
(53, 'CX', 'CXR', 'Wyspa Bożego Narodzenia'),
(54, 'CY', 'CYP', 'Cypr'),
(55, 'CZ', 'CZE', 'Czeska'),
(56, 'DE', 'DEU', 'Niemcy'),
(57, 'DJ', 'DJI', 'Dżibuti'),
(58, 'DK', 'DNK', 'Dania'),
(59, 'DM', 'DMA', 'Dominika'),
(60, 'DO', 'DOM', 'Dominikańska'),
(61, 'DZ', 'DZA', 'Algieria'),
(62, 'EC', 'ECU', 'Ekwador'),
(63, 'EE', 'EST', 'Estonia'),
(64, 'EG', 'EGY', 'Egipt'),
(65, 'EH', 'ESH', 'Sahara Zachodnia'),
(66, 'ER', 'ERI', 'Erytrea'),
(67, 'ES', 'ESP', 'Hiszpania'),
(68, 'ET', 'ETH', 'Etiopia'),
(69, 'FI', 'FIN', 'Finlandia'),
(70, 'FJ', 'FJI', 'Fidżi'),
(71, 'FK', 'FLK', 'Malwiny'),
(72, 'FM', 'FSM', 'Mikronezja'),
(73, 'FO', 'FRO', 'Wyspy Owcze'),
(74, 'FR', 'FRA', 'Francja'),
(75, 'GA', 'GAB', 'Gabon'),
(76, 'GB', 'GBR', 'Wielka Brytania'),
(77, 'GD', 'GRD', 'Grenada'),
(78, 'GE', 'GEO', 'Gruzja'),
(79, 'GF', 'GUF', 'Gujana Francuska'),
(80, 'GG', 'GGY', 'Guernsey'),
(81, 'GH', 'GHA', 'Ghana'),
(82, 'GI', 'GIB', 'Gibraltar'),
(83, 'GL', 'GRL', 'Grenlandia'),
(84, 'GM', 'GMB', 'Gambia'),
(85, 'GN', 'GIN', 'Gwinea'),
(86, 'GP', 'GLP', 'Gwadelupa'),
(87, 'GQ', 'GNQ', 'Gwinea Równikowa'),
(88, 'GR', 'GRC', 'Grecja'),
(89, 'GS', 'SGS', 'Georgia Południowa i Sandwich Południowy'),
(90, 'GT', 'GTM', 'Gwatemala'),
(91, 'GU', 'GUM', 'Guam'),
(92, 'GW', 'GNB', 'Gwinea Bissau'),
(93, 'GY', 'GUY', 'Gujana'),
(94, 'HK', 'HKG', 'Hongkong'),
(95, 'HM', 'HMD', 'Wyspy Heard i McDonalda'),
(96, 'HN', 'HND', 'Honduras'),
(97, 'HR', 'HRV', 'Chorwacja'),
(98, 'HT', 'HTI', 'Haiti'),
(99, 'HU', 'HUN', 'Węgry'),
(100, 'ID', 'IDN', 'Indonezja'),
(101, 'IE', 'IRL', 'Irlandia'),
(102, 'IL', 'ISR', 'Izrael'),
(103, 'IM', 'IMN', 'Wyspa Man'),
(104, 'IN', 'IND', 'Indie'),
(105, 'IO', 'IOT', 'Brytyjskie Terytorium Oceanu Indyjskiego'),
(106, 'IQ', 'IRQ', 'Irak'),
(107, 'IR', 'IRN', 'Iran'),
(108, 'IS', 'ISL', 'Islandia'),
(109, 'IT', 'ITA', 'Włochy'),
(110, 'JE', 'JEY', 'Jersey'),
(111, 'JM', 'JAM', 'Jamajka'),
(112, 'JO', 'JOR', 'Jordania'),
(113, 'JP', 'JPN', 'Japonia'),
(114, 'KE', 'KEN', 'Kenia'),
(115, 'KG', 'KGZ', 'Kirgistan'),
(116, 'KH', 'KHM', 'Kambodża'),
(117, 'KI', 'KIR', 'Kiribati'),
(118, 'KM', 'COM', 'Komory'),
(119, 'KN', 'KNA', 'Saint Kitts i Nevis'),
(120, 'KP', 'PRK', 'Korea Północna'),
(121, 'KR', 'KOR', 'Korea Południowa'),
(122, 'KW', 'KWT', 'Kuwejt'),
(123, 'KY', 'CYM', 'Kajmany'),
(124, 'KZ', 'KAZ', 'Kazachstan'),
(125, 'LA', 'LAO', 'Laos'),
(126, 'LB', 'LBN', 'Liban'),
(127, 'LC', 'LCA', 'Saint Lucia'),
(128, 'LI', 'LIE', 'Liechtenstein'),
(129, 'LK', 'LKA', 'Sri Lanka'),
(130, 'LR', 'LBR', 'Liberia'),
(131, 'LS', 'LSO', 'Lesotho'),
(132, 'LT', 'LTU', 'Litwa'),
(133, 'LU', 'LUX', 'Luksemburg'),
(134, 'LV', 'LVA', 'Łotwa'),
(135, 'LY', 'LBY', 'Libia'),
(136, 'MA', 'MAR', 'Maroko'),
(137, 'MC', 'MCO', 'Monako'),
(138, 'MD', 'MDA', 'Mołdawia'),
(139, 'ME', 'MNE', 'Czarnogóra'),
(140, 'MF', 'MAF', 'Saint-Martin'),
(141, 'MG', 'MDG', 'Madagaskar'),
(142, 'MH', 'MHL', 'Wyspy Marshalla'),
(143, 'MK', 'MKD', 'Macedonia'),
(144, 'ML', 'MLI', 'Mali'),
(145, 'MM', 'MMR', '(Myanmar)'),
(146, 'MN', 'MNG', 'Mongolia'),
(147, 'MO', 'MAC', 'Makau'),
(148, 'MP', 'MNP', 'Mariany Północne'),
(149, 'MQ', 'MTQ', 'Martynika'),
(150, 'MR', 'MRT', 'Mauretania'),
(151, 'MS', 'MSR', 'Montserrat'),
(152, 'MT', 'MLT', 'Malta'),
(153, 'MU', 'MUS', 'Mauritius'),
(154, 'MV', 'MDV', 'Malediwy'),
(155, 'MW', 'MWI', 'Malawi'),
(156, 'MX', 'MEX', 'Meksyk'),
(157, 'MY', 'MYS', 'Malezja'),
(158, 'MZ', 'MOZ', 'Mozambik'),
(159, 'NA', 'NAM', 'Namibia'),
(160, 'NC', 'NCL', 'Nowa Kaledonia'),
(161, 'NE', 'NER', 'Niger'),
(162, 'NF', 'NFK', 'Norfolk'),
(163, 'NG', 'NGA', 'Nigeria'),
(164, 'NI', 'NIC', 'Nikaragua'),
(165, 'NL', 'NLD', '(Niderlandy)'),
(166, 'NO', 'NOR', 'Norwegia'),
(167, 'NP', 'NPL', 'Nepal'),
(168, 'NR', 'NRU', 'Nauru'),
(169, 'NU', 'NIU', 'Niue'),
(170, 'NZ', 'NZL', 'Nowa Zelandia'),
(171, 'OM', 'OMN', 'Oman'),
(172, 'PA', 'PAN', 'Panama'),
(173, 'PE', 'PER', 'Peru'),
(174, 'PF', 'PYF', 'Polinezja Francuska'),
(175, 'PG', 'PNG', 'Papua-Nowa Gwinea'),
(176, 'PH', 'PHL', 'Filipiny'),
(177, 'PK', 'PAK', 'Pakistan'),
(178, 'PL', 'POL', 'Polska'),
(179, 'PM', 'SPM', 'Saint-Pierre i Miquelon'),
(180, 'PN', 'PCN', 'Pitcairn'),
(181, 'PR', 'PRI', 'Portoryko'),
(182, 'PS', 'PSE', 'Palestyna'),
(183, 'PT', 'PRT', 'Portugalia'),
(184, 'PW', 'PLW', 'Palau'),
(185, 'PY', 'PRY', 'Paragwaj'),
(186, 'QA', 'QAT', 'Katar'),
(187, 'RE', 'REU', 'Reunion'),
(188, 'RO', 'ROU', 'Rumunia'),
(189, 'RS', 'SRB', 'Serbia'),
(190, 'RU', 'RUS', 'Rosja'),
(191, 'RW', 'RWA', 'Rwanda'),
(192, 'SA', 'SAU', 'Arabia Saudyjska'),
(193, 'SB', 'SLB', 'Wyspy Salomona'),
(194, 'SC', 'SYC', 'Seszele'),
(195, 'SD', 'SDN', 'Sudan'),
(196, 'SE', 'SWE', 'Szwecja'),
(197, 'SG', 'SGP', 'Singapur'),
(198, 'SH', 'SHN', 'Helena i Zależne'),
(199, 'SI', 'SVN', 'Słowenia'),
(200, 'SJ', 'SJM', 'Jan Mayen (wyspa)'),
(201, 'SK', 'SVK', 'Słowacja'),
(202, 'SL', 'SLE', 'Sierra Leone'),
(203, 'SM', 'SMR', 'San Marino'),
(204, 'SN', 'SEN', 'Senegal'),
(205, 'SO', 'SOM', 'Somalia'),
(206, 'SR', 'SUR', 'Surinam'),
(207, 'ST', 'STP', 'Wyspy Świętego Tomasza i Książęca'),
(208, 'SV', 'SLV', 'Salwador'),
(209, 'SY', 'SYR', 'Syria'),
(210, 'SZ', 'SWZ', 'Suazi'),
(211, 'TC', 'TCA', 'Turks i Caicos'),
(212, 'TD', 'TCD', 'Czad'),
(213, 'TF', 'ATF', 'Antarktyczne Francuskie Terytoria Południowe'),
(214, 'TG', 'TGO', 'Togo'),
(215, 'TH', 'THA', 'Tajlandia'),
(216, 'TJ', 'TJK', 'Tadżykistan'),
(217, 'TK', 'TKL', 'Tokelau'),
(218, 'TL', 'TLS', 'Timor Wschodni'),
(219, 'TM', 'TKM', 'Turkmenistan'),
(220, 'TN', 'TUN', 'Tunezja'),
(221, 'TO', 'TON', 'Tonga'),
(222, 'TR', 'TUR', 'Turcja'),
(223, 'TT', 'TTO', 'Trynidad i Tobago'),
(224, 'TV', 'TUV', 'Tuvalu'),
(225, 'TW', 'TWN', 'Tajwan'),
(226, 'TZ', 'TZA', 'Tanzania'),
(227, 'UA', 'UKR', 'Ukraina'),
(228, 'UG', 'UGA', 'Uganda'),
(229, 'UM', 'UMI', 'Dalekie Wyspy Mniejsze Stanów Zjednoczonych'),
(230, 'US', 'USA', 'Stany Zjednoczone'),
(231, 'UY', 'URY', 'Urugwaj'),
(232, 'UZ', 'UZB', 'Uzbekistan'),
(233, 'VA', 'VAT', 'Watykan'),
(234, 'VC', 'VCT', 'Saint Vincent i Grenadyny'),
(235, 'VE', 'VEN', 'Wenezuela'),
(236, 'VG', 'VGB', 'Brytyjskie Wyspy Dziewicze'),
(237, 'VI', 'VIR', 'Wyspy Dziewicze Stanów Zjednoczonych'),
(238, 'VN', 'VNM', 'Wietnam'),
(239, 'VU', 'VUT', 'Vanuatu'),
(240, 'WF', 'WLF', 'Wallis i Futuna'),
(241, 'WS', 'WSM', 'Samoa'),
(242, 'YE', 'YEM', 'Jemen'),
(243, 'YT', 'MYT', 'Majotta'),
(244, 'ZA', 'ZAF', 'Republika Południowej Afryki'),
(245, 'ZM', 'ZMB', 'Zambia'),
(246, 'ZW', 'ZWE', 'Zimbabwe');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `events`
--

CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gameId` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `city` varchar(32) NOT NULL,
  `countryId` int(11) NOT NULL,
  `dataBegin` datetime NOT NULL,
  `dataEnd` datetime NOT NULL,
  `descriptions` int(11) NOT NULL,
  `teamsId` varchar(128) NOT NULL,
  `url` varchar(32) NOT NULL,
  `images` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `short` varchar(10) NOT NULL,
  `logo` varchar(15) NOT NULL,
  `menuDisplay` enum('true','false') NOT NULL,
  `lp` int(11) NOT NULL,
  `register` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Zrzut danych tabeli `games`
--

INSERT INTO `games` (`id`, `name`, `short`, `logo`, `menuDisplay`, `lp`, `register`) VALUES
(1, 'Counter Strike', 'cs', 'cs.png', 'false', 2, '2013-02-02 12:00:00'),
(2, 'League of Legends', 'lol', 'lol.png', 'true', 1, '2013-02-15 22:00:00'),
(3, 'Startcraft 2', 'sc2', 'sc2.png', 'false', 0, '2013-02-16 00:00:00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `gamings`
--

CREATE TABLE IF NOT EXISTS `gamings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(32) NOT NULL,
  `tag` varchar(32) NOT NULL,
  `register` datetime NOT NULL,
  `active` enum('true','false') NOT NULL,
  `image` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `gamings`
--

INSERT INTO `gamings` (`id`, `fullname`, `tag`, `register`, `active`, `image`, `url`) VALUES
(1, 'Fnatic Team', 'Fnatic', '2013-02-15 22:00:00', 'true', 'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcSoqiTJGmV9yRMPfy6LirDCDH-BxFh_uT7aP8-gUGIRmydh_dq58K6SFw', 'http://www.fnatic.com/'),
(2, 'Meet Your Makers', 'MYM', '2013-02-15 22:00:00', 'true', 'http://www.in4.pl/ima_news/2009/1_19511.', 'http://www.mymym.com/');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `keys`
--

CREATE TABLE IF NOT EXISTS `keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `key` varchar(64) NOT NULL,
  `action` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  `ip` varchar(24) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `keys`
--

INSERT INTO `keys` (`id`, `userId`, `key`, `action`, `date`, `ip`) VALUES
(1, 3, 'b4235174b6dae0e722cd8d0b835f1374', 'account', '2013-03-22 22:48:13', '127.0.0.1');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `matches`
--

CREATE TABLE IF NOT EXISTS `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eventsId` int(11) NOT NULL,
  `gameId` int(11) NOT NULL,
  `teamId-1` int(11) NOT NULL,
  `teamId-2` int(11) NOT NULL,
  `active` enum('true','false') NOT NULL,
  `add` datetime NOT NULL,
  `start` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `matches`
--

INSERT INTO `matches` (`id`, `eventsId`, `gameId`, `teamId-1`, `teamId-2`, `active`, `add`, `start`) VALUES
(1, 0, 2, 1, 2, 'true', '2013-02-15 00:00:00', '2013-02-28 12:15:00'),
(2, 0, 3, 2, 1, 'true', '2013-02-16 00:00:00', '2013-04-05 15:30:00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` enum('before','after','footer') NOT NULL,
  `lp` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Zrzut danych tabeli `menu`
--

INSERT INTO `menu` (`id`, `position`, `lp`, `name`, `link`) VALUES
(1, 'before', 1, 'start', '/'),
(2, 'after', 1, 'events', 'events'),
(3, 'footer', 1, 'map-site', 'mapa-strony'),
(4, 'footer', 2, 'help', 'centrum-pomocy'),
(5, 'footer', 3, 'contact', 'kontakt'),
(6, 'footer', 5, 'work', 'praca'),
(7, 'footer', 4, 'about', 'o-betclic'),
(8, 'footer', 6, 'rules', 'rules'),
(9, 'footer', 7, 'private', 'privates'),
(10, 'footer', 8, 'gaming', 'gaming'),
(11, 'footer', 9, 'team', 'team'),
(12, 'before', 2, 'test', 'test-side');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title-pl` varchar(255) NOT NULL,
  `title-en` varchar(255) NOT NULL,
  `short-url` varchar(255) NOT NULL,
  `access` enum('all','users','admins') NOT NULL,
  `content-pl` longtext NOT NULL,
  `content-en` longtext NOT NULL,
  `add` datetime NOT NULL,
  `userId` int(11) NOT NULL,
  `type` enum('html','bbcode') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `pages`
--

INSERT INTO `pages` (`id`, `title-pl`, `title-en`, `short-url`, `access`, `content-pl`, `content-en`, `add`, `userId`, `type`) VALUES
(1, 'Testowa strona', 'Side for testing', 'test-side', 'all', 'przykladowa tresc', 'for example contain', '2013-02-15 05:24:25', 1, 'html'),
(2, 'Regulamin', 'Rules', 'rules', 'all', '<ol>\r\n<li>Rejestracja jest darmowa</li>\r\n</ol>', '<ol>\r\n<li>If u wanna make a account u must pay for this</li>\r\n</ol>', '2013-02-23 00:00:00', 1, 'html');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `panels`
--

CREATE TABLE IF NOT EXISTS `panels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp` int(11) NOT NULL,
  `active` enum('true','false') NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('html','bbcode') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Zrzut danych tabeli `panels`
--

INSERT INTO `panels` (`id`, `lp`, `active`, `name`, `content`, `type`) VALUES
(1, 1, 'true', 'login', 'file:login.php', ''),
(2, 2, 'true', 'events', 'file:events.php', ''),
(3, 3, 'false', 'BBCode tester', '[code]$zmienna = \\''ALA MA KOTA (12|15)\\''; $liczba = preg_match(\\''/([0-9]+)\\\\|([0-9]+)/\\'', $zmienna, $wyniki);[/code] zwroci: [code]array(3) { [0]=> string(5) \\"12|15\\" [1]=> string(2) \\"12\\" [2]=> string(2) \\"15\\" }[/code]', 'bbcode'),
(4, 2, 'false', 'HTML tester', '<div class="logos">\r\n		<img src="files/images/logos/intel.png">\r\n		<img src="files/images/logos/nvidia.png">\r\n	</div>', 'html');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `partners`
--

CREATE TABLE IF NOT EXISTS `partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp` int(11) NOT NULL,
  `name` varchar(252) NOT NULL,
  `image` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `partners`
--

INSERT INTO `partners` (`id`, `lp`, `name`, `image`, `url`) VALUES
(1, 1, 'Intel Corporation', 'intel.PNG', 'http://www.intel.pl/content/www/pl/pl/homepage.html'),
(2, 2, 'NVIDIA Corporation', 'nvidia.png', '');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(32) NOT NULL,
  `surname` varchar(32) NOT NULL,
  `age` date NOT NULL,
  `gamingId` int(11) NOT NULL,
  `teamId` int(11) NOT NULL,
  `countryId` int(11) NOT NULL,
  `register` datetime NOT NULL,
  `avatar` varchar(48) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(16) NOT NULL,
  `type` varchar(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value-pl` text NOT NULL,
  `value-en` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- Zrzut danych tabeli `settings`
--

INSERT INTO `settings` (`id`, `category`, `type`, `name`, `value-pl`, `value-en`) VALUES
(1, 'auth', 'number', 'unActiveTime', '15', '15'),
(2, 'general', 'string', 'keywords', 'keywords', 'keywords english'),
(3, 'general', 'string', 'description', 'description', 'description english'),
(4, 'general', 'string', 'url', 'http://localhost', 'http://localhost'),
(5, 'ads', 'bool', 'top', 'true', 'true'),
(6, 'general', 'bool', 'login', 'true', 'true'),
(7, 'match', 'number', 'next-match', '5', '5'),
(8, 'general', 'string', 'sitename', 'www.GamingBETS.pl', 'www.GamingBETS.pl'),
(9, 'bets', 'number', 'start-credits', '0', '0'),
(10, 'register', 'bool', 'register', 'true', 'true'),
(11, 'register', 'bool', 'active-account', 'true', 'true'),
(12, 'general', 'bool', 'change-lang', 'true', 'true'),
(13, 'general', 'string', 'default-lang', 'pl', 'pl'),
(14, 'general', 'string', 'rules', 'rules', 'rules'),
(15, 'general', 'string', 'link', 'http://localhost', 'http://localhost'),
(16, 'general', 'string', 'publickey', '6LewTN4SAAAAAOCmGWUIz5PDiqj3kzxPvqWw1Rty', '6LewTN4SAAAAAOCmGWUIz5PDiqj3kzxPvqWw1Rty'),
(17, 'general', 'string', 'privatekey', '6LewTN4SAAAAANVI6UKd3fVBrXzWcCJXA2e-NBTV', '6LewTN4SAAAAANVI6UKd3fVBrXzWcCJXA2e-NBTV'),
(18, 'general', 'string', 'theme', 'blackglass', 'blackglass'),
(19, 'auth', 'number', 'hash-key', '15', '15'),
(20, 'auth', 'number', 'antispam', '3', '3');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `slider`
--

CREATE TABLE IF NOT EXISTS `slider` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp` int(11) NOT NULL,
  `name-pl` varchar(255) NOT NULL,
  `name-en` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description-pl` varchar(255) NOT NULL,
  `description-en` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `active` enum('true','false') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `slider`
--

INSERT INTO `slider` (`id`, `lp`, `name-pl`, `name-en`, `image`, `description-pl`, `description-en`, `url`, `active`) VALUES
(1, 1, 'Intel Extreme Masters', 'Intel Extreme Masters', 'iem.png', '21 stycznia, Warszawa', 'January, 21th, Warsaw', 'page/1', 'true'),
(2, 2, 'League of Legends European Qualification', 'League of Legends European Qualification', 'lol.png', '22 stycznia, Katowice', 'January, 22th, Katowice', 'page/2', 'true');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `social`
--

CREATE TABLE IF NOT EXISTS `social` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp` int(11) NOT NULL,
  `active` enum('true','false') NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Zrzut danych tabeli `social`
--

INSERT INTO `social` (`id`, `lp`, `active`, `name`, `url`) VALUES
(1, 1, 'true', 'facebook', 'http://facebook.com/pieczar92'),
(2, 2, 'true', 'twitter', 'http://twitter.com/pieczar92');

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `teams`
--

CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gameId` int(11) NOT NULL,
  `gamingId` int(11) NOT NULL,
  `countryId` int(11) NOT NULL,
  `registrer` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session-id` varchar(13) NOT NULL,
  `active` enum('true','false') NOT NULL,
  `nick` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `email` varchar(32) NOT NULL,
  `lang` varchar(3) NOT NULL,
  `credits` int(11) NOT NULL,
  `creditsWon` int(11) NOT NULL,
  `creditsBought` int(11) NOT NULL,
  `register` datetime NOT NULL,
  `logged` datetime NOT NULL,
  `lastAction` datetime NOT NULL,
  `firstName` varchar(16) NOT NULL,
  `surname` varchar(32) NOT NULL,
  `age` date NOT NULL,
  `street` varchar(32) NOT NULL,
  `city` varchar(32) NOT NULL,
  `code` varchar(8) NOT NULL,
  `countryId` int(11) NOT NULL,
  `avatar` varchar(48) NOT NULL,
  `ip` varchar(24) NOT NULL,
  `ban` enum('true','false') NOT NULL,
  `banTime` datetime NOT NULL,
  `newsletter` enum('true','false') NOT NULL,
  `refId` int(11) NOT NULL,
  `refCount` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Zrzut danych tabeli `users`
--

INSERT INTO `users` (`id`, `session-id`, `active`, `nick`, `password`, `email`, `lang`, `credits`, `creditsWon`, `creditsBought`, `register`, `logged`, `lastAction`, `firstName`, `surname`, `age`, `street`, `city`, `code`, `countryId`, `avatar`, `ip`, `ban`, `banTime`, `newsletter`, `refId`, `refCount`) VALUES
(1, '514cb27a9a45c', 'true', 'pomek', 'f6595796e883e10a472d5b3d2af53ceb9e1c6b32be1cbac57b', 'pieczar92@interia.pl', 'pl', 1200, 1000, 200, '2013-02-01 00:00:00', '2013-03-22 20:35:22', '2013-03-22 20:46:36', 'Kamil', 'Piechaczek', '1992-01-29', 'Sopocka', 'Sopot', '81-812', 178, 'e9e2cc14a6988f518ac666e48a0b53ad.png', '127.0.0.1', 'false', '0000-00-00 00:00:00', 'true', 0, 1),
(2, '514c68001f088', 'true', 'pomek92', '1b0196edfd601f1889667efaebb33b8c12572835da3f027f78', 'pomek92@pomek92.pl', 'pl', 0, 0, 0, '2013-03-16 22:59:17', '2013-03-22 15:17:36', '2013-03-22 15:17:36', '', '', '0000-00-00', '', '', '', 178, 'none.jpg', '127.0.0.1', 'false', '0000-00-00 00:00:00', 'true', 1, 0),
(3, '', 'false', 'noob', '3054dc00cb601f1889667efaebb33b8c12572835da3f027f78', 'noob@noob.pl', 'pl', 0, 0, 0, '2013-03-22 22:48:13', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '0000-00-00', '', '', '', 178, 'none.jpg', '127.0.0.1', 'false', '0000-00-00 00:00:00', 'false', 1, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

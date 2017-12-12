-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 12, 2017 at 02:26 PM
-- Server version: 5.6.35
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movio`
--

-- --------------------------------------------------------

--
-- Table structure for table `countries_tbl`
--

CREATE TABLE `countries_tbl` (
  `country_id` int(11) NOT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `country_639_2` char(3) DEFAULT NULL,
  `country_639_1` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `countries_tbl`
--

INSERT INTO `countries_tbl` (`country_id`, `country_name`, `country_639_2`, `country_639_1`) VALUES
(1, 'Afrikaans', 'afr', 'af'),
(2, 'Albanian', 'alb', 'sq'),
(3, 'Amharic', 'amh', 'am'),
(4, 'Arabic', 'ara', 'ar'),
(5, 'Armenian', 'arm', 'hy'),
(6, 'Assamese', 'asm', 'as'),
(7, 'Avestan', 'ave', 'ae'),
(8, 'Aymara', 'aym', 'ay'),
(9, 'Azerbaijani', 'aze', 'az'),
(10, 'Bashkir', 'bak', 'ba'),
(11, 'Basque', 'baq', 'eu'),
(12, 'Belarusian', 'bel', 'be'),
(13, 'Bengali', 'ben', 'bn'),
(14, 'Bihari', 'bih', 'bh'),
(15, 'Bislama', 'bis', 'bi'),
(16, 'Bosnian', 'bos', 'bs'),
(17, 'Breton', 'bre', 'br'),
(18, 'Bulgarian', 'bul', 'bg'),
(19, 'Burmese', 'bur', 'my'),
(20, 'Catalan', 'cat', 'ca'),
(21, 'Chamorro', 'cha', 'ch'),
(22, 'Chechen', 'che', 'ce'),
(23, 'Chichewa', 'nya', 'ny'),
(24, 'Chinese', 'chi', 'zh'),
(25, 'Church Slavic', 'chu', 'cu'),
(26, 'Chuvash', 'chv', 'cv'),
(27, 'Cornish', 'cor', 'kw'),
(28, 'Corsican', 'cos', 'co'),
(29, 'Croatian', 'hrv', 'hr'),
(30, 'Czech', 'cze', 'cs'),
(31, 'Danish', 'dan', 'da'),
(32, 'Dutch', 'nld', 'nl'),
(33, 'Dzongkha', 'dzo', 'dz'),
(34, 'English', 'eng', 'en'),
(35, 'Esperanto', 'epo', 'eo'),
(36, 'Estonian', 'est', 'et'),
(37, 'Faroese', 'fao', 'fo'),
(38, 'Fijian', 'fij', 'fj'),
(39, 'Finnish', 'fin', 'fi'),
(40, 'French', 'fra', 'fr'),
(41, 'Frisian', 'fry', 'fy'),
(42, 'Gaelic', 'gla', 'gd'),
(43, 'Galician', 'glg', 'gl'),
(44, 'Georgian', 'geo', 'ka'),
(45, 'German', 'deu', 'de'),
(46, 'Greek (Modern)', 'ell', 'el'),
(47, 'Guarani', 'grn', 'gn'),
(48, 'Gujarati', 'guj', 'gu'),
(49, 'Hebrew', 'heb', 'he'),
(50, 'Herero', 'her', 'hz'),
(51, 'Hindi', 'hin', 'hi'),
(52, 'Hiri Motu', 'hmo', 'ho'),
(53, 'Hungarian', 'hun', 'hu'),
(54, 'Icelandic', 'isl', 'is'),
(55, 'Indonesian', 'ind', 'id'),
(56, 'Interlingua (International Auxiliary Language Association)', 'ina', 'ia'),
(57, 'Interlingue', 'ile', 'ie'),
(58, 'Inuktitut', 'iku', 'iu'),
(59, 'Inupiaq', 'ipk', 'ik'),
(60, 'Irish', 'gle', 'ga'),
(61, 'Italian', 'ita', 'it'),
(62, 'Japanese', 'jpn', 'ja'),
(63, 'Javanese', 'jav', 'jw'),
(64, 'Kalaallisut', 'kal', 'kl'),
(65, 'Kannada', 'kan', 'kn'),
(66, 'Kashmiri', 'kas', 'ks'),
(67, 'Kazakh', 'kaz', 'kk'),
(68, 'Khmer', 'khm', 'km'),
(69, 'Kikuyu', 'kik', 'ki'),
(70, 'Kinyarwanda', 'kin', 'rw'),
(71, 'Kirghiz', 'kir', 'ky'),
(72, 'Komi', 'kom', 'kv'),
(73, 'Korean', 'kor', 'ko'),
(74, 'Kuanyama', 'kua', 'kj'),
(75, 'Kurdish', 'kur', 'ku'),
(76, 'Lao', 'lao', 'lo'),
(77, 'Latin', 'lat', 'la'),
(78, 'Latvian', 'lav', 'lv'),
(79, 'Lingala', 'lin', 'ln'),
(80, 'Lithuanian', 'lit', 'lt'),
(81, 'Luxembourgish', 'ltz', 'lb'),
(82, 'Macedonian', 'mkd', 'mk'),
(83, 'Malagasy', 'mlg', 'mg'),
(84, 'Malay', 'msa', 'ms'),
(85, 'Malayalam', 'mal', 'ml'),
(86, 'Maltese', 'mlt', 'mt'),
(87, 'Manx', 'glv', 'gv'),
(88, 'Maori', 'mao', 'mi'),
(89, 'Marathi', 'mar', 'mr'),
(90, 'Marshallese', 'mah', 'mh'),
(91, 'Moldavian', 'mol', 'mo'),
(92, 'Mongolian', 'mon', 'mn'),
(93, 'Nauru', 'nau', 'na'),
(94, 'Navajo', 'nav', 'nv'),
(95, 'Ndebele, North', 'nde', 'nd'),
(96, 'Ndebele, South', 'nbl', 'nr'),
(97, 'Ndonga', 'ndo', 'ng'),
(98, 'Nepali', 'nep', 'ne'),
(99, 'Northern Sami', 'sme', 'se'),
(100, 'Norwegian', 'nor', 'no'),
(101, 'Norwegian Bokmål', 'nob', 'nb'),
(102, 'Norwegian Nynorsk', 'nno', 'nn'),
(103, 'Occitan (post 1500)', 'oci', 'oc'),
(104, 'Oriya', 'ori', 'or'),
(105, 'Oromo', 'orm', 'om'),
(106, 'Ossetian', 'oss', 'os'),
(107, 'Pali', 'pli', 'pi'),
(108, 'Panjabi', 'pan', 'pa'),
(109, 'Persian', 'fas', 'fa'),
(110, 'Polish', 'pol', 'pl'),
(111, 'Portuguese', 'por', 'pt'),
(112, 'Pushto', 'pus', 'ps'),
(113, 'Quechua', 'que', 'qu'),
(114, 'Raeto-Romance', 'roh', 'rm'),
(115, 'Romanian', 'ron', 'ro'),
(116, 'Rundi', 'run', 'rn'),
(117, 'Russian', 'rus', 'ru'),
(118, 'Samoan', 'smo', 'sm'),
(119, 'Sango', 'sag', 'sg'),
(120, 'Sanskrit', 'san', 'sa'),
(121, 'Sardinian', 'srd', 'sc'),
(122, 'Serbian', 'srp', 'sr'),
(123, 'Shona', 'sna', 'sn'),
(124, 'Sindhi', 'snd', 'sd'),
(125, 'Sinhalese', 'sin', 'si'),
(126, 'Slovak', 'slo', 'sk'),
(127, 'Slovenian', 'slv', 'sl'),
(128, 'Somali', 'som', 'so'),
(129, 'Sotho, Southern', 'sot', 'st'),
(130, 'Spanish', 'spa', 'es'),
(131, 'Sundanese', 'sun', 'su'),
(132, 'Swahili', 'swa', 'sw'),
(133, 'Swati', 'ssw', 'ss'),
(134, 'Swedish', 'swe', 'sv'),
(135, 'Tagalog', 'tgl', 'tl'),
(136, 'Tahitian', 'tah', 'ty'),
(137, 'Tajik', 'tgk', 'tg'),
(138, 'Tamil', 'tam', 'ta'),
(139, 'Tatar', 'tat', 'tt'),
(140, 'Telugu', 'tel', 'te'),
(141, 'Thai', 'tha', 'th'),
(142, 'Tibetan', 'bod', 'bo'),
(143, 'Tsonga', 'tso', 'ts'),
(144, 'Tswana', 'tsn', 'tn'),
(145, 'Turkish', 'tur', 'tr'),
(146, 'Turkmen', 'tuk', 'tk'),
(147, 'Twi', 'twi', 'tw'),
(148, 'Uighur', 'uig', 'ug'),
(149, 'Ukrainian', 'ukr', 'uk'),
(150, 'Urdu', 'urd', 'ur'),
(151, 'Uzbek', 'uzb', 'uz'),
(152, 'Vietnamese', 'vie', 'vi'),
(153, 'Volapük', 'vol', 'vo'),
(154, 'Welsh', 'wel', 'cy'),
(155, 'Welsh', 'cym', 'cy'),
(156, 'Wolof', 'wol', 'wo'),
(157, 'Xhosa', 'xho', 'xh'),
(158, 'Yiddish', 'yid', 'yi'),
(159, 'Zhuang', 'zha', 'za'),
(160, 'Zulu', 'zul', 'zu');

-- --------------------------------------------------------

--
-- Table structure for table `custom_code_mapping_tbl`
--

CREATE TABLE `custom_code_mapping_tbl` (
  `custom_code_mapping_id` int(10) UNSIGNED NOT NULL,
  `custom_code_mapping_description` text NOT NULL,
  `custom_code_mapping_code` varchar(100) NOT NULL DEFAULT '',
  `custom_code_mapping_link` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documents_detail_tbl`
--

CREATE TABLE `documents_detail_tbl` (
  `document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_detail_FK_document_id` int(10) UNSIGNED NOT NULL,
  `document_detail_FK_language_id` int(10) UNSIGNED NOT NULL,
  `document_detail_FK_user_id` int(10) UNSIGNED NOT NULL,
  `document_detail_modificationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `document_detail_status` varchar(9) NOT NULL DEFAULT 'DRAFT',
  `document_detail_translated` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `document_detail_object` longtext NOT NULL,
  `document_detail_isVisible` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `document_detail_note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `documents_detail_tbl`
--

INSERT INTO `documents_detail_tbl` (`document_detail_id`, `document_detail_FK_document_id`, `document_detail_FK_language_id`, `document_detail_FK_user_id`, `document_detail_modificationDate`, `document_detail_status`, `document_detail_translated`, `document_detail_object`, `document_detail_isVisible`, `document_detail_note`) VALUES
(1, 1, 1, 1, '2015-12-15 22:32:36', 'PUBLISHED', 1, '{\"id\":\"2\",\"title\":\"Metanavigation\",\"content\":{\"__indexFields\":{}}}', 1, NULL),
(2, 2, 1, 1, '2015-12-15 22:32:42', 'PUBLISHED', 1, '{\"id\":\"4\",\"title\":\"Utility\",\"content\":{\"__indexFields\":{}}}', 1, NULL),
(3, 3, 1, 1, '2015-12-15 22:32:48', 'PUBLISHED', 1, '{\"id\":\"5\",\"title\":\"Tools\",\"content\":{\"__indexFields\":{}}}', 1, NULL),
(4, 4, 1, 1, '2015-12-15 22:33:24', 'PUBLISHED', 1, '{\"id\":\"6\",\"title\":\"Page 1\",\"content\":{\"__indexFields\":{},\"images\":{},\"attachments\":{},\"text\":\"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duarum enim vitarum nobis erunt instituta capienda. Quid de Pythagora? Bonum liberi: misera orbitas. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Graecum enim hunc versum nostis omnes-: Suavis laborum est praeteritorum memoria. Duo Reges: constructio interrete.<\\/p>\\n<p>An me, inquam, nisi te audire vellem, censes haec dicturum fuisse? At ille pellit, qui permulcet sensum voluptate. Ergo opifex plus sibi proponet ad formarum quam civis excellens ad factorum pulchritudinem? Multoque hoc melius nos veriusque quam Stoici. Heri, inquam, ludis commissis ex urbe profectus veni ad vesperum. Ita enim vivunt quidam, ut eorum vita refellatur oratio. Et quod est munus, quod opus sapientiae? Quae animi affectio suum cuique tribuens atque hanc, quam dico. Non est ista, inquam, Piso, magna dissensio.<\\/p>\\n<p>Quo tandem modo? Sed ego in hoc resisto; Ego vero volo in virtute vim esse quam maximam; Quam ob rem tandem, inquit, non satisfacit? In eo enim positum est id, quod dicimus esse expetendum. Etiam beatissimum?<\\/p>\\n<p>Ita fit cum gravior, tum etiam splendidior oratio. Quodsi vultum tibi, si incessum fingeres, quo gravior viderere, non esses tui similis; Qui convenit? Itaque ad tempus ad Pisonem omnes. Expressa vero in iis aetatibus, quae iam confirmatae sunt. Nunc omni virtuti vitium contrario nomine opponitur.<\\/p>\"}}', 1, NULL),
(5, 5, 1, 1, '2015-12-15 22:33:35', 'PUBLISHED', 1, '{\"id\":\"7\",\"title\":\"Page 2\",\"content\":{\"__indexFields\":{},\"images\":{},\"attachments\":{},\"text\":\"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duarum enim vitarum nobis erunt instituta capienda. Quid de Pythagora? Bonum liberi: misera orbitas. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Graecum enim hunc versum nostis omnes-: Suavis laborum est praeteritorum memoria. Duo Reges: constructio interrete.<\\/p>\\n<p>An me, inquam, nisi te audire vellem, censes haec dicturum fuisse? At ille pellit, qui permulcet sensum voluptate. Ergo opifex plus sibi proponet ad formarum quam civis excellens ad factorum pulchritudinem? Multoque hoc melius nos veriusque quam Stoici. Heri, inquam, ludis commissis ex urbe profectus veni ad vesperum. Ita enim vivunt quidam, ut eorum vita refellatur oratio. Et quod est munus, quod opus sapientiae? Quae animi affectio suum cuique tribuens atque hanc, quam dico. Non est ista, inquam, Piso, magna dissensio.<\\/p>\\n<p>Quo tandem modo? Sed ego in hoc resisto; Ego vero volo in virtute vim esse quam maximam; Quam ob rem tandem, inquit, non satisfacit? In eo enim positum est id, quod dicimus esse expetendum. Etiam beatissimum?<\\/p>\\n<p>Ita fit cum gravior, tum etiam splendidior oratio. Quodsi vultum tibi, si incessum fingeres, quo gravior viderere, non esses tui similis; Qui convenit? Itaque ad tempus ad Pisonem omnes. Expressa vero in iis aetatibus, quae iam confirmatae sunt. Nunc omni virtuti vitium contrario nomine opponitur.<\\/p>\"}}', 1, NULL),
(6, 6, 1, 1, '2015-12-15 22:34:01', 'PUBLISHED', 1, '{\"id\":\"8\",\"title\":\"Guide\",\"content\":{\"__indexFields\":{},\"images\":{},\"attachments\":{},\"text\":\"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duarum enim vitarum nobis erunt instituta capienda. Quid de Pythagora? Bonum liberi: misera orbitas. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Graecum enim hunc versum nostis omnes-: Suavis laborum est praeteritorum memoria. Duo Reges: constructio interrete.<\\/p>\"}}', 1, NULL),
(7, 7, 1, 1, '2015-12-15 22:35:00', 'PUBLISHED', 1, '{\"id\":\"9\",\"title\":\"Sitemap\",\"content\":{\"__indexFields\":{},\"text\":\"\"}}', 1, NULL),
(8, 8, 1, 1, '2015-12-15 22:35:18', 'PUBLISHED', 1, '{\"id\":\"12\",\"title\":\"Lost password\",\"content\":{\"__indexFields\":{},\"text\":\"\",\"confirm\":\"\"}}', 1, NULL),
(9, 9, 1, 1, '2015-12-15 22:35:26', 'PUBLISHED', 1, '{\"id\":\"10\",\"title\":\"Search\",\"content\":{\"__indexFields\":{},\"text\":\"\"}}', 1, NULL),
(10, 10, 1, 1, '2015-12-15 22:35:33', 'PUBLISHED', 1, '{\"id\":\"13\",\"title\":\"My details\",\"content\":{\"__indexFields\":{},\"text\":\"\",\"confirm\":\"\"}}', 1, NULL),
(11, 11, 1, 1, '2015-12-15 22:38:21', 'PUBLISHED', 1, '{\"id\":\"14\",\"title\":\"Contact\",\"content\":{\"__indexFields\":{},\"images\":{},\"attachments\":{},\"text\":\"\"}}', 1, NULL),
(12, 12, 1, 1, '2015-12-15 22:36:37', 'PUBLISHED', 1, '{\"id\":\"17\",\"title\":\"Home\",\"content\":{\"__indexFields\":{},\"link\":\"internal:1\"}}', 1, NULL),
(13, 13, 1, 1, '2015-12-15 22:36:50', 'PUBLISHED', 1, '{\"id\":\"18\",\"title\":\"Sitemap\",\"content\":{\"__indexFields\":{},\"link\":\"internal:9\"}}', 1, NULL),
(14, 14, 1, 1, '2015-12-15 22:37:00', 'PUBLISHED', 1, '{\"id\":\"19\",\"title\":\"Search\",\"content\":{\"__indexFields\":{},\"link\":\"internal:10\"}}', 1, NULL),
(15, 15, 1, 1, '2015-12-15 22:37:10', 'PUBLISHED', 1, '{\"id\":\"15\",\"title\":\"Home\",\"content\":{\"__indexFields\":{},\"link\":\"internal:1\"}}', 1, NULL),
(16, 16, 1, 1, '2015-12-15 22:38:16', 'PUBLISHED', 1, '{\"id\":\"16\",\"title\":\"Contact\",\"content\":{\"__indexFields\":{},\"link\":\"internal:14\"}}', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents_index_datetime_tbl`
--

CREATE TABLE `documents_index_datetime_tbl` (
  `document_index_datetime_id` int(10) UNSIGNED NOT NULL,
  `document_index_datetime_FK_document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_index_datetime_name` varchar(100) NOT NULL,
  `document_index_datetime_value` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documents_index_date_tbl`
--

CREATE TABLE `documents_index_date_tbl` (
  `document_index_date_id` int(10) UNSIGNED NOT NULL,
  `document_index_date_FK_document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_index_date_name` varchar(100) NOT NULL,
  `document_index_date_value` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documents_index_fulltext_tbl`
--

CREATE TABLE `documents_index_fulltext_tbl` (
  `document_index_fulltext_id` int(10) UNSIGNED NOT NULL,
  `document_index_fulltext_FK_document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_index_fulltext_name` varchar(100) NOT NULL,
  `document_index_fulltext_value` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `documents_index_fulltext_tbl`
--

INSERT INTO `documents_index_fulltext_tbl` (`document_index_fulltext_id`, `document_index_fulltext_FK_document_detail_id`, `document_index_fulltext_name`, `document_index_fulltext_value`) VALUES
(1, 1, 'fulltext', 'Metanavigation ##'),
(2, 2, 'fulltext', 'Utility ##'),
(3, 3, 'fulltext', 'Tools ##'),
(4, 4, 'fulltext', 'Page 1 ## Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duarum enim vitarum nobis erunt instituta capienda. Quid de Pythagora? Bonum liberi: misera orbitas. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Graecum enim hunc versum nostis omnes-: Suavis laborum est praeteritorum memoria. Duo Reges: constructio interrete.\nAn me, inquam, nisi te audire vellem, censes haec dicturum fuisse? At ille pellit, qui permulcet sensum voluptate. Ergo opifex plus sibi proponet ad formarum quam civis excellens ad factorum pulchritudinem? Multoque hoc melius nos veriusque quam Stoici. Heri, inquam, ludis commissis ex urbe profectus veni ad vesperum. Ita enim vivunt quidam, ut eorum vita refellatur oratio. Et quod est munus, quod opus sapientiae? Quae animi affectio suum cuique tribuens atque hanc, quam dico. Non est ista, inquam, Piso, magna dissensio.\nQuo tandem modo? Sed ego in hoc resisto; Ego vero volo in virtute vim esse quam maximam; Quam ob rem tandem, inquit, non satisfacit? In eo enim positum est id, quod dicimus esse expetendum. Etiam beatissimum?\nIta fit cum gravior, tum etiam splendidior oratio. Quodsi vultum tibi, si incessum fingeres, quo gravior viderere, non esses tui similis; Qui convenit? Itaque ad tempus ad Pisonem omnes. Expressa vero in iis aetatibus, quae iam confirmatae sunt. Nunc omni virtuti vitium contrario nomine opponitur. ##'),
(5, 5, 'fulltext', 'Page 2 ## Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duarum enim vitarum nobis erunt instituta capienda. Quid de Pythagora? Bonum liberi: misera orbitas. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Graecum enim hunc versum nostis omnes-: Suavis laborum est praeteritorum memoria. Duo Reges: constructio interrete.\nAn me, inquam, nisi te audire vellem, censes haec dicturum fuisse? At ille pellit, qui permulcet sensum voluptate. Ergo opifex plus sibi proponet ad formarum quam civis excellens ad factorum pulchritudinem? Multoque hoc melius nos veriusque quam Stoici. Heri, inquam, ludis commissis ex urbe profectus veni ad vesperum. Ita enim vivunt quidam, ut eorum vita refellatur oratio. Et quod est munus, quod opus sapientiae? Quae animi affectio suum cuique tribuens atque hanc, quam dico. Non est ista, inquam, Piso, magna dissensio.\nQuo tandem modo? Sed ego in hoc resisto; Ego vero volo in virtute vim esse quam maximam; Quam ob rem tandem, inquit, non satisfacit? In eo enim positum est id, quod dicimus esse expetendum. Etiam beatissimum?\nIta fit cum gravior, tum etiam splendidior oratio. Quodsi vultum tibi, si incessum fingeres, quo gravior viderere, non esses tui similis; Qui convenit? Itaque ad tempus ad Pisonem omnes. Expressa vero in iis aetatibus, quae iam confirmatae sunt. Nunc omni virtuti vitium contrario nomine opponitur. ##'),
(6, 6, 'fulltext', 'Guide ## Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duarum enim vitarum nobis erunt instituta capienda. Quid de Pythagora? Bonum liberi: misera orbitas. Quamquam ab iis philosophiam et omnes ingenuas disciplinas habemus; Graecum enim hunc versum nostis omnes-: Suavis laborum est praeteritorum memoria. Duo Reges: constructio interrete. ##'),
(7, 7, 'fulltext', 'Sitemap ##'),
(8, 8, 'fulltext', 'Lost password ##'),
(9, 9, 'fulltext', 'Search ##'),
(10, 10, 'fulltext', 'My details ##'),
(17, 11, 'fulltext', 'Contact ## '),
(12, 12, 'fulltext', 'Home ## internal:1 ##'),
(13, 13, 'fulltext', 'Sitemap ## internal:9 ##'),
(14, 14, 'fulltext', 'Search ## internal:10 ##'),
(15, 15, 'fulltext', 'Home ## internal:1 ##'),
(16, 16, 'fulltext', 'Contact ## internal:14 ##');

-- --------------------------------------------------------

--
-- Table structure for table `documents_index_int_tbl`
--

CREATE TABLE `documents_index_int_tbl` (
  `document_index_int_id` int(10) UNSIGNED NOT NULL,
  `document_index_int_FK_document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_index_int_name` varchar(100) NOT NULL,
  `document_index_int_value` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `documents_index_int_tbl`
--

INSERT INTO `documents_index_int_tbl` (`document_index_int_id`, `document_index_int_FK_document_detail_id`, `document_index_int_name`, `document_index_int_value`) VALUES
(1, 1, 'id', 2),
(2, 2, 'id', 4),
(3, 3, 'id', 5),
(4, 4, 'id', 6),
(5, 5, 'id', 7),
(6, 6, 'id', 8),
(7, 7, 'id', 9),
(8, 8, 'id', 12),
(9, 9, 'id', 10),
(10, 10, 'id', 13),
(11, 11, 'id', 14),
(12, 12, 'id', 17),
(13, 13, 'id', 18),
(14, 14, 'id', 19),
(15, 15, 'id', 15),
(16, 16, 'id', 16);

-- --------------------------------------------------------

--
-- Table structure for table `documents_index_text_tbl`
--

CREATE TABLE `documents_index_text_tbl` (
  `document_index_text_id` int(10) UNSIGNED NOT NULL,
  `document_index_text_FK_document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_index_text_name` varchar(100) NOT NULL,
  `document_index_text_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documents_index_time_tbl`
--

CREATE TABLE `documents_index_time_tbl` (
  `document_index_time_id` int(10) UNSIGNED NOT NULL,
  `document_index_time_FK_document_detail_id` int(10) UNSIGNED NOT NULL,
  `document_index_time_name` varchar(100) NOT NULL,
  `document_index_time_value` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `documents_tbl`
--

CREATE TABLE `documents_tbl` (
  `document_id` int(10) UNSIGNED NOT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `document_creationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `document_FK_site_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `documents_tbl`
--

INSERT INTO `documents_tbl` (`document_id`, `document_type`, `document_creationDate`, `document_FK_site_id`) VALUES
(1, 'glizycms.content', '2015-12-15 22:32:36', NULL),
(2, 'glizycms.content', '2015-12-15 22:32:42', NULL),
(3, 'glizycms.content', '2015-12-15 22:32:48', NULL),
(4, 'glizycms.content', '2015-12-15 22:33:24', NULL),
(5, 'glizycms.content', '2015-12-15 22:33:35', NULL),
(6, 'glizycms.content', '2015-12-15 22:34:01', NULL),
(7, 'glizycms.content', '2015-12-15 22:35:00', NULL),
(8, 'glizycms.content', '2015-12-15 22:35:18', NULL),
(9, 'glizycms.content', '2015-12-15 22:35:26', NULL),
(10, 'glizycms.content', '2015-12-15 22:35:33', NULL),
(11, 'glizycms.content', '2015-12-15 22:35:55', NULL),
(12, 'glizycms.content', '2015-12-15 22:36:37', NULL),
(13, 'glizycms.content', '2015-12-15 22:36:50', NULL),
(14, 'glizycms.content', '2015-12-15 22:37:00', NULL),
(15, 'glizycms.content', '2015-12-15 22:37:10', NULL),
(16, 'glizycms.content', '2015-12-15 22:38:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `entity_properties_tbl`
--

CREATE TABLE `entity_properties_tbl` (
  `entity_properties_id` int(10) UNSIGNED NOT NULL,
  `entity_properties_FK_entity_id` int(10) UNSIGNED NOT NULL,
  `entity_properties_type` varchar(100) NOT NULL,
  `entity_properties_target_FK_entity_id` int(10) UNSIGNED DEFAULT NULL,
  `entity_properties_label_key` varchar(255) NOT NULL,
  `entity_properties_required` tinyint(1) NOT NULL,
  `entity_properties_show_label_in_frontend` tinyint(1) NOT NULL DEFAULT '1',
  `entity_properties_relation_show` int(11) DEFAULT NULL COMMENT '0 = show images, 1 = show links, 2 = show images and links, 3 = hide',
  `entity_properties_reference_relation_show` int(11) NOT NULL DEFAULT '0' COMMENT '0 = Show, 1 = Hide',
  `entity_properties_dublic_core` varchar(100) DEFAULT NULL,
  `entity_properties_row_index` int(10) NOT NULL,
  `entity_properties_params` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `entity_tbl`
--

CREATE TABLE `entity_tbl` (
  `entity_id` int(10) UNSIGNED NOT NULL,
  `entity_name` varchar(255) NOT NULL,
  `entity_show_relations_graph` tinyint(1) NOT NULL DEFAULT '1',
  `entity_skin_attributes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `joins_tbl`
--

CREATE TABLE `joins_tbl` (
  `join_id` int(1) UNSIGNED NOT NULL,
  `join_FK_source_id` int(10) UNSIGNED NOT NULL,
  `join_FK_dest_id` int(10) UNSIGNED NOT NULL,
  `join_objectName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `languages_tbl`
--

CREATE TABLE `languages_tbl` (
  `language_id` int(10) UNSIGNED NOT NULL,
  `language_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `language_name` varchar(100) NOT NULL DEFAULT '',
  `language_code` varchar(10) NOT NULL DEFAULT '',
  `language_FK_country_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `language_isDefault` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `language_order` int(4) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `languages_tbl`
--

INSERT INTO `languages_tbl` (`language_id`, `language_FK_site_id`, `language_name`, `language_code`, `language_FK_country_id`, `language_isDefault`, `language_order`) VALUES
(1, NULL, 'English', 'en', 34, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `logs_tbl`
--

CREATE TABLE `logs_tbl` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `log_level` varchar(100) NOT NULL DEFAULT '',
  `log_date` datetime NOT NULL,
  `log_ip` varchar(20) DEFAULT NULL,
  `log_session` varchar(50) NOT NULL DEFAULT '',
  `log_group` varchar(50) NOT NULL DEFAULT '',
  `log_message` text NOT NULL,
  `log_FK_user_id` int(10) DEFAULT '0',
  `log_FK_site_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mediadetails_tbl`
--

CREATE TABLE `mediadetails_tbl` (
  `mediadetail_id` int(10) UNSIGNED NOT NULL,
  `mediadetail_FK_media_id` int(10) UNSIGNED NOT NULL,
  `media_FK_language_id` int(10) UNSIGNED NOT NULL,
  `media_FK_user_id` int(10) UNSIGNED NOT NULL,
  `media_modificationDate` datetime DEFAULT '0000-00-00 00:00:00',
  `media_title` varchar(255) NOT NULL DEFAULT '',
  `media_category` varchar(255) DEFAULT NULL,
  `media_date` varchar(100) DEFAULT NULL,
  `media_copyright` varchar(255) DEFAULT NULL,
  `media_description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `media_tbl`
--

CREATE TABLE `media_tbl` (
  `media_id` int(10) UNSIGNED NOT NULL,
  `media_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `media_creationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `media_fileName` varchar(255) NOT NULL DEFAULT '',
  `media_size` int(4) UNSIGNED NOT NULL DEFAULT '0',
  `media_type` enum('IMAGE','OFFICE','PDF','ARCHIVE','FLASH','AUDIO','VIDEO','OTHER') NOT NULL DEFAULT 'IMAGE',
  `media_author` varchar(255) DEFAULT '',
  `media_originalFileName` varchar(255) NOT NULL DEFAULT '',
  `media_zoom` tinyint(1) DEFAULT '0',
  `media_download` int(10) NOT NULL DEFAULT '0',
  `media_watermark` tinyint(1) NOT NULL DEFAULT '0',
  `media_allowDownload` tinyint(1) NOT NULL DEFAULT '1',
  `media_thumbFileName` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `menudetails_tbl`
--

CREATE TABLE `menudetails_tbl` (
  `menudetail_id` int(10) UNSIGNED NOT NULL,
  `menudetail_FK_menu_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `menudetail_FK_language_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `menudetail_title` text NOT NULL,
  `menudetail_keywords` text NOT NULL,
  `menudetail_description` text NOT NULL,
  `menudetail_subject` text NOT NULL,
  `menudetail_creator` text NOT NULL,
  `menudetail_publisher` text NOT NULL,
  `menudetail_contributor` text NOT NULL,
  `menudetail_type` text NOT NULL,
  `menudetail_identifier` text NOT NULL,
  `menudetail_source` text NOT NULL,
  `menudetail_relation` text NOT NULL,
  `menudetail_coverage` text NOT NULL,
  `menudetail_isVisible` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `menudetail_titleLink` varchar(255) NOT NULL DEFAULT '',
  `menudetail_linkDescription` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menudetails_tbl`
--

INSERT INTO `menudetails_tbl` (`menudetail_id`, `menudetail_FK_menu_id`, `menudetail_FK_language_id`, `menudetail_title`, `menudetail_keywords`, `menudetail_description`, `menudetail_subject`, `menudetail_creator`, `menudetail_publisher`, `menudetail_contributor`, `menudetail_type`, `menudetail_identifier`, `menudetail_source`, `menudetail_relation`, `menudetail_coverage`, `menudetail_isVisible`, `menudetail_titleLink`, `menudetail_linkDescription`) VALUES
(1, 1, 1, 'Home', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(2, 2, 1, 'Metanavigation', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(3, 3, 1, 'Footer', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(4, 4, 1, 'Utility', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(5, 5, 1, 'Tools', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(6, 6, 1, 'Page 1', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(7, 7, 1, 'Page 2', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(8, 8, 1, 'Guide', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(9, 9, 1, 'Sitemap', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(10, 10, 1, 'Search', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(11, 11, 1, 'Logout', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(12, 12, 1, 'Lost password', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(13, 13, 1, 'My details', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(14, 14, 1, 'Contact', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(15, 15, 1, 'Home', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(16, 16, 1, 'Contact', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(17, 17, 1, 'Home', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(18, 18, 1, 'Sitemap', '', '', '', '', '', '', '', '', '', '', '', 1, '', ''),
(19, 19, 1, 'Search', '', '', '', '', '', '', '', '', '', '', '', 1, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `menus_tbl`
--

CREATE TABLE `menus_tbl` (
  `menu_id` int(10) UNSIGNED NOT NULL,
  `menu_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `menu_parentId` int(10) UNSIGNED DEFAULT '0',
  `menu_pageType` varchar(100) NOT NULL DEFAULT '',
  `menu_order` int(4) UNSIGNED DEFAULT '0',
  `menu_hasPreview` tinyint(1) UNSIGNED DEFAULT '1',
  `menu_creationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `menu_modificationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `menu_type` enum('HOMEPAGE','PAGE','SYSTEM') NOT NULL DEFAULT 'PAGE',
  `menu_url` varchar(255) NOT NULL DEFAULT '',
  `menu_isLocked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `menu_hasComment` tinyint(1) NOT NULL DEFAULT '0',
  `menu_printPdf` tinyint(1) NOT NULL DEFAULT '0',
  `menu_extendsPermissions` tinyint(1) NOT NULL DEFAULT '0',
  `menu_cssClass` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `menus_tbl`
--

INSERT INTO `menus_tbl` (`menu_id`, `menu_FK_site_id`, `menu_parentId`, `menu_pageType`, `menu_order`, `menu_hasPreview`, `menu_creationDate`, `menu_modificationDate`, `menu_type`, `menu_url`, `menu_isLocked`, `menu_hasComment`, `menu_printPdf`, `menu_extendsPermissions`, `menu_cssClass`) VALUES
(1, NULL, 0, 'Home', 1, 1, '2015-01-01 12:00:00', '2015-01-01 12:00:00', 'HOMEPAGE', '', 0, 0, 0, 0, NULL),
(2, NULL, 1, 'Empty', 0, 1, '2015-01-01 12:00:00', '2015-12-15 22:32:37', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(3, NULL, 1, 'Empty', 1, 1, '2015-01-01 12:00:00', '2015-01-01 12:00:00', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(4, NULL, 1, 'Empty', 2, 1, '2015-01-01 12:00:00', '2015-12-15 22:32:43', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(5, NULL, 1, 'Empty', 3, 1, '2015-01-01 12:00:00', '2015-12-15 22:32:48', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(6, NULL, 1, 'Page', 4, 1, '2015-01-01 12:00:00', '2015-12-15 22:33:24', 'PAGE', '', 0, 0, 0, 0, NULL),
(7, NULL, 1, 'Page', 5, 1, '2015-01-01 12:00:00', '2015-12-15 22:33:35', 'PAGE', '', 0, 0, 0, 0, NULL),
(8, NULL, 4, 'Page', 1, 1, '2015-01-01 12:00:00', '2015-12-15 22:34:01', 'PAGE', '', 0, 0, 0, 0, NULL),
(9, NULL, 4, 'SiteMap', 2, 1, '2015-01-01 12:00:00', '2015-12-15 22:35:00', 'PAGE', '', 0, 0, 0, 0, NULL),
(10, NULL, 4, 'Search', 3, 1, '2015-01-01 12:00:00', '2015-12-15 22:35:26', 'PAGE', '', 0, 0, 0, 0, NULL),
(11, NULL, 4, 'Logout', 1, 1, '2015-01-01 12:00:00', '2015-01-01 12:00:00', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(12, NULL, 4, 'LostPassword', 2, 1, '2015-01-01 12:00:00', '2015-12-15 22:35:18', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(13, NULL, 4, 'UserDetails', 3, 1, '2015-01-01 12:00:00', '2015-12-15 22:35:33', 'SYSTEM', '', 0, 0, 0, 0, NULL),
(14, NULL, 4, 'Page', 4, 1, '2015-01-01 12:00:00', '2015-12-15 22:38:21', 'PAGE', '', 0, 0, 0, 0, NULL),
(15, NULL, 3, 'Alias', 1, 1, '2015-01-01 12:00:00', '2015-12-15 22:37:10', 'PAGE', 'alias:internal:1', 0, 0, 0, 0, NULL),
(16, NULL, 3, 'Alias', 2, 1, '2015-01-01 12:00:00', '2015-12-15 22:38:16', 'PAGE', 'alias:internal:14', 0, 0, 0, 0, NULL),
(17, NULL, 2, 'Alias', 3, 1, '2015-01-01 12:00:00', '2015-12-15 22:36:37', 'PAGE', 'alias:internal:1', 0, 0, 0, 0, NULL),
(18, NULL, 2, 'Alias', 4, 1, '2015-01-01 12:00:00', '2015-12-15 22:36:50', 'PAGE', 'alias:internal:9', 0, 0, 0, 0, NULL),
(19, NULL, 2, 'Alias', 5, 1, '2015-01-01 12:00:00', '2015-12-15 22:37:00', 'PAGE', 'alias:internal:10', 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mobilecodes_tbl`
--

CREATE TABLE `mobilecodes_tbl` (
  `mobilecode_id` int(10) UNSIGNED NOT NULL,
  `mobilecode_code` varchar(100) NOT NULL,
  `mobilecode_link` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobilecontents_tbl`
--

CREATE TABLE `mobilecontents_tbl` (
  `content_id` int(10) UNSIGNED NOT NULL,
  `content_menuId` int(10) UNSIGNED NOT NULL,
  `content_documentId` int(10) UNSIGNED NOT NULL,
  `content_pageType` varchar(100) NOT NULL DEFAULT '',
  `content_parent` int(10) UNSIGNED DEFAULT '0',
  `content_type` varchar(100) NOT NULL DEFAULT '',
  `content_title` text NOT NULL,
  `content_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mobilefulltext_tbl`
--

CREATE TABLE `mobilefulltext_tbl` (
  `mobilefulltext_id` int(10) UNSIGNED NOT NULL,
  `mobilefulltext_FK_content_id` int(10) UNSIGNED NOT NULL,
  `mobilefulltext_text` longtext NOT NULL,
  `mobilefulltext_title` varchar(255) NOT NULL,
  `mobilefulltext_subtitle` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `registry_tbl`
--

CREATE TABLE `registry_tbl` (
  `registry_id` int(11) NOT NULL,
  `registry_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `registry_path` varchar(255) NOT NULL DEFAULT '',
  `registry_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `registry_tbl`
--

INSERT INTO `registry_tbl` (`registry_id`, `registry_FK_site_id`, `registry_path`, `registry_value`) VALUES
(1, NULL, 'movio/siteProp/it', 'a:7:{s:5:\"title\";s:5:\"MOVIO\";s:8:\"subtitle\";s:11:\"Sottotitolo\";s:7:\"address\";s:0:\"\";s:6:\"footer\";s:0:\"\";s:9:\"slideShow\";s:0:\"\";s:9:\"analytics\";s:0:\"\";s:7:\"addthis\";i:1;}'),
(2, NULL, 'movio/templateValues/Movio', '{\"0\":{\"title1\":\"MOVIO\",\"title2\":\"Sottotitolo\",\"title3\":\"<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit<\\/p>\",\"title4\":\"<p>Omnia contraria, quos etiam insanos esse vultis.<br \\/>Omnia <strong>contraria<\\/strong>, quos etiam insanos esse vultis.<\\/p>\",\"font1\":\"default\",\"font2\":\"default\",\"c32308f7a298cab9fbf647f092db3fbd3\":\"0\",\"c-body-background\":\"#FFFFFF\",\"c-text\":\"#000000\",\"c-text-heading\":\"#2F2F2F\",\"c-color-link\":\"#CC3522\",\"c-box-image-border\":\"#C6C6C6\",\"c-metanavigation-background\":\"#CC3522\",\"c-metanavigation-link\":\"#FFFFFF\",\"c-metanavigation-background-hover\":\"#B82013\",\"c-slider-background\":\"#CC3522\",\"c-slider-text\":\"#FFFFFF\",\"c-sidebar-background\":\"#F5F5F5\",\"c-sidebar-background-hover\":\"#CC3522\",\"c-sidebar-link\":\"#000000\",\"c-sidebar-link-hover\":\"#FFFFFF\",\"c-box-border\":\"#C6C6C6\",\"c-box-background\":\"#FAFAFA\",\"c-box-header-background\":\"#F3F3F3\",\"c-box-header-link\":\"#000000\",\"c-box-text\":\"#000000\",\"c-icon-in-box\":\"#FFFFFF\",\"c-icon-in-box-background\":\"#A1A1A1\",\"c-color-border-button\":\"#D6D6D6\",\"c-color-background-button\":\"#FFFFFF\",\"c-color-arrow-button-slider\":\"#A1A1A1\",\"c-color-arrow-button-slider-hover\":\"#CB3521\",\"c-timeline-theme\":\"#CC3522\",\"c-footer-background\":\"#363636\",\"c-footer-border\":\"#5E5E5E\",\"c-footer-text\":\"#FFFFFF\",\"customCss\":\"\"}}'),
(3, NULL, 'movio/siteProp/en', 'a:7:{s:5:\"title\";s:10:\"Demo Movio\";s:8:\"subtitle\";s:0:\"\";s:7:\"address\";s:0:\"\";s:9:\"copyright\";s:0:\"\";s:9:\"slideShow\";s:0:\"\";s:9:\"analytics\";s:0:\"\";s:16:\"googleMapsApiKey\";s:0:\"\";}'),
(4, NULL, 'movio/templateName', 'Movio'),
(5, NULL, 'movio/templateValues/Minimal-in-blue', '{\"0\":{\"headerLogo\":\"\",\"footerLogo\":\"\",\"footerLogoLink\":\"\",\"footerLogoTitle\":\"\",\"font1\":\"default\",\"font2\":\"default\",\"cf903f9b82a7e38f5647f245b4042e8fc\":\"0\",\"c-body-background\":\"#FFFFFF\",\"c-text\":\"#333333\",\"c-text-heading\":\"#333333\",\"c-color-link\":\"#0099FF\",\"c-color-link-hover\":\"#008ae6\",\"c-box-image-border\":\"#CCCCCC\",\"c-navigation-background\":\"#0099FF\",\"c-sidebar-link\":\"#0099FF\",\"c-sidebar-link-hover\":\"#008ae6\",\"c-languages-link\":\"#545453\",\"c-languages-link-hover\":\"#0099FF\",\"c-metanavigation-link\":\"#5E5E5D\",\"c-metanavigation-link-hover\":\"#0099FF\",\"c-slider-background\":\"#CCCCCC\",\"c-slider-text\":\"#FFFFFF\",\"c-box-border\":\"#CCCCCC\",\"c-box-background\":\"#FFFFFF\",\"c-box-header-link\":\"#545453\",\"c-box-text\":\"#333333\",\"c-icon-in-box\":\"#FFFFFF\",\"c-icon-in-box-background\":\"#CCCCCC\",\"c-color-border-button\":\"#CCCCCC\",\"c-color-arrow-button-slider\":\"#333333\",\"c-color-arrow-button-slider-hover\":\"0099FF\",\"c-form-border\":\"#CCCCCC\",\"c-form-required\":\"#CCCCCC\",\"c-form-input-text\":\"#333333\",\"c-form-input-background\":\"#FFFFFF\",\"c-form-button-primary\":\"#0099FF\",\"c-form-button\":\"#0099FF\",\"c-form-button-text\":\"#FFFFFF\",\"c-timeline-theme\":\"#0099FF\",\"c-storyteller-background\":\"\",\"c-storyteller-item-background\":\"#FFFFFF\",\"c-storyteller-border\":\"#CCCCCC\",\"c-storyteller-navigation-link\":\"#7A7A7A\",\"c-svg-path-stroke\":\"#000000\",\"c-svg-node-border\":\"#CFCED3\",\"c-svg-main-node-background\":\"#D3D3D3\",\"c-svg-text-link\":\"#CC3522\",\"c-svg-text-node\":\"#000000\",\"c-svg-text-main-node\":\"#000000\",\"c-svg-node-background\":\"#FFFFFF\",\"c-footer-border\":\"#CCCCCC\",\"c-footer-text\":\"#5E5E5D\",\"customCss\":\"\"}}'),
(6, NULL, 'movio/templateValues/Sliding-windows', '{\"0\":{\"headerLogo\":\"\",\"footerLogo\":\"\",\"footerLogoLink\":\"\",\"footerLogoTitle\":\"\",\"font1\":\"default\",\"font2\":\"default\",\"cdfdb5255dc4f54033370477f40d50c4b\":\"0\",\"c-body-background\":\"#FFFFFF\",\"c-box-header-background\":\"#303447\",\"c-text\":\"#000000\",\"c-text-heading\":\"#000000\",\"c-color-link\":\"#076A72\",\"c-breadkcrumbs-link\":\"#000000\",\"c-metanavigation-link\":\"#FFFFFF\",\"c-metanavigation-link-hover\":\"#FFFFFF\",\"c-slider-background\":\"#CCCCCC\",\"c-slider-text\":\"#FFFFFF\",\"c-slider-bullet-background\":\"#000000\",\"c-sidebar-background\":\"#F5F5F5\",\"c-sidebar-background-hover\":\"#076A72\",\"c-sidebar-border\":\"#7A7A7A\",\"c-icon-in-box\":\"#FFFFFF\",\"c-icon-in-box-background\":\"#CCCCCC\",\"c-box-border\":\"#CCCCCC\",\"c-box-background\":\"#FFFFFF\",\"c-box-header-link\":\"#FFFFFF\",\"c-box-text\":\"#000000\",\"c-color-border-button\":\"#CCCCCC\",\"c-color-arrow-button-slider\":\"#000000\",\"c-color-arrow-button-slider-hover\":\"#076A72\",\"c-color-background-button\":\"#FFFFFF\",\"c-storyteller-background\":\"#E4E4E4\",\"c-storyteller-border\":\"#D8D8D8\",\"c-storyteller-item-background\":\"#F9F9F9\",\"c-storyteller-link\":\"#076A72\",\"c-storyteller-navigation-link\":\"#7A7A7A\",\"c-storyteller-image-border\":\"#C6C6C6\",\"c-form-border\":\"#CCCCCC\",\"c-form-button\":\"#7A7A7A\",\"c-form-button-text\":\"#FFFFFF\",\"c-form-button-primary\":\"#076A72\",\"c-form-input-background\":\"#FFFFFF\",\"c-timeline-theme\":\"#076A72\",\"c-footer-background\":\"#303447\",\"c-footer-border\":\"#52979D\",\"c-footer-text\":\"#FFFFFF\",\"c-background-info-page\":\"#076A72\",\"c-color-link-menu\":\"#FFFFFF\",\"c-border-color-link\":\"#CC0000\",\"c-border-sub-title-page\":\"#CCCCCC\",\"c-border-input-header\":\"#838591\",\"c-color-link-entity\":\"#000000\",\"c-button-zoom-img\":\"#CC0000\",\"c-box-sub-menu-background\":\"#222431\",\"c-background-main-content\":\"#FFFFFF\",\"customCss\":\"\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_index_datetime_tbl`
--

CREATE TABLE `simple_documents_index_datetime_tbl` (
  `simple_document_index_datetime_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_datetime_FK_simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_datetime_name` varchar(100) NOT NULL,
  `simple_document_index_datetime_value` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_index_date_tbl`
--

CREATE TABLE `simple_documents_index_date_tbl` (
  `simple_document_index_date_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_date_FK_simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_date_name` varchar(100) NOT NULL,
  `simple_document_index_date_value` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_index_fulltext_tbl`
--

CREATE TABLE `simple_documents_index_fulltext_tbl` (
  `simple_document_index_fulltext_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_fulltext_FK_simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_fulltext_name` varchar(100) NOT NULL,
  `simple_document_index_fulltext_value` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_index_int_tbl`
--

CREATE TABLE `simple_documents_index_int_tbl` (
  `simple_document_index_int_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_int_FK_simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_int_name` varchar(100) NOT NULL,
  `simple_document_index_int_value` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_index_text_tbl`
--

CREATE TABLE `simple_documents_index_text_tbl` (
  `simple_document_index_text_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_text_FK_simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_text_name` varchar(100) NOT NULL,
  `simple_document_index_text_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_index_time_tbl`
--

CREATE TABLE `simple_documents_index_time_tbl` (
  `simple_document_index_time_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_time_FK_simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_index_time_name` varchar(100) NOT NULL,
  `simple_document_index_time_value` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `simple_documents_tbl`
--

CREATE TABLE `simple_documents_tbl` (
  `simple_document_id` int(10) UNSIGNED NOT NULL,
  `simple_document_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `simple_document_type` varchar(255) NOT NULL,
  `simple_document_object` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `speakingurls_tbl`
--

CREATE TABLE `speakingurls_tbl` (
  `speakingurl_id` int(10) UNSIGNED NOT NULL,
  `speakingurl_FK_language_id` int(10) UNSIGNED NOT NULL,
  `speakingurl_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `speakingurl_FK` int(10) UNSIGNED NOT NULL,
  `speakingurl_type` varchar(255) NOT NULL,
  `speakingurl_value` varchar(255) NOT NULL,
  `speakingurl_option` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `usergroups_tbl`
--

CREATE TABLE `usergroups_tbl` (
  `usergroup_id` int(11) NOT NULL,
  `usergroup_name` varchar(50) NOT NULL DEFAULT '',
  `usergroup_backEndAccess` tinyint(1) NOT NULL DEFAULT '0',
  `usergroup_FK_site_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usergroups_tbl`
--

INSERT INTO `usergroups_tbl` (`usergroup_id`, `usergroup_name`, `usergroup_backEndAccess`, `usergroup_FK_site_id`) VALUES
(1, 'Administrator', 1, 1),
(2, 'Supervisor', 1, 1),
(3, 'Editor', 1, 1),
(4, 'User', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `userlogs_tbl`
--

CREATE TABLE `userlogs_tbl` (
  `userlog_id` int(10) UNSIGNED NOT NULL,
  `userlog_FK_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `userlog_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `userlog_session` varchar(50) NOT NULL DEFAULT '',
  `userlog_ip` varchar(50) NOT NULL DEFAULT '',
  `userlog_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userlog_lastAction` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_tbl`
--

CREATE TABLE `users_tbl` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `user_FK_usergroup_id` int(10) UNSIGNED NOT NULL DEFAULT '2',
  `user_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  `user_dateCreation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_isActive` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `user_loginId` varchar(100) NOT NULL DEFAULT '',
  `user_password` varchar(100) NOT NULL DEFAULT '',
  `user_firstName` varchar(100) NOT NULL DEFAULT '',
  `user_lastName` varchar(100) NOT NULL DEFAULT '',
  `user_title` varchar(50) DEFAULT NULL,
  `user_companyName` varchar(255) DEFAULT NULL,
  `user_address` varchar(255) DEFAULT NULL,
  `user_city` varchar(255) DEFAULT NULL,
  `user_zip` varchar(20) DEFAULT NULL,
  `user_state` varchar(100) DEFAULT NULL,
  `user_country` varchar(100) DEFAULT NULL,
  `user_FK_country_id` int(50) DEFAULT '0',
  `user_phone` varchar(100) DEFAULT NULL,
  `user_phone2` varchar(50) DEFAULT NULL,
  `user_mobile` varchar(50) DEFAULT NULL,
  `user_fax` varchar(100) DEFAULT NULL,
  `user_email` varchar(255) NOT NULL DEFAULT '',
  `user_www` varchar(255) DEFAULT NULL,
  `user_birthday` date NOT NULL DEFAULT '0000-00-00',
  `user_sex` enum('M','F') DEFAULT 'M',
  `user_confirmCode` varchar(200) DEFAULT NULL,
  `user_wantNewsletter` tinyint(1) UNSIGNED DEFAULT '1',
  `user_isInMailinglist` tinyint(1) UNSIGNED DEFAULT '0',
  `user_position` varchar(255) DEFAULT NULL,
  `user_department` varchar(255) DEFAULT NULL,
  `user_extid` int(10) UNSIGNED NOT NULL,
  `user_fiscalCode` varchar(32) NOT NULL DEFAULT '',
  `user_vat` varchar(32) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_tbl`
--

INSERT INTO `users_tbl` (`user_id`, `user_FK_usergroup_id`, `user_FK_site_id`, `user_dateCreation`, `user_isActive`, `user_loginId`, `user_password`, `user_firstName`, `user_lastName`, `user_title`, `user_companyName`, `user_address`, `user_city`, `user_zip`, `user_state`, `user_country`, `user_FK_country_id`, `user_phone`, `user_phone2`, `user_mobile`, `user_fax`, `user_email`, `user_www`, `user_birthday`, `user_sex`, `user_confirmCode`, `user_wantNewsletter`, `user_isInMailinglist`, `user_position`, `user_department`, `user_extid`, `user_fiscalCode`, `user_vat`) VALUES
(1, 1, NULL, '2015-01-01 12:00:00', 1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Admin', 'Admin', '', '', '', '', '', '', NULL, 0, '', '', '', '', 'admin', '', '2015-01-01', 'M', '', 1, 1, '', '', 0, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `countries_tbl`
--
ALTER TABLE `countries_tbl`
  ADD PRIMARY KEY (`country_id`);

--
-- Indexes for table `custom_code_mapping_tbl`
--
ALTER TABLE `custom_code_mapping_tbl`
  ADD PRIMARY KEY (`custom_code_mapping_id`);

--
-- Indexes for table `documents_detail_tbl`
--
ALTER TABLE `documents_detail_tbl`
  ADD PRIMARY KEY (`document_detail_id`),
  ADD KEY `document_detail_fk_document_id` (`document_detail_FK_document_id`),
  ADD KEY `document_detail_fk_language_id` (`document_detail_FK_language_id`),
  ADD KEY `document_detail_fk_user_id` (`document_detail_FK_user_id`),
  ADD KEY `document_detail_status` (`document_detail_status`);

--
-- Indexes for table `documents_index_datetime_tbl`
--
ALTER TABLE `documents_index_datetime_tbl`
  ADD PRIMARY KEY (`document_index_datetime_id`),
  ADD KEY `document_index_datetime_fk` (`document_index_datetime_FK_document_detail_id`),
  ADD KEY `document_index_datetime_name` (`document_index_datetime_name`),
  ADD KEY `document_index_datetime_value` (`document_index_datetime_value`);

--
-- Indexes for table `documents_index_date_tbl`
--
ALTER TABLE `documents_index_date_tbl`
  ADD PRIMARY KEY (`document_index_date_id`),
  ADD KEY `document_index_date_name` (`document_index_date_name`),
  ADD KEY `document_index_date_value` (`document_index_date_value`),
  ADD KEY `document_index_date_fk` (`document_index_date_FK_document_detail_id`) USING BTREE;

--
-- Indexes for table `documents_index_fulltext_tbl`
--
ALTER TABLE `documents_index_fulltext_tbl`
  ADD PRIMARY KEY (`document_index_fulltext_id`),
  ADD KEY `document_index_fulltext_name` (`document_index_fulltext_name`),
  ADD KEY `document_index_fulltext_fk` (`document_index_fulltext_FK_document_detail_id`) USING BTREE;
ALTER TABLE `documents_index_fulltext_tbl` ADD FULLTEXT KEY `document_index_fulltext_value` (`document_index_fulltext_value`);

--
-- Indexes for table `documents_index_int_tbl`
--
ALTER TABLE `documents_index_int_tbl`
  ADD PRIMARY KEY (`document_index_int_id`),
  ADD KEY `document_index_int_fk` (`document_index_int_FK_document_detail_id`),
  ADD KEY `document_index_int_name` (`document_index_int_name`),
  ADD KEY `document_index_int_value` (`document_index_int_value`);

--
-- Indexes for table `documents_index_text_tbl`
--
ALTER TABLE `documents_index_text_tbl`
  ADD PRIMARY KEY (`document_index_text_id`),
  ADD KEY `document_index_text_fk` (`document_index_text_FK_document_detail_id`),
  ADD KEY `document_index_text_name` (`document_index_text_name`),
  ADD KEY `document_index_text_value` (`document_index_text_value`);

--
-- Indexes for table `documents_index_time_tbl`
--
ALTER TABLE `documents_index_time_tbl`
  ADD PRIMARY KEY (`document_index_time_id`),
  ADD KEY `document_index_time_fk` (`document_index_time_FK_document_detail_id`),
  ADD KEY `document_index_time_name` (`document_index_time_name`),
  ADD KEY `document_index_time_value` (`document_index_time_value`);

--
-- Indexes for table `documents_tbl`
--
ALTER TABLE `documents_tbl`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `document_type` (`document_type`),
  ADD KEY `document_FK_site_id` (`document_FK_site_id`);

--
-- Indexes for table `entity_properties_tbl`
--
ALTER TABLE `entity_properties_tbl`
  ADD PRIMARY KEY (`entity_properties_id`),
  ADD KEY `entity_properties_fk_entity_id` (`entity_properties_FK_entity_id`),
  ADD KEY `entity_properties_target_fk_entity_id` (`entity_properties_target_FK_entity_id`);

--
-- Indexes for table `entity_tbl`
--
ALTER TABLE `entity_tbl`
  ADD PRIMARY KEY (`entity_id`);

--
-- Indexes for table `joins_tbl`
--
ALTER TABLE `joins_tbl`
  ADD PRIMARY KEY (`join_id`),
  ADD KEY `join_FK_source_id` (`join_FK_source_id`),
  ADD KEY `join_FK_dest_id` (`join_FK_dest_id`),
  ADD KEY `join_objectName` (`join_objectName`);

--
-- Indexes for table `languages_tbl`
--
ALTER TABLE `languages_tbl`
  ADD PRIMARY KEY (`language_id`),
  ADD KEY `language_FK_country_id` (`language_FK_country_id`),
  ADD KEY `language_isDefault` (`language_isDefault`),
  ADD KEY `language_order` (`language_order`);

--
-- Indexes for table `logs_tbl`
--
ALTER TABLE `logs_tbl`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `log_level` (`log_level`),
  ADD KEY `log_group` (`log_group`),
  ADD KEY `log_FK_user_id` (`log_FK_user_id`),
  ADD KEY `log_FK_site_id` (`log_FK_site_id`);

--
-- Indexes for table `mediadetails_tbl`
--
ALTER TABLE `mediadetails_tbl`
  ADD PRIMARY KEY (`mediadetail_id`),
  ADD KEY `mediadetail_FK_media_id` (`mediadetail_FK_media_id`),
  ADD KEY `media_FK_language_id` (`media_FK_language_id`),
  ADD KEY `media_FK_user_id` (`media_FK_user_id`);

--
-- Indexes for table `media_tbl`
--
ALTER TABLE `media_tbl`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `media_FK_site_id` (`media_FK_site_id`),
  ADD KEY `media_type` (`media_type`);

--
-- Indexes for table `menudetails_tbl`
--
ALTER TABLE `menudetails_tbl`
  ADD PRIMARY KEY (`menudetail_id`),
  ADD KEY `menudetail_FK_menu_id` (`menudetail_FK_menu_id`),
  ADD KEY `menudetail_FK_language_id` (`menudetail_FK_language_id`);

--
-- Indexes for table `menus_tbl`
--
ALTER TABLE `menus_tbl`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `menu_FK_site_id` (`menu_FK_site_id`),
  ADD KEY `menu_parentId` (`menu_parentId`),
  ADD KEY `menu_pageType` (`menu_pageType`);

--
-- Indexes for table `mobilecodes_tbl`
--
ALTER TABLE `mobilecodes_tbl`
  ADD PRIMARY KEY (`mobilecode_id`);

--
-- Indexes for table `mobilecontents_tbl`
--
ALTER TABLE `mobilecontents_tbl`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `content_menuId` (`content_menuId`),
  ADD KEY `content_documentId` (`content_documentId`),
  ADD KEY `content_parent` (`content_parent`);

--
-- Indexes for table `mobilefulltext_tbl`
--
ALTER TABLE `mobilefulltext_tbl`
  ADD PRIMARY KEY (`mobilefulltext_id`);

--
-- Indexes for table `registry_tbl`
--
ALTER TABLE `registry_tbl`
  ADD PRIMARY KEY (`registry_id`),
  ADD KEY `registry_path` (`registry_path`);

--
-- Indexes for table `simple_documents_index_datetime_tbl`
--
ALTER TABLE `simple_documents_index_datetime_tbl`
  ADD PRIMARY KEY (`simple_document_index_datetime_id`),
  ADD KEY `simple_document_index_datetime_name` (`simple_document_index_datetime_name`) USING BTREE,
  ADD KEY `simple_document_index_datetime_value` (`simple_document_index_datetime_value`) USING BTREE,
  ADD KEY `simple_document_index_datetime_fk` (`simple_document_index_datetime_FK_simple_document_id`) USING BTREE;

--
-- Indexes for table `simple_documents_index_date_tbl`
--
ALTER TABLE `simple_documents_index_date_tbl`
  ADD PRIMARY KEY (`simple_document_index_date_id`),
  ADD KEY `simple_document_index_date_fk` (`simple_document_index_date_FK_simple_document_id`),
  ADD KEY `simple_document_index_date_value` (`simple_document_index_date_value`),
  ADD KEY `simple_document_index_date_name` (`simple_document_index_date_name`) USING BTREE;

--
-- Indexes for table `simple_documents_index_fulltext_tbl`
--
ALTER TABLE `simple_documents_index_fulltext_tbl`
  ADD PRIMARY KEY (`simple_document_index_fulltext_id`),
  ADD KEY `simple_document_index_fulltext_name` (`simple_document_index_fulltext_name`),
  ADD KEY `simple_document_index_fulltext_fk` (`simple_document_index_fulltext_FK_simple_document_id`) USING BTREE;
ALTER TABLE `simple_documents_index_fulltext_tbl` ADD FULLTEXT KEY `simple_document_index_fulltext_value` (`simple_document_index_fulltext_value`);

--
-- Indexes for table `simple_documents_index_int_tbl`
--
ALTER TABLE `simple_documents_index_int_tbl`
  ADD PRIMARY KEY (`simple_document_index_int_id`),
  ADD KEY `simple_document_index_int_fk` (`simple_document_index_int_FK_simple_document_id`),
  ADD KEY `simple_document_index_int_value` (`simple_document_index_int_value`),
  ADD KEY `simple_document_index_int_name` (`simple_document_index_int_name`) USING BTREE;

--
-- Indexes for table `simple_documents_index_text_tbl`
--
ALTER TABLE `simple_documents_index_text_tbl`
  ADD PRIMARY KEY (`simple_document_index_text_id`),
  ADD KEY `simple_document_index_text_fk` (`simple_document_index_text_FK_simple_document_id`),
  ADD KEY `simple_document_index_text_value` (`simple_document_index_text_value`),
  ADD KEY `simple_document_index_text_name` (`simple_document_index_text_name`) USING BTREE;

--
-- Indexes for table `simple_documents_index_time_tbl`
--
ALTER TABLE `simple_documents_index_time_tbl`
  ADD PRIMARY KEY (`simple_document_index_time_id`),
  ADD KEY `simple_document_index_time_fk` (`simple_document_index_time_FK_simple_document_id`),
  ADD KEY `simple_document_index_time_name` (`simple_document_index_time_name`) USING BTREE,
  ADD KEY `simple_document_index_time_value` (`simple_document_index_time_value`);

--
-- Indexes for table `simple_documents_tbl`
--
ALTER TABLE `simple_documents_tbl`
  ADD PRIMARY KEY (`simple_document_id`),
  ADD KEY `simple_document_type` (`simple_document_type`),
  ADD KEY `simple_document_FK_site_id` (`simple_document_FK_site_id`);

--
-- Indexes for table `speakingurls_tbl`
--
ALTER TABLE `speakingurls_tbl`
  ADD PRIMARY KEY (`speakingurl_id`),
  ADD KEY `speakingurl_FK_language_id` (`speakingurl_FK_language_id`),
  ADD KEY `speakingurl_FK` (`speakingurl_FK`),
  ADD KEY `speakingurl_type` (`speakingurl_type`),
  ADD KEY `speakingurl_FK_site_id` (`speakingurl_FK_site_id`);

--
-- Indexes for table `usergroups_tbl`
--
ALTER TABLE `usergroups_tbl`
  ADD PRIMARY KEY (`usergroup_id`),
  ADD KEY `usergroup_FK_site_id` (`usergroup_FK_site_id`);

--
-- Indexes for table `userlogs_tbl`
--
ALTER TABLE `userlogs_tbl`
  ADD PRIMARY KEY (`userlog_id`),
  ADD UNIQUE KEY `userlog_FK_user_id` (`userlog_FK_user_id`),
  ADD KEY `userlog_FK_site_id` (`userlog_FK_site_id`);

--
-- Indexes for table `users_tbl`
--
ALTER TABLE `users_tbl`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_FK_usergroup_id` (`user_FK_usergroup_id`),
  ADD KEY `user_FK_site_id` (`user_FK_site_id`),
  ADD KEY `user_extid` (`user_extid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `countries_tbl`
--
ALTER TABLE `countries_tbl`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;
--
-- AUTO_INCREMENT for table `custom_code_mapping_tbl`
--
ALTER TABLE `custom_code_mapping_tbl`
  MODIFY `custom_code_mapping_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `documents_detail_tbl`
--
ALTER TABLE `documents_detail_tbl`
  MODIFY `document_detail_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `documents_index_datetime_tbl`
--
ALTER TABLE `documents_index_datetime_tbl`
  MODIFY `document_index_datetime_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `documents_index_date_tbl`
--
ALTER TABLE `documents_index_date_tbl`
  MODIFY `document_index_date_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `documents_index_fulltext_tbl`
--
ALTER TABLE `documents_index_fulltext_tbl`
  MODIFY `document_index_fulltext_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `documents_index_int_tbl`
--
ALTER TABLE `documents_index_int_tbl`
  MODIFY `document_index_int_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `documents_index_text_tbl`
--
ALTER TABLE `documents_index_text_tbl`
  MODIFY `document_index_text_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `documents_index_time_tbl`
--
ALTER TABLE `documents_index_time_tbl`
  MODIFY `document_index_time_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `documents_tbl`
--
ALTER TABLE `documents_tbl`
  MODIFY `document_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `entity_properties_tbl`
--
ALTER TABLE `entity_properties_tbl`
  MODIFY `entity_properties_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `entity_tbl`
--
ALTER TABLE `entity_tbl`
  MODIFY `entity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `joins_tbl`
--
ALTER TABLE `joins_tbl`
  MODIFY `join_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `languages_tbl`
--
ALTER TABLE `languages_tbl`
  MODIFY `language_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `logs_tbl`
--
ALTER TABLE `logs_tbl`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mediadetails_tbl`
--
ALTER TABLE `mediadetails_tbl`
  MODIFY `mediadetail_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `media_tbl`
--
ALTER TABLE `media_tbl`
  MODIFY `media_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `menudetails_tbl`
--
ALTER TABLE `menudetails_tbl`
  MODIFY `menudetail_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `menus_tbl`
--
ALTER TABLE `menus_tbl`
  MODIFY `menu_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
--
-- AUTO_INCREMENT for table `mobilecodes_tbl`
--
ALTER TABLE `mobilecodes_tbl`
  MODIFY `mobilecode_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mobilecontents_tbl`
--
ALTER TABLE `mobilecontents_tbl`
  MODIFY `content_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;
--
-- AUTO_INCREMENT for table `mobilefulltext_tbl`
--
ALTER TABLE `mobilefulltext_tbl`
  MODIFY `mobilefulltext_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;
--
-- AUTO_INCREMENT for table `registry_tbl`
--
ALTER TABLE `registry_tbl`
  MODIFY `registry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `simple_documents_index_datetime_tbl`
--
ALTER TABLE `simple_documents_index_datetime_tbl`
  MODIFY `simple_document_index_datetime_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `simple_documents_index_date_tbl`
--
ALTER TABLE `simple_documents_index_date_tbl`
  MODIFY `simple_document_index_date_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `simple_documents_index_fulltext_tbl`
--
ALTER TABLE `simple_documents_index_fulltext_tbl`
  MODIFY `simple_document_index_fulltext_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `simple_documents_index_int_tbl`
--
ALTER TABLE `simple_documents_index_int_tbl`
  MODIFY `simple_document_index_int_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `simple_documents_index_text_tbl`
--
ALTER TABLE `simple_documents_index_text_tbl`
  MODIFY `simple_document_index_text_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `simple_documents_index_time_tbl`
--
ALTER TABLE `simple_documents_index_time_tbl`
  MODIFY `simple_document_index_time_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `simple_documents_tbl`
--
ALTER TABLE `simple_documents_tbl`
  MODIFY `simple_document_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `speakingurls_tbl`
--
ALTER TABLE `speakingurls_tbl`
  MODIFY `speakingurl_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `usergroups_tbl`
--
ALTER TABLE `usergroups_tbl`
  MODIFY `usergroup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `userlogs_tbl`
--
ALTER TABLE `userlogs_tbl`
  MODIFY `userlog_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users_tbl`
--
ALTER TABLE `users_tbl`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

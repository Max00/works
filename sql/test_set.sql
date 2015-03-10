-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mar 10 Mars 2015 à 12:53
-- Version du serveur :  5.6.17
-- Version de PHP :  5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `works`
--

-- --------------------------------------------------------

--
-- Structure de la table `oeuvres`
--

CREATE TABLE IF NOT EXISTS `oeuvres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `date_realisation` varchar(8) DEFAULT NULL,
  `coords_x` varchar(20) DEFAULT NULL,
  `coords_y` varchar(20) DEFAULT NULL,
  `coords_z` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=190 ;

--
-- Contenu de la table `oeuvres`
--

INSERT INTO `oeuvres` (`id`, `title`, `artist`, `numero`, `date_realisation`, `coords_x`, `coords_y`, `coords_z`) VALUES
(1, 'Ateliers de la compagnie Mi-Octobre / Serge Ricci', 'La compagnie Mi-Octobre/Serge Ricci', '', '20120920', '', '', ''),
(2, 'Par erreur', 'Maciej Albrzykowski', '001', '20020715', 'E5 25 06.7', 'N48 55 10.8', '297'),
(3, 'Utopia : 8215 km dans le 260°', 'Klaus Heid', '002', '19970712', 'E5 26 19.1', 'N48 57 12.8', '239'),
(4, 'Balance', 'Benoît Tremsal', '003', '19970715', '', '', ''),
(5, 'Mobilier désurbanisé', 'Made', '004', '19970712', 'E5 21 22.6', 'N48 55 04.5', '296'),
(6, 'Après le vent', 'Tônu Maarand', '009', '19970715', 'E5 26 47.7', 'N48 56 29.2', '283'),
(7, 'Tüul', 'Ekke Väli', '010', '19970715', 'E5 28 40.7', 'N48 56 01.6', '306'),
(8, 'Une fenêtre en forêt', 'Hiroshi Teshima', '011', '19970712', 'E5 25 43.5', 'N48 53 41.5 ', '305'),
(9, 'Land Field Forest Signs', 'Ernst Amelung', '012', '19970715', 'E5 25 01.9', 'N48 56 08.6', '330'),
(10, 'Acrobates', 'Tjerrie Verhellen', '013', '19970712', 'E5 23 13.8', 'N48 54 05.2 ', '295'),
(11, 'Ombre de lune', 'Sven Domann', '014', '19970715', 'E5 24 20.6', 'N48 53 35.0 ', '300'),
(12, 'Gardien', 'Marek Borsanyi', '015', '19970712', 'E5 28 22.0', 'N48 55 30.0 ', '231'),
(13, 'L''oeil du Cyclope', 'Nicolas Chenard', '016', '19970712', 'E5 25 28.0', 'N48 55 46.2 ', '303'),
(14, 'L''écho du clocher', 'Brigitte Sillard', '017', '19970715', 'E5 24 24.1', 'N48 56 06.1', '306'),
(15, 'Taking the deer home', 'Anna Jacquemard', '020', '19970715', '', '', ''),
(16, 'Notre stère qui est aux cieux', 'Françoise Crémel', '021', '19980711', 'E5 27 17.7', 'N48 55 26.8 ', '258'),
(17, 'Notre stère qui est aux cieux', 'Stéphanie Buttier', '021', '19980711', 'E5 27 17.7', 'N48 55 26.8 ', '258'),
(18, 'Ego', 'Daniel Denise', '024', '19980710', 'E5 24 12.3', 'N48 55 53.7 ', '326'),
(19, 'Renaissance', 'Ryszard Litwiniuk', '025', '19980711', 'E5 22 44.3', 'N48 54 48.8 ', '289'),
(20, 'Responsabilité', 'Terje Ojaver', '029', '19980711', 'E5 24 53.1', 'N48 54 19.6 ', '287'),
(21, 'Le sapin', 'Kazys Venclovas', '033', '19980711', 'E5 24 58.7', 'N48 53 41.3 ', '338'),
(22, 'La forêt m''est témoin', 'Christine Beloeil', '034', '19990710', 'E5 26 48.2', 'N48 56 21.2 ', '306'),
(23, 'Terre / Ciel', 'Chris Booth', '035', '19990710', 'E5 25 14.5', 'N48 55 21.8 ', '332'),
(24, 'La migration du rhinocéros', 'Roger Gaudreau', '036', '19990710', 'E5 27 35.7', 'N48 55 25.7 ', '243'),
(25, 'Stairway to the stars', 'Bo Karberg', '041', '19990715', '', '', ''),
(26, 'Dédale', 'Françoise Maire', '043', '19990710', 'E5 26 27.5', 'N48 56 01.8 ', '346'),
(27, 'Dédale', 'Michèle Schneider', '043', '19990710', 'E5 26 27.5', 'N48 56 01.8 ', '346'),
(28, 'Made by God', 'Tanya Preminger', '045', '19990717', 'E5 23 48.3', 'N48 53 45.4 ', '288'),
(29, 'Puzzle', 'Aligna', '048', '20000715', '', '', ''),
(30, 'Etagère', 'Sarunas Arbaciauskas', '049', '20000715', '', '', ''),
(31, 'Mémorial', 'Claudine Bendotti', '050', '20000715', '', '', ''),
(32, 'Mémorial', 'Amélie Chabannes', '050', '20000715', '', '', ''),
(33, 'Passages', 'Jean-Marie Boivin', '051', '20000715', 'E5 24 33.8', 'N48 56 24.5', '276'),
(34, 'Survivant', 'Horacio Castrejon Calvan', '052', '20000715', '', '', ''),
(35, 'Pinocchio', 'Corinne Jamet', '055', '20000715', '', '', ''),
(36, 'Pinocchio', 'Pierre-Edouard Larivière', '055', '20000715', '', '', ''),
(37, 'L''Envol', 'Michel Kauffman', '056', '20000715', '', '', ''),
(38, 'L''Envol', 'Marie Saurat', '056', '20000715', '', '', ''),
(39, 'Tricolore', 'Richard Künz', '057', '20000715', '', '', ''),
(40, 'Epaves Forestières', 'L.N. Lecheviller', '058', '20000715', '', '', ''),
(41, 'Après', 'Ulrike Rumpenhorst', '061', '20000715', '', '', ''),
(42, 'L''oiseau source', 'Jean-Pierre Brazs', '063', '20000715', 'E5 23 12.6', 'N48 55 17.7 ', '328'),
(43, 'Témoins occulaires', 'Raom', '064', '', '', '', ''),
(44, 'Témoins occulaires', 'Loba', '064', '', '', '', ''),
(45, 'Rising Fall', 'Cornelia Konrads', '065', '20010715', '', '', ''),
(46, 'Les cents ciels plantés', 'Denis Malbos', '066', '20010714', 'E5 26 14.8', 'N48 55 49.1 ', '331'),
(47, 'Neighborhood', 'Bong-Gi Park', '067', '20010714', 'E5 21 06.9', 'N48 55 15.2', '266'),
(48, 'Le jardin ouvrier', 'Annechien Meier', '068', '20010715', 'E5 27 26.6', 'N48 55 26.1 ', '250'),
(49, 'Ecriture de l''arbre', 'Jean Colboc', '069', '20010715', ' E5 26 39.6', 'N48 55 30.4', '341'),
(50, 'Ecriture de l''arbre', 'Philippe Beaudelocque', '069', '20010715', ' E5 26 39.6', 'N48 55 30.4', '341'),
(51, 'Vent des Forêts', 'Daniel Van De Velde', '070', '20010715', '', '', ''),
(52, 'Supper Memory', 'Joan Crous', '071', '20010714', 'E5 25 10.8', 'N48 54 17.9 ', '299'),
(53, 'La source escamotée', 'Aleksey Sorokin', '072', '20010714', 'E5 26 57.4', 'N48 55 17.1 ', '286'),
(54, 'Big Mushroom', 'Baek Sung Kyun', '073', '20010715', 'E5 25 49.7', 'N48 57 00.2', '247'),
(55, 'Oeuvre en chantier', 'Philippe Luyten', '074', '20010714', 'E5 23 31.7', 'N48 53 51.7 ', '289'),
(56, 'Breath the Earth', 'Leonard Aguinaldo', '075', '20010715', 'E5 22 32.0', 'N48 54 53.7', '296'),
(57, 'Les Chevaux du Vent', 'Thierry Amarger', '077', '20020715', '', '', ''),
(58, 'Run to the forest', 'Ali Bates', '078', '20020715', 'E5 24 51.5', 'N48 56 48.4', '274'),
(59, 'Shelter', 'Mat Chivers', '079', '20020715', 'E5 22 25.6', 'N48 54 55.8', '293'),
(60, 'Shelter', 'Yannick Keltz', '079', '20020715', 'E5 22 25.6', 'N48 54 55.8', '293'),
(61, 'The Basillica of the Forest', 'J.Lonne Christiansen', '080', '20020713', 'E5 24 50.9', 'N48 54 50.7 ', '310'),
(62, 'Les Surgissants', 'Awena Cozanet', '081', '20020715', '', '', ''),
(63, 'Barques', 'Thierry Devaux', '082', '20020715', '', '', ''),
(64, 'Chemin de vie', 'Liliana de Vito', '083', '20020712', 'E5 22 12.7', 'N48 55 35.4 ', '290'),
(65, 'Satisfait ou remboursé', 'Marie-Christine Dieudonné', '084', '20020715', '', '', ''),
(66, 'Belle de nuit', 'Erdem Hawa', '085', '20020715', '', '', ''),
(67, 'Dreaming Rebirth of 9 Dragons...', 'Byoung Moon-Tak', '086', '20020713', 'E5 28 16.2', 'N48 56 09.8 ', '304'),
(68, 'Lieu de rêve', 'Walter Piesch', '087', '20020713', 'E5 26 23.4', 'N48 55 41.2 ', '351'),
(69, 'Exode', 'Joël Thiepault', '088', '20020713', 'E5 25 21.4', 'N48 55 31.5 ', '349'),
(70, 'Toboggan', 'Claude Cattelain', '089', '20030715', '', '', ''),
(71, 'La machine à trier les glands', 'Julien David', '090', '20030715', 'E5 26 38.0', 'N48 56 09.7', '330'),
(72, 'Renaissance', 'Nicolas Franchot', '091', '20030715', '', '', ''),
(73, 'Les Ecrins', 'Pierre Jourde', '092', '20030715', 'E5 23 05.8', 'N48 54 33.6', '328'),
(74, 'Berge négative', 'François Lelong', '093', '20030715', 'E5 25 52.0', 'N48 56 07.4', '267'),
(75, 'Ceux qui sont passés par là...', 'Sandrine Lemonnier', '094', '20030715', 'E5 25 11.8', 'N48 55 49.7', '317'),
(76, 'Backbone', 'Agnès Huber', '095', '20030712', 'E5 26 42.9', 'N48 56 35.5 ', '269'),
(77, 'Backbone', 'Valérie Mair', '095', '20030712', 'E5 26 42.9', 'N48 56 35.5 ', '269'),
(78, 'Backbone', 'Herbert Schönegger', '095', '20030712', 'E5 26 42.9', 'N48 56 35.5 ', '269'),
(79, 'La ballade des pendus', 'Emmanuel Perrin', '096', '20030715', 'E5 26 41.4', 'N48 55 11.5', '339'),
(80, 'Petite amie', 'Charlotte Peter', '097', '20030715', 'E5 21 31.5', 'N48 55 23.6', '280'),
(81, 'Le chemin à sensations', 'Fabienne Plateau', '098', '20030715', '', '', ''),
(82, 'Hole-Hill', 'TVA GROUP', '099', '20030712', 'E5 23 27.3', 'N48 55 24.7', '336'),
(83, 'Sans Titre', 'Urs-P. Twellmann', '100', '', '', '', ''),
(84, 'Cubes, Triangles, Squares', 'Strijdom Van Der Merwe', '101', '20030715', '', '', ''),
(85, 'La madre del agua caliente', 'Lesley Yendell', '102', '20030715', 'E5 25 15.3', 'N48 57 01.6', '296'),
(86, 'Morceaux choisis', 'Didier Béquillard', '103', '20040716', 'E5 24 13.1', 'N48 53 32.7', '283'),
(87, 'Les six bornes', 'Clément Borderie', '104', '20040715', '', '', ''),
(88, 'Sans titre', 'Faust Cardinali', '105', '20040716', 'E5 24 49.1', 'N48 56 59.9 ', '332'),
(89, 'Le petit index', 'Aldo Caredda', '106', '20040715', '', '', ''),
(90, 'Mc Farlan Tartare', 'Anthony Freestone', '107', '20040716', 'E5 28 40.0', 'N48 56 01.6', '305'),
(91, '7,5 m² de frontière', 'Aï Kitahara', '108', '20040716', 'E5 22 32.9', 'N48 54 53.2 ', '284'),
(92, 'Entre', 'Benjamin Laurent-Aman', '109', '20040715', '', '', ''),
(93, 'Zone de rétrécissement', 'Benjamin Laurent-Aman', '109bis', '20040715', '', '', ''),
(94, '3 x 4 - 12', 'Miguel-Angel Molina', '110', '20040716', 'E5 23 11.4', 'N48 54 38.9 ', '317'),
(95, 'Evasion', 'Charlie Skubich', '112', '20040716', 'E5 24 34.1', 'N48 54 28.3 ', '301'),
(96, 'Camouflage', 'Heather Deedman', '114', '20050715', '', '', ''),
(97, 'Figura Translata', 'Luc Doerflinger', '115', '20050716', 'E5 25 08.1', 'N48 55 15.3 ', '308'),
(98, 'Tuning landscape n°1', 'Arno Fabre', '116', '20050716', 'E5 23 21.0', 'N48 55 39.5', '333'),
(99, 'Patterns', 'Samuel François', '117', '20050715', '', '', ''),
(100, 'Patterns', 'Samuel François', '117bis', '20050715', '', '', ''),
(101, 'Entrelacs (fatras)', 'François Génot', '118', '20050716', 'E5 21 45.3', 'N48 55 28.9 ', '290'),
(102, 'Entrelacs', 'François Génot', '118bis', '20050716', 'E5 24 50.9', 'N48 56 47.3 ', '274'),
(103, 'Ecosophie', 'Bruno Guiganti', '119', '20050715', 'E5 26 20.2', 'N48 57 05.8', '239'),
(104, 'Dérives écogéographiques', 'Bruno Guiganti', '119bis', '20050715', '', '', ''),
(105, 'Laboratoire II', 'Jean-Louis Hurlin', '120', '20050716', 'E5 21 49.0', 'N48 54 53.3', '276'),
(106, 'Plasticulture I', 'Florent Lamouroux', '121', '20050715', 'E5 27 52.9', 'N48 56 11.0', '309'),
(107, 'Plasticulture II', 'Florent Lamouroux', '121bis', '20050715', '', '', ''),
(108, 'A plat', 'Caroline Molusson', '122', '20050715', 'E5 26 20.9', 'N48 56 09.1 ', '328'),
(109, 'Boiserie', 'Caroline Molusson', '122bis', '20050715', 'E5 23 23.4', 'N48 55 41.8', '324'),
(110, 'L''arbre aux étoiles', 'Justin Morin', '123', '20050716', 'E5 27 18.8', 'N48 56 28.8', '325'),
(111, 'Le chien', 'Justin Morin', '123bis', '20050716', 'E5 27 19.9', 'N48 56 34.2', '320'),
(112, 'Sous le soleil exactement', 'Marion Robin', '124', '20050716', 'E5 24 55.0', 'N48 56 15.5', '312'),
(113, 'Raccourcis', 'Marion Robin', '124bis', '20050716', '', '', ''),
(114, 'Le mât de cocagne et ses engins', 'Jean-François Chevalier', '125', '20070714', 'E5 24 00.0', 'N48 53 32.2', '284'),
(115, 'Le mât de cocagnes et ses engins', 'Jean-François Chevalier', '125', '20060715', 'E5 24 00.0', 'N48 53 32.2 ', '284'),
(116, 'Le mât de cocagnes et ses engins', 'Jean-François Chevalier', '125bis', '20060715', '', '', ''),
(117, 'Manège au repos', 'Rodolphe Huguet', '126', '20060715', '', '', ''),
(118, 'Piège à sieste', 'Rodolphe Huguet', '126bis', '20060715', '', '', ''),
(119, 'Pierre dorée', 'Frédérique Lecerf', '127', '20070714', 'E5 26 38.4', 'N48 55 29.6', '342'),
(120, 'Cachée', 'Lorentino', '128', '20070714', 'E5 26 35.4', 'N48 56 12.1 ', '318'),
(121, 'Cachée', 'Lorentino', '128bis', '20070714', 'E5 23 54.7', 'N48 55 41.6', '306'),
(122, 'Château chamois', 'Adrienne Scherrer', '129', '20060715', 'E5 21 40.8', 'N48 54 58.3', '305'),
(123, 'Parades nuptiales', 'Adrienne Scherrer', '129bis', '20060715', '', '', ''),
(124, 'De mille feux', 'Jean Wary', '130', '20060715', 'E5 23 13.0', 'N48 54 29.3 ', '328'),
(125, 'Paysage multiplié', 'Igor Antic', '131', '20070714', '', '', ''),
(126, 'Paysage multiplié', 'Igor Antic', '131bis', '20070714', '', '', ''),
(127, 'Immigrare I', 'Sanaz Azari', '132', '20070714', 'E5 26 19.4', 'N48 56 15.9 ', '324'),
(128, 'Immigrare II', 'Sanaz Azari', '132bis', '20070714', 'E5 23 52.7', 'N48 55 36.0', '328'),
(129, 'STATION - Je suis toujours vivant', 'Edouard Boyer', '133', '20070714', 'E5 23 55.9', 'N48 55 32.5', '337'),
(130, 'STATION - Je me suis levé', 'Edouard Boyer', '133bis', '20070714', 'E5 23 36.8', 'N48 55 29.5', '332'),
(131, 'BOUM', 'Charlotte Fuillet', '134', '20070715', 'E5 22 04.8', 'N48 54 56.7', '290'),
(132, 'Grrr', 'Charlotte Fuillet', '134bis', '20070715', '', '', ''),
(133, 'Flying Carpet', 'Ibai Hernandorena', '135', '20070714', 'E5 22 54.9', 'N48 54 42.6 ', '298'),
(134, 'Peuple Migrateur', 'Katarina Kudelova', '136', '20070714', 'E5 23 27.3', 'N48 55 11.0', '329'),
(135, 'Racines', 'Katarina Kudelova', '136bis', '20070714', 'E5 23 24.4', 'N48 55 41.5', '327'),
(136, 'Fontaine', 'Fabien Lerat', '137', '20070714', 'E5 25 49.6', 'N48 56 02.9 ', '277'),
(137, 'Tranchée', 'Fabien Lerat', '137bis', '20070714', 'E5 23 25.9', 'N48 55 26.3', '336'),
(138, 'Gradin B', 'Romain Pellas', '138', '20070715', 'E5 29 11.6', 'N48 55 49.2', '253'),
(139, 'Sculpture A', 'Romain Pellas', '138bis', '20070715', '', '', ''),
(140, 'Fleurs en mal du pays', 'Nicolas Pinier', '139', '20070715', 'E5 22 45.1', 'N48 55 30.3', '319'),
(141, 'Le douanier', 'Nicolas Pinier', '139bis', '20070715', '', '', ''),
(142, 'Prendre racine', 'Edouard Sautai', '140', '20070715', '', '', ''),
(143, 'La vie de château (lever de camp)', 'Edouard Sautai', '140bis', '20070715', '', '', ''),
(144, 'Appropriation', 'Herman Steins', '141', '20070715', 'E5 26 35.5', 'N48 55 35.2', '343'),
(145, 'Appropriation kits', 'Herman Steins', '141bis', '20070715', '', '', ''),
(146, '28', 'Simon Bernheim', '142', '20080712', 'E5 23 12.7', 'N48 54 41.9', '314'),
(147, 'Vibrations', 'Dominique Blais', '143', '20080715', 'E5 25 40.9', 'N48 55 01.9', '284'),
(148, 'Das Adlernest', 'Clément Laigle', '143', '20080715', '', '', ''),
(149, 'Le silence des icebergs', 'Laurent Pernot', '145', '20080712', 'E5 21 10.7', 'N48 55 01.6 ', '288'),
(150, 'Nappe', 'Sébastien Rinckel', '146', '20080715', '', '', ''),
(151, 'Palissade', 'Caroline Rivalin', '147', '20080715', 'E5 27 41.7', 'N48 56 18.3', '315'),
(152, 'Le balcon du maire', 'Erick Steinbrecher', '148', '20080715', 'E5 24 31.9', 'N48 56 22.4', '279'),
(153, 'Skin', 'Mehmet Ali Uysal', '149', '20080718', 'E5 20 02.5', 'N48 53 01.5 ', '253'),
(154, 'Volis et chandelles', 'Dominique Blais', '150', '20080718', 'E5 25 07.3', 'N48 55 12.6 ', '299'),
(155, 'Solstice et Systole', 'Alain Domagala', '151', '20090717', 'E5 23 26.9', 'N48 54 45.7 ', '317'),
(156, 'Wikiki', 'Vincent Kohler', '152', '20090717', 'E5 25 15.2', 'N48 57 01.6', '298'),
(157, 'Le silence divisé', 'Christian Lapie', '153', '20090717', 'E5 24 55.7', 'N48 56 14.9', '315'),
(158, 'Hibou', 'Laurent Le Deunff', '154', '20090717', 'E5 23 10.1', 'N48 54 13.9 ', '302'),
(159, 'Le théorème des dictateurs', 'Vincent Mauger', '155', '20090717', 'E5 28 40.0', 'N48 56 01.8 ', '304'),
(160, 'Two thumbs up monument', 'Guillaume Pilet', '156', '20090717', 'E5 23 04.9', 'N48 54 35.0 ', '318'),
(161, 'Miss Panoramique', 'Elsa Sahal', '157', '20090717', 'E5 26 09.2', 'N48 54 56.2 ', '306'),
(162, 'Twisted Cube', 'Karina Bisch', '158', '20100716', 'E5 26 33.7', 'N48 56 48.2', '255'),
(163, 'Saphira', 'Claudia Comte', '159', '20100716', 'E5 24 02.5', 'N48 54 57.1 ', '316'),
(164, 'Aire cellulaire', 'Sébastien Lacroix', '160', '20100710', 'E5 26 38.1', 'N48 55 28.3 ', '342'),
(165, 'Le Voile du Palais', 'Emmanuelle Lainé', '161', '20100716', 'E5 25 53.7', 'N48 55 03.8 ', '279'),
(166, 'Air 23', 'Vincent Lamouroux', '162', '20100716', '', '', ''),
(167, 'La molécule du territoire', 'Evariste Richer', '163', '20100716', 'E5 27 22.9', 'N48 56 30.0 ', '324'),
(168, 'One of those who were too long in the woods', 'Stefan Rinck', '164', '20100716', 'E5 23 25.7', 'N48 55 36.2', '336'),
(169, 'Réenchantement', 'Jean-Luc Verna', '165', '20100716', 'E5 24 35.2', 'N48 54 39.8 ', '335'),
(170, 'La chambre forte', 'Les Frères Chapuisat', '166', '20110715', 'E5 24 31.4', 'N48 56 22.4', '269'),
(171, 'Le Nichoir', 'matali crasset', '167', '20110715', 'E5 26 33.0', 'N48 54 38.3', '317'),
(172, 'Bientôt dans votre village', 'Théo Mercier', '168', '20110715', 'E5 22 18.8', 'N48 53 33.1', '330'),
(173, 'Bientôt dans votre village', 'Christophe Hamaide-Pierson', '168', '20110715', 'E5 22 18.8', 'N48 53 33.1', '330'),
(174, 'Fog Garden #07172 - Moss Garden Nicey-sur-Aire', 'Fujiko Nakaya', '169', '20110715', 'E5 20 03.4', 'N48 53 20.5', '257'),
(175, 'Sidewalk Chalk', 'Mick Peter', '170', '20110715', 'E5 24 14.7', 'N48 53 33.3', '286'),
(176, 'Green Nest', 'Srinivasa Prasad', '171', '20110715', 'E5 27 47.3', 'N48 56 21.1 ', '308'),
(177, 'Sylvia', 'Stéphane Vigny', '173', '20110715', 'E5 22 45.2', 'N48 55 30.3', '313'),
(178, 'Les variables obsolètes', 'Nicolas Boulard', '174', '20120911', 'E5 22 58.2', 'N48 55 22.2 ', '321'),
(179, 'Terrain d''occurrences', 'Jennifer Caubet', '175', '', 'E5 25 41.4', 'N48 54 10.8', '308'),
(180, 'La Noisette', 'matali crasset', '176', '', '', '', ''),
(181, 'Salut pour tous, encore des agapes à moratoire orphique', 'Théodore Fivel', '177', '20120714', 'E5 25 29.7', 'N48 55 47.1', '296'),
(182, 'ATLS', 'Vincent Lamouroux', '178', '', '', '', ''),
(183, 'Quand deux vaisseaux deviennent un', 'Ernesto Sartori', '179', '', 'E5 26 46.2', 'N48 55 14.3', '312'),
(184, 'Turbo Tango', 'Julia Cottin', '180', '', '', '', ''),
(185, 'Tranchée', 'Alexandra Engelfriet', '181', '', '', '', ''),
(186, 'Le Tortueux', 'Ingrid Luche', '182', '', '', '', ''),
(187, 'Chants Silencieux', 'Lionel Sabatté', '183', '', '', '', ''),
(188, 'Globe', 'Maarten V. Eynde', '184', '', '', '', ''),
(189, 'Cartouche', 'Marion Verboom', '185', '', '', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Contenu de la table `roles`
--

INSERT INTO `roles` (`id`, `label`) VALUES
(1, 'Opérant'),
(2, 'Superviseur');

-- --------------------------------------------------------

--
-- Structure de la table `types`
--

CREATE TABLE IF NOT EXISTS `types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `color` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=138 ;

--
-- Contenu de la table `types`
--

INSERT INTO `types` (`id`, `name`, `color`) VALUES
(1, 'Coupe de l''herbe', NULL),
(2, 'Kangoo', 'EBEDD8'),
(3, 'Nettoyage', 'D8DAED'),
(137, 'Zaaz', 'e1e6ff'),
(136, 'Type exemple', '61a1cf'),
(135, 'CCCC', 'ff08e6'),
(134, 'Ertee', 'e1e6ff'),
(133, 'Asdasd2', '86c4f0'),
(132, 'Wqerwewer', 'e1e6ff'),
(131, 'Retertert', 'e1e6ff'),
(130, 'Ertertret', 'e1e6ff'),
(129, 'Eeee', 'e1e6ff'),
(128, 'Werwerwewer', 'e1e6ff'),
(127, 'Gttrrt', 'e1e6ff'),
(126, 'Qwerqwewqeqw', 'e1e6ff'),
(125, 'Asdasdasd', 'e1e6ff'),
(124, 'Werwer2', 'e1e6ff'),
(123, 'Asd', 'e1e6ff'),
(122, 'Asdasdww', 'e1e6ff'),
(121, 'Asdasd', 'e1e6ff'),
(120, 'Coupe 2', 'e1e6ff'),
(119, 'Aaaa', '836de3'),
(118, 'Ljknkjn', '836de3'),
(117, 'Zaza4', 'ff0000'),
(116, 'Ewrwer', 'e1e6ff'),
(115, 'Sdfdsfsd', 'e1e6ff'),
(114, 'Dfs', 'e1e6ff'),
(113, 'Ddfdf', 'e1e6ff'),
(112, 'Werwer', 'e1e6ff'),
(111, 'Zaza3', 'e1e6ff'),
(110, 'Zaza2', 'e1e6ff'),
(109, 'Lijoloinoi', 'e1e6ff'),
(108, 'Lijolij', 'e1e6ff'),
(107, 'Qwe', 'e1e6ff'),
(106, 'Ererer', 'e1e6ff'),
(105, 'Erer', 'e1e6ff'),
(104, 'Zaza', 'e1e6ff');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_last_conx` date DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `mail`, `pass`, `date_last_conx`, `role_id`) VALUES
(1, 'Maxime', 'Kieffer', 'mk@bwets.net', 'hashed', NULL, 2),
(2, 'Grégory', 'Sommeil', 'worker@ventdesforets.org', 'hashed', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `works`
--

CREATE TABLE IF NOT EXISTS `works` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `date_creation` date NOT NULL,
  `date_update` date NOT NULL,
  `desc_emplact` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coords_x` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coords_y` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coords_z` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prio` int(11) NOT NULL,
  `markup` tinyint(1) DEFAULT NULL,
  `question` tinyint(1) DEFAULT NULL,
  `answer` text COLLATE utf8_unicode_ci,
  `frequency_months` int(11) DEFAULT NULL,
  `frequency_weeks` int(11) DEFAULT NULL,
  `frequency_days` int(11) DEFAULT NULL,
  `date_last_done` date DEFAULT NULL,
  `oeuvre_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=84 ;

--
-- Contenu de la table `works`
--

INSERT INTO `works` (`id`, `title`, `description`, `date_creation`, `date_update`, `desc_emplact`, `coords_x`, `coords_y`, `coords_z`, `prio`, `markup`, `question`, `answer`, `frequency_months`, `frequency_weeks`, `frequency_days`, `date_last_done`, `oeuvre_id`) VALUES
(2, 'Cartel V ==> Enlever clous en trop // Mettre ressort !!\r\n', NULL, '2014-01-29', '0000-00-00', NULL, NULL, NULL, NULL, 3, 1, 0, NULL, NULL, NULL, NULL, NULL, 8),
(3, '', NULL, '2014-01-29', '0000-00-00', NULL, NULL, NULL, NULL, 2, 0, 0, NULL, 1, NULL, NULL, NULL, 0),
(4, '', NULL, '2014-01-29', '0000-00-00', NULL, NULL, NULL, NULL, 3, 0, 0, NULL, NULL, 2, NULL, NULL, 0),
(5, 'Ramener petit pont et réparer', NULL, '2014-02-05', '0000-00-00', NULL, NULL, NULL, NULL, 3, 0, 0, NULL, NULL, NULL, NULL, NULL, 174),
(6, 'Réparer/lasurer caisse en bois', NULL, '2014-02-05', '0000-00-00', NULL, NULL, NULL, NULL, 3, 0, 0, NULL, NULL, NULL, NULL, NULL, 174),
(7, 'Inventaire des bambous à changer', NULL, '2014-02-05', '0000-00-00', NULL, NULL, NULL, NULL, 2, 0, 0, NULL, NULL, NULL, NULL, NULL, 174),
(8, 'Enlever filtres dans la station, etc.', 'Nouvelle description', '2014-02-05', '2015-02-18', NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, 4, '2015-02-24', 174),
(9, 'Nettoyer sphère', 'Apporter chiffons, produits d''entretien', '2014-02-05', '0000-00-00', NULL, NULL, NULL, NULL, 3, 0, 0, NULL, NULL, NULL, NULL, NULL, 167),
(10, 'Enlever la pointe dans le cartel', NULL, '0000-00-00', '0000-00-00', NULL, NULL, NULL, NULL, 3, 1, 0, NULL, NULL, NULL, NULL, NULL, 167),
(11, 'Nom du travail', 'Description du travail', '2014-12-01', '0000-00-00', 'Desc emplacement', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(12, 'Nom du travail', 'Description du travail', '2014-12-01', '0000-00-00', 'Desc emplacement', 'E5 21 22.6', 'N48 55 04.5', NULL, 2, NULL, NULL, NULL, NULL, 3, NULL, NULL, 0),
(17, 'Nom du travail 2', 'Desc travail', '2014-12-02', '2014-12-02', 'ewr wer werwer', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, 53, NULL, 2),
(33, 'srdf ', '', '2014-12-02', '2014-12-02', '', NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, '2015-03-06', 0),
(35, 'srdf ', '', '2014-12-02', '2014-12-02', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(65, 'lk', '', '2014-12-02', '2014-12-02', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(66, 'Question 1', 'Desc question', '2014-12-02', '2014-12-02', '', NULL, NULL, NULL, 3, NULL, 1, NULL, NULL, NULL, NULL, NULL, 0),
(67, 'Balisage 1', 'Balisage desc', '2014-12-02', '2014-12-02', '', NULL, NULL, NULL, 3, 1, NULL, NULL, 2, NULL, NULL, '2015-01-04', 0),
(68, 'Normal 1', 'normal desc', '2014-12-02', '2014-12-02', '', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(69, 'oij', '', '2014-12-09', '2014-12-09', '', NULL, NULL, NULL, 2, NULL, 1, NULL, NULL, NULL, NULL, NULL, 2),
(71, 'Nouveau travail', '', '2014-12-10', '2014-12-10', '', NULL, NULL, NULL, 2, NULL, 1, NULL, NULL, 10, NULL, NULL, 147),
(72, 'Un travail pouet pouet', '', '2014-12-17', '2014-12-17', '', NULL, NULL, NULL, 3, 1, NULL, NULL, 3, NULL, NULL, NULL, 33),
(73, 'Un autre travail', '', '2014-12-17', '2014-12-17', '', 'E 5° 25'' 26.48''''', 'N 48° 55'' 11.32''''', NULL, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(74, 'Travail 3', 'Desc', '2015-01-05', '2015-01-05', '', 'E 5° 23'' 34.82''''', 'N 48° 54'' 4.78''''', NULL, 2, NULL, NULL, NULL, 6, NULL, NULL, NULL, 0),
(75, 'Test 222', '', '2015-01-12', '2015-01-12', '', NULL, NULL, NULL, 1, 1, NULL, NULL, NULL, NULL, 56, NULL, 137),
(76, 'TRest', '', '2015-01-12', '2015-01-12', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 137),
(77, 'Test 4', '', '2015-01-12', '2015-01-12', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(78, 'Travail test', 'Lorem ipsum dolor sit amet, et eliptir consec demagos', '2015-01-12', '2015-01-12', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, 3, NULL, NULL, 0),
(79, 'Travail 4', '', '2015-01-12', '2015-01-12', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(81, 'Une question ?', 'Desc\r\n', '2015-01-19', '2015-02-18', '', NULL, NULL, NULL, 3, NULL, 1, NULL, NULL, 2, NULL, '2015-03-06', 117),
(82, 'iuhubjy', 'fvtfvhjtfytvhtf\r\n]yjbybyjf\r\n', '2015-01-25', '2015-01-25', '', 'E 5° 23'' 17.57''''', 'N 48° 55'' 34.01''''', NULL, 3, NULL, NULL, NULL, 6, NULL, NULL, '2015-03-06', 0),
(83, 'Test 123', '', '2015-02-23', '2015-02-23', '', NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 17);

-- --------------------------------------------------------

--
-- Structure de la table `works_types`
--

CREATE TABLE IF NOT EXISTS `works_types` (
  `work_id` int(11) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL,
  PRIMARY KEY (`work_id`,`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `works_types`
--

INSERT INTO `works_types` (`work_id`, `type_id`) VALUES
(0, 0),
(1, 2),
(1, 3),
(3, 2),
(4, 3),
(8, 137),
(9, 3),
(67, 1),
(67, 2),
(67, 3),
(80, 3),
(80, 107),
(80, 108),
(80, 109),
(80, 118),
(80, 126),
(81, 1),
(81, 3),
(81, 107),
(81, 115),
(81, 120),
(81, 122),
(81, 135),
(82, 1),
(82, 135),
(82, 136),
(83, 1),
(83, 3),
(83, 136);

-- --------------------------------------------------------

--
-- Structure de la table `works_workers`
--

CREATE TABLE IF NOT EXISTS `works_workers` (
  `work_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_done` datetime DEFAULT NULL,
  PRIMARY KEY (`work_id`,`user_id`,`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `works_workers`
--

INSERT INTO `works_workers` (`work_id`, `user_id`, `date_added`, `date_done`) VALUES
(75, 2, '2015-03-09 11:09:05', NULL),
(68, 2, '2015-03-06 17:33:13', NULL),
(11, 2, '2015-03-06 17:33:09', '2015-04-03 17:33:00'),
(82, 2, '2015-03-06 11:23:52', NULL),
(81, 2, '2015-03-06 11:33:12', NULL),
(33, 2, '2015-03-06 11:22:53', NULL),
(76, 2, '2015-03-06 11:22:49', NULL),
(77, 2, '2015-03-08 22:25:46', NULL),
(82, 2, '2015-03-06 11:22:42', NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

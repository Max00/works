-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mer 01 Avril 2015 à 13:28
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
-- Structure de la table `additional_workers`
--

CREATE TABLE IF NOT EXISTS `additional_workers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `work_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

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
(2, 'Par erreur', 'Maciej Albrzykowski', '001', '20020715', '5.4185277777778', '48.919666666667', '297'),
(3, 'Utopia : 8215 km dans le 260°', 'Klaus Heid', '002', '19970712', '5.4386388888889', '48.953555555556', '239'),
(4, 'Balance', 'Benoît Tremsal', '003', '19970715', '', '', ''),
(5, 'Mobilier désurbanisé', 'Made', '004', '19970712', '5.3562777777778', '48.917916666667', '296'),
(6, 'Après le vent', 'Tônu Maarand', '009', '19970715', '5.4465833333333', '48.941444444444', '283'),
(7, 'Tüul', 'Ekke Väli', '010', '19970715', '5.4779722222222', '48.933777777778', '306'),
(8, 'Une fenêtre en forêt', 'Hiroshi Teshima', '011', '19970712', '5.42875', '48.894861111111', '305'),
(9, 'Land Field Forest Signs', 'Ernst Amelung', '012', '19970715', '5.4171944444444', '48.935722222222', '330'),
(10, 'Acrobates', 'Tjerrie Verhellen', '013', '19970712', '5.3871666666667', '48.901444444444', '295'),
(11, 'Ombre de lune', 'Sven Domann', '014', '19970715', '5.4057222222222', '48.893055555556', '300'),
(12, 'Gardien', 'Marek Borsanyi', '015', '19970712', '5.4727777777778', '48.925', '231'),
(13, 'L''oeil du Cyclope', 'Nicolas Chenard', '016', '19970712', '5.4244444444444', '48.9295', '303'),
(14, 'L''écho du clocher', 'Brigitte Sillard', '017', '19970715', '5.4066944444444', '48.935027777778', '306'),
(15, 'Taking the deer home', 'Anna Jacquemard', '020', '19970715', '', '', ''),
(16, 'Notre stère qui est aux cieux', 'Françoise Crémel', '021', '19980711', '5.4549166666667', '48.924111111111', '258'),
(17, 'Notre stère qui est aux cieux', 'Stéphanie Buttier', '021', '19980711', '5.4549166666667', '48.924111111111', '258'),
(18, 'Ego', 'Daniel Denise', '024', '19980710', '5.4034166666667', '48.931583333333', '326'),
(19, 'Renaissance', 'Ryszard Litwiniuk', '025', '19980711', '5.3789722222222', '48.913555555556', '289'),
(20, 'Responsabilité', 'Terje Ojaver', '029', '19980711', '5.41475', '48.905444444444', '287'),
(21, 'Le sapin', 'Kazys Venclovas', '033', '19980711', '5.4163055555556', '48.894805555556', '338'),
(22, 'La forêt m''est témoin', 'Christine Beloeil', '034', '19990710', '5.4467222222222', '48.939222222222', '306'),
(23, 'Terre / Ciel', 'Chris Booth', '035', '19990710', '5.4206944444444', '48.922722222222', '332'),
(24, 'La migration du rhinocéros', 'Roger Gaudreau', '036', '19990710', '5.4599166666667', '48.923805555556', '243'),
(25, 'Stairway to the stars', 'Bo Karberg', '041', '19990715', '', '', ''),
(26, 'Dédale', 'Françoise Maire', '043', '19990710', '5.4409722222222', '48.933833333333', '346'),
(27, 'Dédale', 'Michèle Schneider', '043', '19990710', '5.4409722222222', '48.933833333333', '346'),
(28, 'Made by God', 'Tanya Preminger', '045', '19990717', '5.39675', '48.895944444444', '288'),
(29, 'Puzzle', 'Aligna', '048', '20000715', '', '', ''),
(30, 'Etagère', 'Sarunas Arbaciauskas', '049', '20000715', '', '', ''),
(31, 'Mémorial', 'Claudine Bendotti', '050', '20000715', '', '', ''),
(32, 'Mémorial', 'Amélie Chabannes', '050', '20000715', '', '', ''),
(33, 'Passages', 'Jean-Marie Boivin', '051', '20000715', '5.4093888888889', '48.940138888889', '276'),
(34, 'Survivant', 'Horacio Castrejon Calvan', '052', '20000715', '', '', ''),
(35, 'Pinocchio', 'Corinne Jamet', '055', '20000715', '', '', ''),
(36, 'Pinocchio', 'Pierre-Edouard Larivière', '055', '20000715', '', '', ''),
(37, 'L''Envol', 'Michel Kauffman', '056', '20000715', '', '', ''),
(38, 'L''Envol', 'Marie Saurat', '056', '20000715', '', '', ''),
(39, 'Tricolore', 'Richard Künz', '057', '20000715', '', '', ''),
(40, 'Epaves Forestières', 'L.N. Lecheviller', '058', '20000715', '', '', ''),
(41, 'Après', 'Ulrike Rumpenhorst', '061', '20000715', '', '', ''),
(42, 'L''oiseau source', 'Jean-Pierre Brazs', '063', '20000715', '5.3868333333333', '48.921583333333', '328'),
(43, 'Témoins occulaires', 'Raom', '064', '', '', '', ''),
(44, 'Témoins occulaires', 'Loba', '064', '', '', '', ''),
(45, 'Rising Fall', 'Cornelia Konrads', '065', '20010715', '', '', ''),
(46, 'Les cents ciels plantés', 'Denis Malbos', '066', '20010714', '5.4374444444444', '48.930305555556', '331'),
(47, 'Neighborhood', 'Bong-Gi Park', '067', '20010714', '5.3519166666667', '48.920888888889', '266'),
(48, 'Le jardin ouvrier', 'Annechien Meier', '068', '20010715', '5.4573888888889', '48.923916666667', '250'),
(49, 'Ecriture de l''arbre', 'Jean Colboc', '069', '20010715', '0.0072222222222222', '48.925111111111', '341'),
(50, 'Ecriture de l''arbre', 'Philippe Beaudelocque', '069', '20010715', '0.0072222222222222', '48.925111111111', '341'),
(51, 'Vent des Forêts', 'Daniel Van De Velde', '070', '20010715', '', '', ''),
(52, 'Supper Memory', 'Joan Crous', '071', '20010714', '5.4196666666667', '48.904972222222', '299'),
(53, 'La source escamotée', 'Aleksey Sorokin', '072', '20010714', '5.4492777777778', '48.921416666667', '286'),
(54, 'Big Mushroom', 'Baek Sung Kyun', '073', '20010715', '5.4304722222222', '48.950055555556', '247'),
(55, 'Oeuvre en chantier', 'Philippe Luyten', '074', '20010714', '5.3921388888889', '48.897694444444', '289'),
(56, 'Breath the Earth', 'Leonard Aguinaldo', '075', '20010715', '5.3755555555556', '48.914916666667', '296'),
(57, 'Les Chevaux du Vent', 'Thierry Amarger', '077', '20020715', '', '', ''),
(58, 'Run to the forest', 'Ali Bates', '078', '20020715', '5.4143055555556', '48.946777777778', '274'),
(59, 'Shelter', 'Mat Chivers', '079', '20020715', '5.3737777777778', '48.9155', '293'),
(60, 'Shelter', 'Yannick Keltz', '079', '20020715', '5.3737777777778', '48.9155', '293'),
(61, 'The Basillica of the Forest', 'J.Lonne Christiansen', '080', '20020713', '5.4141388888889', '48.914083333333', '310'),
(62, 'Les Surgissants', 'Awena Cozanet', '081', '20020715', '', '', ''),
(63, 'Barques', 'Thierry Devaux', '082', '20020715', '', '', ''),
(64, 'Chemin de vie', 'Liliana de Vito', '083', '20020712', '5.3701944444444', '48.9265', '290'),
(65, 'Satisfait ou remboursé', 'Marie-Christine Dieudonné', '084', '20020715', '', '', ''),
(66, 'Belle de nuit', 'Erdem Hawa', '085', '20020715', '', '', ''),
(67, 'Dreaming Rebirth of 9 Dragons...', 'Byoung Moon-Tak', '086', '20020713', '5.4711666666667', '48.936055555556', '304'),
(68, 'Lieu de rêve', 'Walter Piesch', '087', '20020713', '5.4398333333333', '48.928111111111', '351'),
(69, 'Exode', 'Joël Thiepault', '088', '20020713', '5.4226111111111', '48.925416666667', '349'),
(70, 'Toboggan', 'Claude Cattelain', '089', '20030715', '', '', ''),
(71, 'La machine à trier les glands', 'Julien David', '090', '20030715', '5.4438888888889', '48.936027777778', '330'),
(72, 'Renaissance', 'Nicolas Franchot', '091', '20030715', '', '', ''),
(73, 'Les Ecrins', 'Pierre Jourde', '092', '20030715', '5.3849444444444', '48.909333333333', '328'),
(74, 'Berge négative', 'François Lelong', '093', '20030715', '5.4311111111111', '48.935388888889', '267'),
(75, 'Ceux qui sont passés par là...', 'Sandrine Lemonnier', '094', '20030715', '5.4199444444444', '48.930472222222', '317'),
(76, 'Backbone', 'Agnès Huber', '095', '20030712', '5.44525', '48.943194444444', '269'),
(77, 'Backbone', 'Valérie Mair', '095', '20030712', '5.44525', '48.943194444444', '269'),
(78, 'Backbone', 'Herbert Schönegger', '095', '20030712', '5.44525', '48.943194444444', '269'),
(79, 'La ballade des pendus', 'Emmanuel Perrin', '096', '20030715', '5.4448333333333', '48.919861111111', '339'),
(80, 'Petite amie', 'Charlotte Peter', '097', '20030715', '5.35875', '48.923222222222', '280'),
(81, 'Le chemin à sensations', 'Fabienne Plateau', '098', '20030715', '', '', ''),
(82, 'Hole-Hill', 'TVA GROUP', '099', '20030712', '5.3909166666667', '48.923527777778', '336'),
(83, 'Sans Titre', 'Urs-P. Twellmann', '100', '', '', '', ''),
(84, 'Cubes, Triangles, Squares', 'Strijdom Van Der Merwe', '101', '20030715', '', '', ''),
(85, 'La madre del agua caliente', 'Lesley Yendell', '102', '20030715', '5.4209166666667', '48.950444444444', '296'),
(86, 'Morceaux choisis', 'Didier Béquillard', '103', '20040716', '5.4036388888889', '48.892416666667', '283'),
(87, 'Les six bornes', 'Clément Borderie', '104', '20040715', '', '', ''),
(88, 'Sans titre', 'Faust Cardinali', '105', '20040716', '5.4136388888889', '48.949972222222', '332'),
(89, 'Le petit index', 'Aldo Caredda', '106', '20040715', '', '', ''),
(90, 'Mc Farlan Tartare', 'Anthony Freestone', '107', '20040716', '5.4777777777778', '48.933777777778', '305'),
(91, '7,5 m² de frontière', 'Aï Kitahara', '108', '20040716', '5.3758055555556', '48.914777777778', '284'),
(92, 'Entre', 'Benjamin Laurent-Aman', '109', '20040715', '', '', ''),
(93, 'Zone de rétrécissement', 'Benjamin Laurent-Aman', '109bis', '20040715', '', '', ''),
(94, '3 x 4 - 12', 'Miguel-Angel Molina', '110', '20040716', '5.3865', '48.910805555556', '317'),
(95, 'Evasion', 'Charlie Skubich', '112', '20040716', '5.4094722222222', '48.907861111111', '301'),
(96, 'Camouflage', 'Heather Deedman', '114', '20050715', '', '', ''),
(97, 'Figura Translata', 'Luc Doerflinger', '115', '20050716', '5.4189166666667', '48.920916666667', '308'),
(98, 'Tuning landscape n°1', 'Arno Fabre', '116', '20050716', '5.3891666666667', '48.927638888889', '333'),
(99, 'Patterns', 'Samuel François', '117', '20050715', '', '', ''),
(100, 'Patterns', 'Samuel François', '117bis', '20050715', '', '', ''),
(101, 'Entrelacs (fatras)', 'François Génot', '118', '20050716', '5.3625833333333', '48.924694444444', '290'),
(102, 'Entrelacs', 'François Génot', '118bis', '20050716', '5.4141388888889', '48.946472222222', '274'),
(103, 'Ecosophie', 'Bruno Guiganti', '119', '20050715', '5.4389444444444', '48.951611111111', '239'),
(104, 'Dérives écogéographiques', 'Bruno Guiganti', '119bis', '20050715', '', '', ''),
(105, 'Laboratoire II', 'Jean-Louis Hurlin', '120', '20050716', '5.3636111111111', '48.914805555556', '276'),
(106, 'Plasticulture I', 'Florent Lamouroux', '121', '20050715', '5.4646944444444', '48.936388888889', '309'),
(107, 'Plasticulture II', 'Florent Lamouroux', '121bis', '20050715', '', '', ''),
(108, 'A plat', 'Caroline Molusson', '122', '20050715', '5.4391388888889', '48.935861111111', '328'),
(109, 'Boiserie', 'Caroline Molusson', '122bis', '20050715', '5.3898333333333', '48.928277777778', '324'),
(110, 'L''arbre aux étoiles', 'Justin Morin', '123', '20050716', '5.4552222222222', '48.941333333333', '325'),
(111, 'Le chien', 'Justin Morin', '123bis', '20050716', '5.4555277777778', '48.942833333333', '320'),
(112, 'Sous le soleil exactement', 'Marion Robin', '124', '20050716', '5.4152777777778', '48.937638888889', '312'),
(113, 'Raccourcis', 'Marion Robin', '124bis', '20050716', '', '', ''),
(114, 'Le mât de cocagne et ses engins', 'Jean-François Chevalier', '125', '20070714', '5.4', '48.892277777778', '284'),
(115, 'Le mât de cocagnes et ses engins', 'Jean-François Chevalier', '125', '20060715', '5.4', '48.892277777778', '284'),
(116, 'Le mât de cocagnes et ses engins', 'Jean-François Chevalier', '125bis', '20060715', '', '', ''),
(117, 'Manège au repos', 'Rodolphe Huguet', '126', '20060715', '', '', ''),
(118, 'Piège à sieste', 'Rodolphe Huguet', '126bis', '20060715', '', '', ''),
(119, 'Pierre dorée', 'Frédérique Lecerf', '127', '20070714', '5.444', '48.924888888889', '342'),
(120, 'Cachée', 'Lorentino', '128', '20070714', '5.4431666666667', '48.936694444444', '318'),
(121, 'Cachée', 'Lorentino', '128bis', '20070714', '5.3985277777778', '48.928222222222', '306'),
(122, 'Château chamois', 'Adrienne Scherrer', '129', '20060715', '5.3613333333333', '48.916194444444', '305'),
(123, 'Parades nuptiales', 'Adrienne Scherrer', '129bis', '20060715', '', '', ''),
(124, 'De mille feux', 'Jean Wary', '130', '20060715', '5.3869444444444', '48.908138888889', '328'),
(125, 'Paysage multiplié', 'Igor Antic', '131', '20070714', '', '', ''),
(126, 'Paysage multiplié', 'Igor Antic', '131bis', '20070714', '', '', ''),
(127, 'Immigrare I', 'Sanaz Azari', '132', '20070714', '5.4387222222222', '48.93775', '324'),
(128, 'Immigrare II', 'Sanaz Azari', '132bis', '20070714', '5.3979722222222', '48.926666666667', '328'),
(129, 'STATION - Je suis toujours vivant', 'Edouard Boyer', '133', '20070714', '5.3988611111111', '48.925694444444', '337'),
(130, 'STATION - Je me suis levé', 'Edouard Boyer', '133bis', '20070714', '5.3935555555556', '48.924861111111', '332'),
(131, 'BOUM', 'Charlotte Fuillet', '134', '20070715', '5.368', '48.91575', '290'),
(132, 'Grrr', 'Charlotte Fuillet', '134bis', '20070715', '', '', ''),
(133, 'Flying Carpet', 'Ibai Hernandorena', '135', '20070714', '5.3819166666667', '48.911833333333', '298'),
(134, 'Peuple Migrateur', 'Katarina Kudelova', '136', '20070714', '5.3909166666667', '48.919722222222', '329'),
(135, 'Racines', 'Katarina Kudelova', '136bis', '20070714', '5.3901111111111', '48.928194444444', '327'),
(136, 'Fontaine', 'Fabien Lerat', '137', '20070714', '5.4304444444444', '48.934138888889', '277'),
(137, 'Tranchée', 'Fabien Lerat', '137bis', '20070714', '5.3905277777778', '48.923972222222', '336'),
(138, 'Gradin B', 'Romain Pellas', '138', '20070715', '5.4865555555556', '48.930333333333', '253'),
(139, 'Sculpture A', 'Romain Pellas', '138bis', '20070715', '', '', ''),
(140, 'Fleurs en mal du pays', 'Nicolas Pinier', '139', '20070715', '5.3791944444444', '48.925083333333', '319'),
(141, 'Le douanier', 'Nicolas Pinier', '139bis', '20070715', '', '', ''),
(142, 'Prendre racine', 'Edouard Sautai', '140', '20070715', '', '', ''),
(143, 'La vie de château (lever de camp)', 'Edouard Sautai', '140bis', '20070715', '', '', ''),
(144, 'Appropriation', 'Herman Steins', '141', '20070715', '5.4431944444444', '48.926444444444', '343'),
(145, 'Appropriation kits', 'Herman Steins', '141bis', '20070715', '', '', ''),
(146, '28', 'Simon Bernheim', '142', '20080712', '5.3868611111111', '48.911638888889', '314'),
(147, 'Vibrations', 'Dominique Blais', '143', '20080715', '5.4280277777778', '48.917194444444', '284'),
(148, 'Das Adlernest', 'Clément Laigle', '143', '20080715', '', '', ''),
(149, 'Le silence des icebergs', 'Laurent Pernot', '145', '20080712', '5.3529722222222', '48.917111111111', '288'),
(150, 'Nappe', 'Sébastien Rinckel', '146', '20080715', '', '', ''),
(151, 'Palissade', 'Caroline Rivalin', '147', '20080715', '5.4615833333333', '48.938416666667', '315'),
(152, 'Le balcon du maire', 'Erick Steinbrecher', '148', '20080715', '5.4088611111111', '48.939555555556', '279'),
(153, 'Skin', 'Mehmet Ali Uysal', '149', '20080718', '5.3340277777778', '48.88375', '253'),
(154, 'Volis et chandelles', 'Dominique Blais', '150', '20080718', '5.4186944444444', '48.920166666667', '299'),
(155, 'Solstice et Systole', 'Alain Domagala', '151', '20090717', '5.3908055555556', '48.912694444444', '317'),
(156, 'Wikiki', 'Vincent Kohler', '152', '20090717', '5.4208888888889', '48.950444444444', '298'),
(157, 'Le silence divisé', 'Christian Lapie', '153', '20090717', '5.4154722222222', '48.937472222222', '315'),
(158, 'Hibou', 'Laurent Le Deunff', '154', '20090717', '5.3861388888889', '48.903861111111', '302'),
(159, 'Le théorème des dictateurs', 'Vincent Mauger', '155', '20090717', '5.4777777777778', '48.933833333333', '304'),
(160, 'Two thumbs up monument', 'Guillaume Pilet', '156', '20090717', '5.3846944444444', '48.909722222222', '318'),
(161, 'Miss Panoramique', 'Elsa Sahal', '157', '20090717', '5.4358888888889', '48.915611111111', '306'),
(162, 'Twisted Cube', 'Karina Bisch', '158', '20100716', '5.4426944444444', '48.946722222222', '255'),
(163, 'Saphira', 'Claudia Comte', '159', '20100716', '5.4006944444444', '48.915861111111', '316'),
(164, 'Aire cellulaire', 'Sébastien Lacroix', '160', '20100710', '5.4439166666667', '48.924527777778', '342'),
(165, 'Le Voile du Palais', 'Emmanuelle Lainé', '161', '20100716', '5.4315833333333', '48.917722222222', '279'),
(166, 'Air 23', 'Vincent Lamouroux', '162', '20100716', '', '', ''),
(167, 'La molécule du territoire', 'Evariste Richer', '163', '20100716', '5.4563611111111', '48.941666666667', '324'),
(168, 'One of those who were too long in the woods', 'Stefan Rinck', '164', '20100716', '5.3904722222222', '48.926722222222', '336'),
(169, 'Réenchantement', 'Jean-Luc Verna', '165', '20100716', '5.4097777777778', '48.911055555556', '335'),
(170, 'La chambre forte', 'Les Frères Chapuisat', '166', '20110715', '5.4087222222222', '48.939555555556', '269'),
(171, 'Le Nichoir', 'matali crasset', '167', '20110715', '5.4425', '48.910638888889', '317'),
(172, 'Bientôt dans votre village', 'Théo Mercier', '168', '20110715', '5.3718888888889', '48.892527777778', '330'),
(173, 'Bientôt dans votre village', 'Christophe Hamaide-Pierson', '168', '20110715', '5.3718888888889', '48.892527777778', '330'),
(174, 'Fog Garden #07172 - Moss Garden Nicey-sur-Aire', 'Fujiko Nakaya', '169', '20110715', '5.3342777777778', '48.889027777778', '257'),
(175, 'Sidewalk Chalk', 'Mick Peter', '170', '20110715', '5.4040833333333', '48.892583333333', '286'),
(176, 'Green Nest', 'Srinivasa Prasad', '171', '20110715', '5.4631388888889', '48.939194444444', '308'),
(177, 'Sylvia', 'Stéphane Vigny', '173', '20110715', '5.3792222222222', '48.925083333333', '313'),
(178, 'Les variables obsolètes', 'Nicolas Boulard', '174', '20120911', '5.3828333333333', '48.922833333333', '321'),
(179, 'Terrain d''occurrences', 'Jennifer Caubet', '175', '', '5.4281666666667', '48.903', '308'),
(180, 'La Noisette', 'matali crasset', '176', '', '', '', ''),
(181, 'Salut pour tous, encore des agapes à moratoire orphique', 'Théodore Fivel', '177', '20120714', '5.4249166666667', '48.92975', '296'),
(182, 'ATLS', 'Vincent Lamouroux', '178', '', '', '', ''),
(183, 'Quand deux vaisseaux deviennent un', 'Ernesto Sartori', '179', '', '5.4461666666667', '48.920638888889', '312'),
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Contenu de la table `types`
--

INSERT INTO `types` (`id`, `name`, `color`) VALUES
(1, 'Peinture', 'e1e6ff'),
(2, 'Soudure', 'e1e6ff');

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
  `tools` text COLLATE utf8_unicode_ci,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

--
-- Contenu de la table `works`
--

INSERT INTO `works` (`id`, `title`, `description`, `tools`, `date_creation`, `date_update`, `desc_emplact`, `coords_x`, `coords_y`, `coords_z`, `prio`, `markup`, `question`, `answer`, `frequency_months`, `frequency_weeks`, `frequency_days`, `date_last_done`, `oeuvre_id`) VALUES
(1, 'Écorcer arbre Damien Roubaix', '', 'Pioche, marteau', '2015-03-11', '2015-04-01', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 'Restauration eau bâtiment Ehren', '', NULL, '2015-03-11', '2015-03-11', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 'Repeindre', '', NULL, '2015-03-11', '2015-03-11', '', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3),
(4, 'Recoller le miroir', '', NULL, '2015-03-11', '2015-03-11', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 136),
(5, 'Nettoyer et peindre plaques anti-rouille + soudure', 'Soudure: RENNESSON \r\nManitout: COHEN', NULL, '2015-03-11', '2015-03-11', '', NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL, NULL, '2015-03-23', 157),
(6, 'Réparer les attaches des deux lettres et les repositionner', '', 'Choisir une sangle de couleur discrète (pas bleue ni rouge)', '2015-03-11', '2015-03-11', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 120),
(7, 'Changer support contre-plaqué / rapporter une lettre au bureau pour évaluer la restauration', '', 'Sangle et échelle', '2015-03-11', '2015-03-11', '', NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, '2015-03-11', 121);

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
(3, 1),
(5, 1),
(5, 2);

-- --------------------------------------------------------

--
-- Structure de la table `works_workers`
--

CREATE TABLE IF NOT EXISTS `works_workers` (
  `work_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date_added` datetime DEFAULT NULL,
  `date_done` datetime DEFAULT NULL,
  PRIMARY KEY (`work_id`,`user_id`,`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `works_workers`
--

INSERT INTO `works_workers` (`work_id`, `user_id`, `date_added`, `date_done`) VALUES
(5, 2, '2015-03-23 11:25:16', NULL),
(6, 2, '2015-03-23 11:25:21', NULL),
(3, 2, '2015-03-31 16:29:28', NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE VIEW works_with_coords AS
    SELECT w.id, w.title AS work_title, o.title AS oeuvre_title, CONVERT(COALESCE(w.coords_x, o.coords_x) USING utf8) as coords_x, CONVERT(COALESCE(w.coords_y, o.coords_y) USING utf8) as coords_y
    FROM works AS w
    JOIN oeuvres AS o ON o.id = w.oeuvre_id
    UNION
    SELECT w.id, w.title AS work_title, "" AS oeuvre_title, CONVERT(w.coords_x USING utf8) as coords_x, CONVERT(w.coords_y USING utf8) as coords_y
    FROM works AS w
    WHERE w.coords_x IS NOT NULL
;
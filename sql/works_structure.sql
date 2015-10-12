-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Lun 12 Octobre 2015 à 12:10
-- Version du serveur :  5.6.26
-- Version de PHP :  5.6.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `works`
--

-- --------------------------------------------------------

--
-- Structure de la table `additional_workers`
--

CREATE TABLE IF NOT EXISTS `additional_workers` (
  `id` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `work_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `oeuvres`
--

CREATE TABLE IF NOT EXISTS `oeuvres` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `date_realisation` varchar(8) DEFAULT NULL,
  `coords_x` varchar(20) DEFAULT NULL,
  `coords_y` varchar(20) DEFAULT NULL,
  `coords_z` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL,
  `label` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `types`
--

CREATE TABLE IF NOT EXISTS `types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(6) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lname` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_last_conx` date DEFAULT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `works`
--

CREATE TABLE IF NOT EXISTS `works` (
  `id` int(11) NOT NULL,
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
  `term` int(11) DEFAULT NULL,
  `term_set_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `works_types`
--

CREATE TABLE IF NOT EXISTS `works_types` (
  `work_id` int(11) NOT NULL DEFAULT '0',
  `type_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `works_with_coords`
--
CREATE TABLE IF NOT EXISTS `works_with_coords` (
`id` int(11)
,`work_title` varchar(255)
,`oeuvre_title` varchar(255)
,`coords_x` varchar(20)
,`coords_y` varchar(20)
);

-- --------------------------------------------------------

--
-- Structure de la table `works_workers`
--

CREATE TABLE IF NOT EXISTS `works_workers` (
  `work_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_done` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la vue `works_with_coords`
--
DROP TABLE IF EXISTS `works_with_coords`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `works_with_coords` AS select `w`.`id` AS `id`,`w`.`title` AS `work_title`,`o`.`title` AS `oeuvre_title`,convert(coalesce(`w`.`coords_x`,`o`.`coords_x`) using utf8) AS `coords_x`,convert(coalesce(`w`.`coords_y`,`o`.`coords_y`) using utf8) AS `coords_y` from (`works` `w` join `oeuvres` `o` on((`o`.`id` = `w`.`oeuvre_id`))) union select `w`.`id` AS `id`,`w`.`title` AS `work_title`,'' AS `oeuvre_title`,convert(`w`.`coords_x` using utf8) AS `coords_x`,convert(`w`.`coords_y` using utf8) AS `coords_y` from `works` `w` where (`w`.`coords_x` is not null);

--
-- Index pour les tables exportées
--

--
-- Index pour la table `additional_workers`
--
ALTER TABLE `additional_workers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `oeuvres`
--
ALTER TABLE `oeuvres`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `works`
--
ALTER TABLE `works`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `works_types`
--
ALTER TABLE `works_types`
  ADD PRIMARY KEY (`work_id`,`type_id`);

--
-- Index pour la table `works_workers`
--
ALTER TABLE `works_workers`
  ADD PRIMARY KEY (`work_id`,`user_id`,`date_added`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `additional_workers`
--
ALTER TABLE `additional_workers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `oeuvres`
--
ALTER TABLE `oeuvres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `works`
--
ALTER TABLE `works`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

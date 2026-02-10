-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 04 fév. 2026 à 20:01
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `événement`
--

-- --------------------------------------------------------

--
-- Structure de la table `bracket`
--

CREATE TABLE `bracket` (
  `idBracket` int(11) NOT NULL,
  `typeBracket` enum('single elimination','double elimination') NOT NULL,
  `nombreTours` int(11) NOT NULL,
  `statut` enum('en attente','en cours','terminé') NOT NULL,
  `idEvenement` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipe`
--

CREATE TABLE `equipe` (
  `idEquipe` int(11) NOT NULL,
  `nomEquipe` varchar(100) NOT NULL,
  `tag` varchar(10) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `dateCreation` date DEFAULT NULL,
  `niveau` enum('amateur','semi-pro','pro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `idEvenement` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `jeu` varchar(50) NOT NULL,
  `typeEvenement` enum('online','offline') NOT NULL,
  `dateDebut` date NOT NULL,
  `dateFin` date NOT NULL,
  `statut` enum('prévu','en cours','terminé') NOT NULL,
  `regles` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `match`
--

CREATE TABLE `match` (
  `idMatch` int(11) NOT NULL,
  `dateMatch` datetime NOT NULL,
  `tour` int(11) NOT NULL,
  `scoreEquipeA` int(11) DEFAULT 0,
  `scoreEquipeB` int(11) DEFAULT 0,
  `statut` enum('à venir','en cours','terminé') NOT NULL,
  `equipeA_id` int(11) NOT NULL,
  `equipeB_id` int(11) NOT NULL,
  `idBracket` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bracket`
--
ALTER TABLE `bracket`
  ADD PRIMARY KEY (`idBracket`),
  ADD UNIQUE KEY `idEvenement` (`idEvenement`);

--
-- Index pour la table `equipe`
--
ALTER TABLE `equipe`
  ADD PRIMARY KEY (`idEquipe`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`idEvenement`);

--
-- Index pour la table `match`
--
ALTER TABLE `match`
  ADD PRIMARY KEY (`idMatch`),
  ADD KEY `fk_match_equipeA` (`equipeA_id`),
  ADD KEY `fk_match_equipeB` (`equipeB_id`),
  ADD KEY `fk_match_bracket` (`idBracket`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `bracket`
--
ALTER TABLE `bracket`
  MODIFY `idBracket` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `equipe`
--
ALTER TABLE `equipe`
  MODIFY `idEquipe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `idEvenement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `match`
--
ALTER TABLE `match`
  MODIFY `idMatch` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `bracket`
--
ALTER TABLE `bracket`
  ADD CONSTRAINT `fk_bracket_evenement` FOREIGN KEY (`idEvenement`) REFERENCES `evenement` (`idEvenement`) ON DELETE CASCADE;

--
-- Contraintes pour la table `match`
--
ALTER TABLE `match`
  ADD CONSTRAINT `fk_match_bracket` FOREIGN KEY (`idBracket`) REFERENCES `bracket` (`idBracket`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_match_equipeA` FOREIGN KEY (`equipeA_id`) REFERENCES `equipe` (`idEquipe`),
  ADD CONSTRAINT `fk_match_equipeB` FOREIGN KEY (`equipeB_id`) REFERENCES `equipe` (`idEquipe`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

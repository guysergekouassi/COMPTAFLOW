-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 20 août 2025 à 19:31
-- Version du serveur : 10.11.11-MariaDB-deb12
-- Version de PHP : 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dckno2255957_3fpeyb`
--

-- --------------------------------------------------------

--
-- Structure de la table `grand_livres_tiers`
--

CREATE TABLE `grand_livres_tiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `plan_tiers_id_1` bigint(20) UNSIGNED NOT NULL,
  `plan_tiers_id_2` bigint(20) UNSIGNED NOT NULL,
  `grand_livre_tiers` varchar(191) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Index pour les tables déchargées
--

--
-- Index pour la table `grand_livres_tiers`
--
ALTER TABLE `grand_livres_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grand_livres_tiers_user_id_foreign` (`user_id`),
  ADD KEY `grand_livres_tiers_company_id_foreign` (`company_id`),
  ADD KEY `grand_livres_tiers_plan_tiers_id_1_foreign` (`plan_tiers_id_1`),
  ADD KEY `grand_livres_tiers_plan_tiers_id_2_foreign` (`plan_tiers_id_2`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `grand_livres_tiers`
--
ALTER TABLE `grand_livres_tiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

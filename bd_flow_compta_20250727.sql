-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 28 juil. 2025 à 01:33
-- Version du serveur : 8.0.20
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bd_flow_compta`
--

-- --------------------------------------------------------

--
-- Structure de la table `balances`
--

CREATE TABLE `balances` (
  `id` bigint UNSIGNED NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `plan_comptable_id_1` bigint UNSIGNED NOT NULL,
  `plan_comptable_id_2` bigint UNSIGNED NOT NULL,
  `balance` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `code_journals`
--

CREATE TABLE `code_journals` (
  `id` bigint UNSIGNED NOT NULL,
  `code_journal` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intitule` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `traitement_analytique` tinyint(1) NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compte_de_contrepartie` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rapprochement_sur` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `compte_de_tresorerie` bigint UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `companies`
--

CREATE TABLE `companies` (
  `id` bigint UNSIGNED NOT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `juridique_form` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `social_capital` decimal(15,2) NOT NULL,
  `adresse` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_postal` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_adresse` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identification_TVA` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `activity`, `juridique_form`, `social_capital`, `adresse`, `code_postal`, `city`, `country`, `phone_number`, `email_adresse`, `identification_TVA`, `is_blocked`, `created_at`, `updated_at`) VALUES
(1, 'Société Ivoirienne de Services', 'Services informatiques et conseils', 'SARL', 5000000.00, 'Abidjan Plateau, Rue des Jardins', '01 BP 1234', 'Abidjan', 'Côte d\'Ivoire', '+225 27 21 00 00 00', 'contact@sis.ci', 'CI1234567890', 0, '2025-06-24 22:19:55', '2025-07-21 22:06:03');

-- --------------------------------------------------------

--
-- Structure de la table `ecriture_comptables`
--

CREATE TABLE `ecriture_comptables` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `n_saisie` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_operation` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_piece` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_comptable_id` bigint UNSIGNED NOT NULL,
  `plan_tiers_id` bigint UNSIGNED NOT NULL,
  `plan_analytique` tinyint(1) NOT NULL,
  `code_journal_id` bigint UNSIGNED NOT NULL,
  `exercices_comptables_id` bigint UNSIGNED NOT NULL,
  `journaux_saisis_id` bigint UNSIGNED NOT NULL,
  `debit` decimal(15,2) DEFAULT NULL,
  `credit` decimal(15,2) DEFAULT NULL,
  `piece_justificatif` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `exercices_comptables`
--

CREATE TABLE `exercices_comptables` (
  `id` bigint UNSIGNED NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `intitule` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre_journaux_saisis` int UNSIGNED NOT NULL DEFAULT '0',
  `cloturer` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grand_livres`
--

CREATE TABLE `grand_livres` (
  `id` bigint UNSIGNED NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `plan_comptable_id_1` bigint UNSIGNED NOT NULL,
  `plan_comptable_id_2` bigint UNSIGNED NOT NULL,
  `grand_livre` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `journaux_saisis`
--

CREATE TABLE `journaux_saisis` (
  `id` bigint UNSIGNED NOT NULL,
  `annee` int NOT NULL,
  `mois` int NOT NULL,
  `exercices_comptables_id` bigint UNSIGNED NOT NULL,
  `code_journals_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_06_24_192509_create_companies_table', 1),
(4, '2025_06_24_192549_create_users_table', 1),
(5, '2025_06_25_183457_create_plan_comptables_table', 2),
(6, '2025_06_25_215544_create_plan_tiers_table', 3),
(7, '2025_06_26_184708_create_code_journals_table', 4),
(8, '2025_06_27_212521_create_ecriture_comptables_table', 5),
(9, '2025_06_30_205147_add_habilitations_to_users_table', 6),
(10, '2025_06_30_205416_add_habilitations_to_users_table', 7),
(11, '2025_07_03_182015_modify_classe_nullable_in_plan_comptables_table', 8),
(12, '2025_07_03_200009_remove_annee_mois_from_code_journals_table', 9),
(13, '2025_07_03_202758_alter_compte_de_tresorerie_in_code_journals_table', 10),
(14, '2025_07_03_211111_create_exercices_comptables_table', 11),
(15, '2025_07_03_214449_add_nombre_journaux_saisis_to_exercices_comptables_table', 12),
(16, '2025_07_03_214603_create_journaux_saisis_table', 13),
(17, '2025_07_04_203311_add_exercice_and_journal_to_ecriture_comptables_table', 14),
(18, '2025_07_08_162833_add_cloturer_to_exercices_comptables_table', 15),
(19, '2025_07_10_202233_create_grand_livres_table', 16),
(20, '2025_07_10_202233_create_balances_table', 17),
(21, '2025_07_15_182955_update_plan_comptables_table', 18),
(22, '2025_07_17_204422_update_grand_livres_replace_code_journal_with_plan_comptable', 19),
(23, '2025_07_26_203904_update_grand_livres_add_second_plan_comptable', 20),
(24, '2025_07_27_095414_update_balances_table_for_plan_comptables', 21);

-- --------------------------------------------------------

--
-- Structure de la table `plan_comptables`
--

CREATE TABLE `plan_comptables` (
  `id` bigint UNSIGNED NOT NULL,
  `numero_de_compte` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `intitule` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adding_strategy` enum('auto','manuel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manuel',
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `plan_tiers`
--

CREATE TABLE `plan_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `numero_de_tiers` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `compte_general` bigint UNSIGNED NOT NULL,
  `intitule` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_de_tiers` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `company_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_adresse` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('comptable','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT '0',
  `company_id` bigint UNSIGNED NOT NULL,
  `habilitations` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `name`, `last_name`, `email_adresse`, `password`, `role`, `is_online`, `company_id`, `habilitations`, `created_at`, `updated_at`) VALUES
(1, 'tanoh', 'eliezer', 'tanoh@gmail.com', '$2y$12$Fynyhky1.fXbJ.gUdvxOx.eT71NXaGTGNv.u8c0bFecY4lBsZPYXm', 'admin', 1, 1, '{\"balance\": true, \"journaux\": true, \"dashboard\": true, \"parametre\": true, \"plan_tiers\": true, \"grand_livre\": true, \"plan_comptable\": true, \"fichier_joindre\": true, \"etats_financiers\": true}', '2025-06-24 20:23:49', '2025-07-19 19:28:55'),
(4, 'Tchoman', 'josee', 'josee@gmail.com', '$2y$12$0tgfcngsI2JRSGX4A1e.3e5MTt7dvL6tws9WEJZWbEr453Y/0GA0O', 'comptable', 0, 1, '{\"balance\": false, \"journaux\": true, \"dashboard\": true, \"parametre\": false, \"plan_tiers\": false, \"grand_livre\": false, \"plan_comptable\": false, \"fichier_joindre\": false, \"etats_financiers\": true}', '2025-06-30 05:36:12', '2025-07-01 20:09:25'),
(5, 'koffi', 'michael', 'koffi@gmail.com', '$2y$12$mvS4Ed.PlP.SWNC6LlFvxOR4YK5CRgOmwWPmSu0h8X1DTTGZgN5du', 'comptable', 0, 1, '{\"balance\": false, \"journaux\": true, \"dashboard\": true, \"parametre\": false, \"plan_tiers\": true, \"grand_livre\": false, \"plan_comptable\": true, \"fichier_joindre\": false, \"etats_financiers\": false}', '2025-06-30 21:02:58', '2025-07-01 20:05:44'),
(6, 'michael', 'michael', 'michael@gmail.com', '$2y$12$n/gAoOiam69Rs5ooQbDbHeVJDAvSDNEseBqSIcXH7jyinp9ux1mzO', 'admin', 0, 1, '{\"balance\": true, \"journaux\": true, \"dashboard\": true, \"parametre\": true, \"plan_tiers\": true, \"grand_livre\": true, \"plan_comptable\": true, \"fichier_joindre\": true, \"etats_financiers\": true}', '2025-07-01 20:12:10', '2025-07-01 20:12:10');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `balances`
--
ALTER TABLE `balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balances_user_id_foreign` (`user_id`),
  ADD KEY `balances_company_id_foreign` (`company_id`),
  ADD KEY `balances_plan_comptable_id_1_foreign` (`plan_comptable_id_1`),
  ADD KEY `balances_plan_comptable_id_2_foreign` (`plan_comptable_id_2`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Index pour la table `code_journals`
--
ALTER TABLE `code_journals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_journals_user_id_foreign` (`user_id`),
  ADD KEY `code_journals_company_id_foreign` (`company_id`),
  ADD KEY `code_journals_compte_de_tresorerie_foreign` (`compte_de_tresorerie`);

--
-- Index pour la table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `companies_email_adresse_unique` (`email_adresse`);

--
-- Index pour la table `ecriture_comptables`
--
ALTER TABLE `ecriture_comptables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ecriture_comptables_plan_comptable_id_foreign` (`plan_comptable_id`),
  ADD KEY `ecriture_comptables_plan_tiers_id_foreign` (`plan_tiers_id`),
  ADD KEY `ecriture_comptables_code_journal_id_foreign` (`code_journal_id`),
  ADD KEY `ecriture_comptables_user_id_foreign` (`user_id`),
  ADD KEY `ecriture_comptables_company_id_foreign` (`company_id`),
  ADD KEY `ecriture_comptables_exercices_comptables_id_foreign` (`exercices_comptables_id`),
  ADD KEY `ecriture_comptables_journaux_saisis_id_foreign` (`journaux_saisis_id`);

--
-- Index pour la table `exercices_comptables`
--
ALTER TABLE `exercices_comptables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exercices_comptables_user_id_foreign` (`user_id`),
  ADD KEY `exercices_comptables_company_id_foreign` (`company_id`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `grand_livres`
--
ALTER TABLE `grand_livres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grand_livres_user_id_foreign` (`user_id`),
  ADD KEY `grand_livres_company_id_foreign` (`company_id`),
  ADD KEY `grand_livres_plan_comptable_id_1_foreign` (`plan_comptable_id_1`),
  ADD KEY `grand_livres_plan_comptable_id_2_foreign` (`plan_comptable_id_2`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `journaux_saisis`
--
ALTER TABLE `journaux_saisis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journaux_saisis_exercices_comptables_id_foreign` (`exercices_comptables_id`),
  ADD KEY `journaux_saisis_code_journals_id_foreign` (`code_journals_id`),
  ADD KEY `journaux_saisis_user_id_foreign` (`user_id`),
  ADD KEY `journaux_saisis_company_id_foreign` (`company_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `plan_comptables`
--
ALTER TABLE `plan_comptables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_comptables_user_id_foreign` (`user_id`),
  ADD KEY `plan_comptables_company_id_foreign` (`company_id`);

--
-- Index pour la table `plan_tiers`
--
ALTER TABLE `plan_tiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plan_tiers_numero_de_tiers_unique` (`numero_de_tiers`),
  ADD KEY `plan_tiers_compte_general_foreign` (`compte_general`),
  ADD KEY `plan_tiers_user_id_foreign` (`user_id`),
  ADD KEY `plan_tiers_company_id_foreign` (`company_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_adresse_unique` (`email_adresse`),
  ADD KEY `users_compagny_id_foreign` (`company_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `balances`
--
ALTER TABLE `balances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `code_journals`
--
ALTER TABLE `code_journals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `ecriture_comptables`
--
ALTER TABLE `ecriture_comptables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `exercices_comptables`
--
ALTER TABLE `exercices_comptables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `grand_livres`
--
ALTER TABLE `grand_livres`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `journaux_saisis`
--
ALTER TABLE `journaux_saisis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `plan_comptables`
--
ALTER TABLE `plan_comptables`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `plan_tiers`
--
ALTER TABLE `plan_tiers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

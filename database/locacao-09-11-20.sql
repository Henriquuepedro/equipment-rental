-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 09-Nov-2020 às 02:37
-- Versão do servidor: 10.1.37-MariaDB
-- versão do PHP: 7.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `locacao`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name_address` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complement` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `neigh` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lng` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_insert` bigint(20) UNSIGNED NOT NULL,
  `user_update` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `addresses`
--

INSERT INTO `addresses` (`id`, `company_id`, `client_id`, `name_address`, `address`, `number`, `cep`, `complement`, `reference`, `neigh`, `city`, `state`, `lat`, `lng`, `user_insert`, `user_update`, `created_at`, `updated_at`) VALUES
(47, 1, 10, 'Endereço 1', 'Rua Felipe Schmidt', '100', '88010000', 'Apto 204', NULL, 'Centro', 'Florianópolis', 'SC', '-27.5969', '-48.55129', 1, NULL, '2020-10-28 05:59:17', '2020-10-28 05:59:17'),
(48, 1, 10, 'hgdfgdf', 'Servidão da Água-Viva', '111', '88049445', NULL, NULL, 'Tapera da Base', 'Florianópolis', 'SC', '-27.68702', '-48.55395', 1, NULL, '2020-10-28 05:59:17', '2020-10-28 05:59:17'),
(49, 1, 10, 'aaaaaa', 'Rua Felipe Schmidt111', '11', '88010000', NULL, NULL, 'Centro', 'Florianópolis', 'SC', '-27.59648', '-48.55216', 1, NULL, '2020-10-28 05:59:17', '2020-10-28 05:59:17'),
(50, 1, 10, '111111111', 'Rua José Correia', '200', '88049400', NULL, NULL, 'Tapera da Base', 'Florianópolis', 'SC', '-27.68462', '-48.55418', 1, NULL, '2020-10-28 05:59:17', '2020-10-28 05:59:17'),
(51, 1, 10, 'APRESI', 'Servidão Pau Brasil', '103', '88049450', NULL, NULL, 'Tapera da Base', 'Florianópolis', 'SC', '-27.687898307499886', '-48.55373370016677', 1, NULL, '2020-10-28 05:59:17', '2020-10-28 05:59:17');

-- --------------------------------------------------------

--
-- Estrutura da tabela `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fantasy` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_1` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_2` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf_cnpj` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rg_ie` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observation` longtext COLLATE utf8mb4_unicode_ci,
  `contact` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_insert` bigint(20) UNSIGNED NOT NULL,
  `user_update` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `clients`
--

INSERT INTO `clients` (`id`, `company_id`, `name`, `type`, `fantasy`, `email`, `phone_1`, `phone_2`, `cpf_cnpj`, `rg_ie`, `observation`, `contact`, `user_insert`, `user_update`, `created_at`, `updated_at`) VALUES
(10, 1, 'Pedro Henrique Ambrosio', 'pf', NULL, 'pedrohenrique.sc.96@gmail.com', '48996677961', '4833374653', '71459881001', '6552981', 'kdlakdlaskdlksadl', 'Pedrin', 1, 1, '2020-10-18 02:39:36', '2020-10-28 05:59:17'),
(11, 1, 'Pedro Henrique Ambrosio - 1', 'pf', NULL, 'pedrohenrique.sc.96@gmail.com', '48996677961', '4833374653', '10267745940', '6552981', 'kdlakdlaskdlksadl', 'Pedrin', 1, 1, '2020-10-18 02:39:36', '2020-11-09 04:31:48');

-- --------------------------------------------------------

--
-- Estrutura da tabela `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fantasy` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_person` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf_cnpj` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_1` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_2` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_update` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `companies`
--

INSERT INTO `companies` (`id`, `name`, `fantasy`, `type_person`, `cpf_cnpj`, `email`, `phone_1`, `phone_2`, `contact`, `logo`, `user_update`, `created_at`, `updated_at`) VALUES
(1, 'Empresa Locação', NULL, 'pf', '10267745940', 'pedrohenrique.sc.96@gmail.com', '48996677961', '48996677961', 'Pedrin', 'MTAwMHB4LUFtYXp72.png', 1, '2020-10-17 22:52:38', '2020-11-09 04:32:22');

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipaments`
--

CREATE TABLE `equipaments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `manufacturer` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `volume` int(11) DEFAULT NULL,
  `user_insert` bigint(20) UNSIGNED NOT NULL,
  `user_update` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `equipaments`
--

INSERT INTO `equipaments` (`id`, `company_id`, `name`, `reference`, `stock`, `value`, `manufacturer`, `volume`, `user_insert`, `user_update`, `created_at`, `updated_at`) VALUES
(8, 1, 'Equipamento 1', 'E-001', 0, '100.00', 'Fabricante Teste', NULL, 1, 1, '2020-10-22 03:50:22', '2020-10-27 04:42:55'),
(9, 1, NULL, 'C-003', 10, '0.00', 'Fabricante Teste', 6, 1, 1, '2020-10-22 04:34:58', '2020-11-05 03:20:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `equipament_wallets`
--

CREATE TABLE `equipament_wallets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_id` bigint(20) UNSIGNED NOT NULL,
  `equipament_id` bigint(20) UNSIGNED NOT NULL,
  `day_start` int(11) NOT NULL,
  `day_end` int(11) DEFAULT NULL,
  `value` decimal(12,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `user_insert` bigint(20) UNSIGNED NOT NULL,
  `user_update` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_11_160802_create_companies_table', 1),
(2, '2014_10_12_000000_create_users_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2020_10_11_182700_create_clients_table', 1),
(6, '2020_10_17_024042_create_addresses_table', 1),
(9, '2020_10_21_213742_create_equipaments_table', 2),
(10, '2020_10_21_223842_create_equipment_wallets_table', 2),
(13, '2020_10_21_223842_create_equipament_wallets_table', 3),
(14, '2020_11_04_001239_create_permissions_table', 3);

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group_text` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auto_check` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `text`, `group_name`, `group_text`, `auto_check`, `active`, `created_at`, `updated_at`) VALUES
(1, 'ClientView', 'Visualizar Cliente', 'client', 'Cliente', '[]', 1, NULL, NULL),
(2, 'ClientCreatePost', 'Cadastrar Cliente', 'client', 'Cliente', '[1]', 1, NULL, NULL),
(3, 'ClientUpdatePost', 'Atualizar Cliente', 'client', 'Cliente', '[1]', 1, NULL, NULL),
(4, 'ClientDeletePost', 'Excluir Cliente', 'client', 'Cliente', '[1]', 1, NULL, NULL),
(5, 'EquipamentView', 'Visualizar Equipamento', 'equipament', 'Equipamento', '[]', 1, NULL, NULL),
(6, 'EquipamentCreatePost', 'Cadastrar Equipamento', 'equipament', 'Equipamento', '[5]', 1, NULL, NULL),
(7, 'EquipamentUpdatePost', 'Atualizar Equipamento', 'equipament', 'Equipamento', '[5]', 1, NULL, NULL),
(8, 'EquipamentDeletePost', 'Excluir Equipamento', 'equipament', 'Equipamento', '[5]', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_access_at` datetime DEFAULT NULL,
  `logout` tinyint(1) NOT NULL DEFAULT '0',
  `type_user` tinyint(2) NOT NULL DEFAULT '0',
  `permission` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `phone`, `password`, `company_id`, `profile`, `active`, `last_login_at`, `last_login_ip`, `last_access_at`, `logout`, `type_user`, `permission`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Pedro Henrique Ambrosio', NULL, 'admin@admin.com', NULL, '48996677969', '$2y$10$/FTyC6xN/J9f.BzSATy22.20uf0zghJ.Zn4KVUKgzLisJSSHjFHuy', 1, 'aW9zLTExLTIuanB77.jpg', 1, '2020-11-03 21:47:14', '127.0.0.1', '2020-11-09 01:34:18', 0, 2, '[8,6,5,7]', 'bPFH48pDNTXKmvJBqlzOJ1mWCFSjqSRKLxKERowciXjATBhILxeq42infTQi', '2020-10-18 01:51:46', '2020-11-09 04:34:18'),
(6, 'Pedro Henrique', NULL, 'admin123@admin.com', NULL, '4899667796', '$2y$10$uYA/gv0b8MSrh9GSDxLh4uFk.CmvwNx0g9..q0la/QVPfwZpAjuH6', 1, NULL, 1, NULL, NULL, NULL, 0, 0, '[1,2,3,5,6]', NULL, '2020-11-09 04:25:40', '2020-11-09 04:32:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_company_id_foreign` (`company_id`),
  ADD KEY `addresses_client_id_foreign` (`client_id`),
  ADD KEY `addresses_user_insert_foreign` (`user_insert`),
  ADD KEY `addresses_user_update_foreign` (`user_update`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_company_id_foreign` (`company_id`),
  ADD KEY `clients_user_insert_foreign` (`user_insert`),
  ADD KEY `clients_user_update_foreign` (`user_update`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipaments`
--
ALTER TABLE `equipaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipaments_company_id_foreign` (`company_id`),
  ADD KEY `equipaments_user_insert_foreign` (`user_insert`),
  ADD KEY `equipaments_user_update_foreign` (`user_update`);

--
-- Indexes for table `equipament_wallets`
--
ALTER TABLE `equipament_wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipament_wallets_company_id_foreign` (`company_id`),
  ADD KEY `equipament_wallets_equipament_id_foreign` (`equipament_id`),
  ADD KEY `equipament_wallets_user_insert_foreign` (`user_insert`),
  ADD KEY `equipament_wallets_user_update_foreign` (`user_update`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_company_id_foreign` (`company_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipaments`
--
ALTER TABLE `equipaments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `equipament_wallets`
--
ALTER TABLE `equipament_wallets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addresses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addresses_user_insert_foreign` FOREIGN KEY (`user_insert`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `addresses_user_update_foreign` FOREIGN KEY (`user_update`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clients_user_insert_foreign` FOREIGN KEY (`user_insert`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `clients_user_update_foreign` FOREIGN KEY (`user_update`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `equipaments`
--
ALTER TABLE `equipaments`
  ADD CONSTRAINT `equipaments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipaments_user_insert_foreign` FOREIGN KEY (`user_insert`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `equipaments_user_update_foreign` FOREIGN KEY (`user_update`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `equipament_wallets`
--
ALTER TABLE `equipament_wallets`
  ADD CONSTRAINT `equipament_wallets_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipament_wallets_equipament_id_foreign` FOREIGN KEY (`equipament_id`) REFERENCES `equipaments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipament_wallets_user_insert_foreign` FOREIGN KEY (`user_insert`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `equipament_wallets_user_update_foreign` FOREIGN KEY (`user_update`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

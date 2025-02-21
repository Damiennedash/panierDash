-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 21 fév. 2025 à 12:45
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
-- Base de données : `digital_flea_market`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id_category` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id_category`, `name`) VALUES
(1, 'Vêtements'),
(2, 'Livres'),
(3, 'Chaussures'),
(4, 'Nourriture');

-- --------------------------------------------------------

--
-- Structure de la table `deliveries`
--

CREATE TABLE `deliveries` (
  `id_delivery` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `delivery_date` date NOT NULL,
  `status` enum('Pending','In Progress','Delivered') NOT NULL,
  `id_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `merchants`
--

CREATE TABLE `merchants` (
  `id_merchant` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `abonnement` enum('confirmed','unconfirmed') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `merchants`
--

INSERT INTO `merchants` (`id_merchant`, `id_user`, `abonnement`) VALUES
(1, 19, 'confirmed'),
(2, 17, 'confirmed'),
(3, 23, 'confirmed'),
(4, 15, 'confirmed'),
(5, 14, 'confirmed'),
(6, 13, 'confirmed');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id_message` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `operateur`
--

CREATE TABLE `operateur` (
  `id_operateur` int(11) NOT NULL,
  `logo_operateur` varchar(200) NOT NULL,
  `nom_operateur` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `operateur`
--

INSERT INTO `operateur` (`id_operateur`, `logo_operateur`, `nom_operateur`, `description`, `date_creation`) VALUES
(2, 'path/to/mixx-logo.jpg', 'Mixx', 'Opérateur de téléphonie mobile offrant des services variés.', '2025-02-08 12:13:23'),
(3, 'path/to/moov-logo.jpg', 'Moov', 'Opérateur de téléphonie mobile offrant des services de communication et de données.', '2025-02-08 12:14:56');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` enum('In Progress','Confirmed','Delivered','Cancelled') NOT NULL,
  `id_student` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `total` float NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id_order`, `order_date`, `status`, `id_student`, `id_product`, `quantity`, `numero`, `total`, `id_user`) VALUES
(9, '2025-02-12 00:18:36', '', 18, 5, 2, 99094523, 246, 19),
(10, '2025-02-12 00:18:47', '', 18, 7, 2, 99094523, 200, 19),
(11, '2025-02-12 06:07:48', '', 18, 4, 1, 99094523, 12345, 19),
(12, '2025-02-12 06:07:57', '', 18, 6, 1, 99094523, 122.96, 19),
(13, '2025-02-12 06:09:19', 'Confirmed', 18, 4, 1, 99094523, 12345, 19),
(14, '2025-02-12 09:03:23', 'Confirmed', 18, 7, 5, 99094523, 500, 19),
(15, '2025-02-12 09:03:31', 'Confirmed', 18, 5, 1, 99094523, 123, 19),
(16, '2025-02-12 09:03:37', '', 18, 8, 1, 91094523, 3500, 23),
(17, '2025-02-12 09:07:36', 'Confirmed', 18, 4, 2, 91094523, 24690, 19),
(18, '2025-02-12 09:07:45', 'Confirmed', 18, 7, 4, 91094523, 400, 19),
(19, '2025-02-12 09:38:02', '', 18, 2, 1, 99094523, 1234, 19),
(20, '2025-02-12 09:38:07', '', 18, 5, 1, 99094523, 123, 19),
(21, '2025-02-12 09:38:13', '', 18, 10, 1, 99094523, 3000, 23),
(22, '2025-02-12 09:38:19', '', 18, 9, 1, 99094523, 3000, 23),
(23, '2025-02-12 09:44:46', 'Delivered', 18, 9, 3, 99094523, 9000, 23),
(24, '2025-02-12 09:45:51', '', 18, 7, 3, 91094523, 300, 19),
(25, '2025-02-12 09:46:20', '', 18, 7, 5, 91094523, 500, 19),
(26, '2025-02-12 09:47:31', 'Delivered', 18, 7, 2, 91094523, 200, 19),
(27, '2025-02-12 09:56:32', 'Delivered', 18, 8, 2, 99094523, 7000, 23),
(28, '2025-02-12 09:57:18', 'Delivered', 18, 2, 5, 99094523, 6170, 19),
(29, '2025-02-12 10:58:07', 'Delivered', 18, 9, 1, 99094523, 3000, 23),
(30, '2025-02-12 10:58:12', 'Delivered', 18, 10, 2, 99094523, 6000, 23),
(31, '2025-02-12 11:00:44', 'Delivered', 18, 6, 1, 91094523, 122.96, 19),
(32, '2025-02-12 11:01:27', 'Delivered', 18, 2, 1, 99094523, 1234, 19),
(33, '2025-02-12 11:06:13', 'Delivered', 18, 2, 1, 99094523, 1234, 19),
(34, '2025-02-12 14:21:07', 'Confirmed', 18, 9, 2, 91094523, 6000, 23),
(35, '2025-02-12 14:22:10', 'Confirmed', 18, 2, 1, 91094523, 1234, 19),
(36, '2025-02-12 16:38:39', 'Confirmed', 18, 2, 1, 91094523, 1234, 19),
(37, '2025-02-12 16:38:44', 'Confirmed', 18, 2, 1, 91094523, 1234, 19),
(38, '2025-02-12 16:58:14', 'Confirmed', 18, 2, 1, 99094523, 1234, 19),
(39, '2025-02-12 16:58:18', 'Confirmed', 18, 2, 1, 99094523, 1234, 19),
(40, '2025-02-12 16:59:19', 'Confirmed', 18, 2, 1, 91094523, 1234, 19),
(41, '2025-02-12 16:59:23', 'Confirmed', 18, 9, 1, 91094523, 3000, 23),
(42, '2025-02-12 17:05:01', 'Confirmed', 18, 2, 1, 91094523, 1234, 19),
(43, '2025-02-12 17:05:05', 'Confirmed', 18, 5, 1, 91094523, 123, 19),
(44, '2025-02-12 17:05:11', 'Confirmed', 18, 11, 1, 99094523, 3000, 18),
(45, '2025-02-12 17:31:43', 'Delivered', 18, 2, 2, 91094523, 2468, 19),
(46, '2025-02-12 17:32:43', 'Confirmed', 18, 11, 1, 91111111, 3000, 18),
(47, '2025-02-13 08:03:36', 'Delivered', 18, 4, 3, 99657899, 37035, 19),
(48, '2025-02-13 08:18:32', 'Confirmed', 18, 2, 1, 99657899, 1234, 19),
(49, '2025-02-13 11:45:08', 'Confirmed', 18, 2, 1, 91111111, 1234, 19),
(50, '2025-02-13 11:48:00', 'Confirmed', 18, 9, 1, 99657899, 3000, 23),
(51, '2025-02-13 16:40:15', 'Confirmed', 18, 2, 2, 91094523, 2468, 19),
(52, '2025-02-13 16:50:15', 'Confirmed', 18, 6, 1, 91094523, 122.96, 19),
(53, '2025-02-13 18:04:45', 'Confirmed', 18, 11, 1, 91094523, 3000, 18),
(54, '2025-02-13 18:13:32', 'Confirmed', 18, 6, 1, 91094523, 122.96, 19),
(55, '2025-02-19 09:15:18', 'Confirmed', 18, 2, 2, 99657899, 2468, 19);

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id_payment` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_method` enum('Card','PayPal','Cash') NOT NULL,
  `id_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id_product` int(11) NOT NULL,
  `id_merchant` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `available_quantity` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `id_category` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id_product`, `id_merchant`, `product_name`, `description`, `price`, `available_quantity`, `image`, `id_category`, `created_at`, `updated_at`) VALUES
(2, 19, 'sdfghjksdfghj', 'GFHJK?', 1234.00, 12314, 'uploads/Food flyer.jpg', 3, '2025-01-28 06:49:28', '2025-02-19 09:15:18'),
(3, 19, 'edfghjk', 'ertyuiklmdsfghjklmk,n', 0.26, 0, 'uploads/téléchargement - 2025-02-03T101343.597.jpg', 1, '2025-02-04 12:55:19', '2025-02-11 18:18:11'),
(4, 19, 'CODE QR', 'ertfghjklmù*', 12345.00, 12, 'uploads/Qr_candidature_Senghor_-1024.png', 2, '2025-02-04 13:03:20', '2025-02-13 08:03:36'),
(5, 19, 'dfghjkl', 'ytuio', 123.00, 7, 'uploads/téléchargement - 2025-01-29T145835.108.jpg', 3, '2025-02-04 19:06:45', '2025-02-12 17:05:05'),
(6, 19, 'zertyu', 'esdrtfyguhijok', 122.96, 2, 'uploads/téléchargement - 2025-02-03T094026.336.jpg', 3, '2025-02-06 14:12:51', '2025-02-13 18:13:32'),
(7, 19, 'Yaourt', 'TRES DOUX', 100.00, 0, 'uploads/e1d02696-546f-4a60-aac5-02666f97d7e9.png', 4, '2025-02-07 14:25:33', '2025-02-12 09:47:31'),
(8, 23, 'Triple Burger', 'sauce viande frite salade', 3500.00, 0, 'uploads/téléchargement - 2025-02-11T064108.663.jpg', 4, '2025-02-11 06:44:31', '2025-02-12 09:56:32'),
(9, 23, 'Triple Burger', 'Viande sauce salade', 3000.00, 1, 'uploads/hd-whopper-burger-fast-food-png-704081694863355z5mhf17nk1.png', 4, '2025-02-11 06:45:27', '2025-02-13 11:48:00'),
(10, 23, 'crispy burger', 'poulet sauce salade frite', 3000.00, 2, 'uploads/hd-beef-burger-ham-and-cheese-fast-food-png-704081694863375h4sgwgua5l.png', 4, '2025-02-11 06:47:49', '2025-02-12 10:58:12'),
(11, 18, 'crispy burger', 'poulet sauce salade frite', 3000.00, 1, 'uploads/hd-beef-burger-ham-and-cheese-fast-food-png-704081694863375h4sgwgua5l.png', 4, '2025-02-11 18:18:30', '2025-02-13 18:04:45');

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

CREATE TABLE `students` (
  `id_student` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`id_student`) VALUES
(18),
(21);

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id_subscription` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `id_merchant` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Admin','Merchant','Student') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `name`, `email`, `phone`, `password`, `user_type`) VALUES
(12, 'DJATA Damienne', 'djatadamienne5@gmail.com', '93365551', '$2y$10$fYQV38biRvMeat1hhGMW4edsPrY3KY8XdiMYx2VM.DbON4I/sN0oa', 'Student'),
(13, 'DJATA Damie', 'djatadamienne@gmail.com', '93365556', '$2y$10$B/7BbVeTnvCcxayewgwLje3lcL5Ds4aZCH0rE/06VcfaMQmaoi/O6', 'Merchant'),
(14, 'DJATA Damie', 'djatadamienneqze@gmail.com', '93365556', '$2y$10$s0PVtXShRtqIgeX8cGxpnu7xKhZWsbeDy2UQ8M6SiKPm3.KqHu6kq', 'Merchant'),
(15, 'DJATA Damie', 'djatadamienN@gmail.com', '93365556', '$2y$10$9HGGBdSaEpa/ocOLp1D8x.tLWttXfoGBsc6WYGL8LJrjXv6wlhGZO', 'Merchant'),
(16, 'dhgfhj', 'djatadamienneNN@gmail.com', '9876544345', '$2y$10$ROC9ZIyW8v44DDiETesLo.MVIb6V.3MTrlkpQ1LMcsQzIEn3.z5cW', 'Student'),
(17, 'dja point', 'damienne.djata@lomebs.com', '93365556', '$2y$10$ixlp7F/iK/R8kUoX03nV.uHhJzRbT/icrCZZ0T6i36ExYa9FWd/J6', 'Merchant'),
(18, 'QQQQSDFG', 'djatadamienne5Nnn@gmail.com', '0987678', '$2y$10$DmjRnhIeAmLNasGqh53juOT7HGdgtHhq2iWMkiPz8XJCWo1Sougyy', 'Student'),
(19, 'QQQQSDFGaze', 'djatadamienne5NN@gmail.com', '55643456', '$2y$10$yYcKhDPKSzmU13Kk8ArOA.wJtiKX5ZpGR52j.vb6z/ILEAm5vze0C', 'Merchant'),
(20, 'DJATA Damie', 'djatadamien5@gmail.com', '933655562345', '$2y$10$dfzQ8Ld/W2Ss6dym0MB3heDwvDFnzwvLbz8OfuFdtLFBTTsObbbuS', 'Student'),
(21, 'dja point', 'djatadamienNn@gmail.com', '12345', '$2y$10$NX3DlUbLlXsAoyhaM15xV.tBqokndmqtmdTDquGq9yTb.LIRUlOh.', 'Student'),
(23, 'DJAT DAM', 'djatadamienne5NNNn@gmail.com', '91094523', '$2y$10$84Kgo/eIVSDvOsAtVSo/UulcQrcA1h8vmmsqFWAMd2GNuQQguCE3y', 'Merchant');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_category`);

--
-- Index pour la table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id_delivery`),
  ADD KEY `id_order` (`id_order`);

--
-- Index pour la table `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`id_merchant`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Index pour la table `operateur`
--
ALTER TABLE `operateur`
  ADD PRIMARY KEY (`id_operateur`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_student` (`id_student`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`),
  ADD KEY `id_order` (`id_order`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id_product`),
  ADD KEY `id_merchant` (`id_merchant`),
  ADD KEY `id_category` (`id_category`);

--
-- Index pour la table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id_student`);

--
-- Index pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id_subscription`),
  ADD KEY `id_merchant` (`id_merchant`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_category` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id_delivery` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `merchants`
--
ALTER TABLE `merchants`
  MODIFY `id_merchant` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `operateur`
--
ALTER TABLE `operateur`
  MODIFY `id_operateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id_student` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id_subscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`);

--
-- Contraintes pour la table `merchants`
--
ALTER TABLE `merchants`
  ADD CONSTRAINT `merchants_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_id_product` FOREIGN KEY (`id_product`) REFERENCES `products` (`id_product`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_id_student` FOREIGN KEY (`id_student`) REFERENCES `students` (`id_student`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_id_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`);

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`id_category`) REFERENCES `categories` (`id_category`),
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`id_merchant`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`id_merchant`) REFERENCES `merchants` (`id_merchant`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

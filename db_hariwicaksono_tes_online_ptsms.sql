# Host: localhost  (Version 5.7.36)
# Date: 2026-04-01 15:09:06
# Generator: MySQL-Front 6.1  (Build 1.26)


#
# Structure for table "backups"
#

DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "backups"
#


#
# Structure for table "failed_jobs"
#

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "failed_jobs"
#


#
# Structure for table "menus"
#

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permission_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(3) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_parent_id_foreign` (`parent_id`),
  CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "menus"
#

INSERT INTO `menus` VALUES (1,'dashboard','mdi-view-dashboard','/dashboard',NULL,NULL,0,1,1,NULL,'2025-07-29 10:12:57'),(2,'Pages','mdi-file-document-edit','/pages','page.view',7,1,1,1,NULL,'2025-07-29 10:12:57'),(3,'Users','mdi-account-multiple',NULL,'user.view',NULL,2,1,1,NULL,'2025-07-29 10:12:57'),(4,'users','mdi-account','/users','user.view',3,0,1,1,NULL,'2025-07-29 10:12:57'),(5,'Roles','mdi-account-check','/roles','role.view',3,1,1,1,NULL,'2025-07-29 10:12:57'),(6,'Permissions','mdi-account-details','/permissions','permission.view',3,2,1,1,NULL,'2025-07-29 10:12:57'),(7,'system','mdi-cog-box',NULL,'setting.view',NULL,3,1,1,NULL,'2025-07-29 10:12:57'),(8,'settings','mdi-cog','/settings','setting.update',7,0,1,1,NULL,'2025-07-29 10:12:57'),(9,'Menu','mdi-format-list-bulleted-square','/menus','menu.view',7,1,1,1,NULL,'2025-07-29 10:12:57'),(10,'log_activity','mdi-database-eye','/logs','log.view',7,2,1,1,NULL,'2025-07-29 10:12:57'),(11,'Backup DB','mdi-database','/backups','backup.view',7,3,1,1,NULL,'2025-07-29 10:12:57'),(12,'Product','mdi-package-variant-closed','/product','product.view',NULL,0,1,0,'2025-12-21 20:25:01','2025-12-21 20:25:01');

#
# Structure for table "migrations"
#

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "migrations"
#

INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2025_06_30_112617_create_settings_table',2),(6,'2025_07_01_044557_add_role_status_to_users_table',3),(7,'2025_07_01_130506_create_roles_and_permissions_tables',4),(8,'2025_07_08_201124_create_activity_logs_table',5),(9,'2025_07_17_102926_create_backups_table',6),(10,'2025_07_26_085502_create_pages_table',7),(11,'2025_07_28_085027_create_menus_table',8),(12,'2026_04_01_063040_create_products_table',9),(13,'2026_04_01_063221_create_purchases_table',9),(14,'2026_04_01_063250_create_purchase_items_table',9);

#
# Structure for table "pages"
#

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_en` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "pages"
#

INSERT INTO `pages` VALUES (1,'Tentang','About','<h1>Selamat datang di website kami</h1><p>Ini adalah halaman beranda.</p>','<h1>Welcome to our website</h1><p>This is the homepage.</p>',1,'about',1,'2025-07-26 11:26:23','2025-07-26 11:26:23'),(2,'Syarat','Terms','<h1>Tentang Kami</h1><p>Informasi mengenai perusahaan atau organisasi.</p>','<h1>About Us</h1><p>Information about the company or organization.</p>',1,'terms',1,'2025-07-26 11:55:17','2025-07-26 20:33:42'),(3,'Privasi','Privacy','<h1>Kontak Kami</h1><p>Hubungi kami melalui form atau media sosial.</p>','<h1>Contact Us</h1><p>Contact us via form or social media.</p>',1,'privacy',1,'2025-07-26 11:56:25','2025-07-26 11:59:35');

#
# Structure for table "password_reset_tokens"
#

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "password_reset_tokens"
#


#
# Structure for table "permissions"
#

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "permissions"
#

INSERT INTO `permissions` VALUES (1,'user.view',NULL,NULL),(2,'user.create',NULL,NULL),(3,'user.update',NULL,NULL),(4,'user.delete',NULL,NULL),(5,'setting.view',NULL,NULL),(6,'setting.update',NULL,NULL),(7,'role.view','2025-07-03 04:37:29','2025-07-03 04:37:29'),(8,'role.create','2025-07-03 04:40:45','2025-07-03 04:40:45'),(9,'role.update','2025-07-03 04:41:52','2025-07-03 04:41:52'),(10,'role.delete','2025-07-03 04:42:21','2025-07-03 04:42:21'),(11,'permission.view','2025-07-03 11:33:48','2025-07-03 11:33:48'),(12,'permission.create','2025-07-03 11:33:56','2025-07-03 11:33:56'),(13,'permission.delete','2025-07-03 11:34:04','2025-07-03 11:34:04'),(14,'log.view','2025-07-09 09:42:20','2025-07-09 09:42:20'),(15,'backup.view','2025-07-17 10:44:03','2025-07-17 10:44:03'),(16,'backup.create','2025-07-17 10:44:11','2025-07-17 10:44:11'),(17,'backup.restore','2025-07-17 10:44:33','2025-07-17 10:44:33'),(18,'backup.download','2025-07-17 10:44:42','2025-07-17 10:44:42'),(19,'backup.delete','2025-07-17 10:44:53','2025-07-17 10:44:53'),(20,'page.view','2025-07-26 20:34:31','2025-07-26 20:34:31'),(21,'page.create','2025-07-26 20:34:42','2025-07-26 20:34:42'),(22,'page.update','2025-07-26 20:34:48','2025-07-26 20:34:48'),(23,'page.delete','2025-07-26 20:34:54','2025-07-26 20:34:54'),(24,'menu.view','2025-07-28 15:06:53','2025-07-28 15:06:53'),(25,'menu.create','2025-07-28 15:07:07','2025-07-28 15:07:07'),(26,'menu.update','2025-07-28 15:07:12','2025-07-28 15:07:12'),(27,'menu.delete','2025-07-29 20:30:51','2025-07-29 20:30:51'),(28,'product.view','2025-12-21 20:19:45','2025-12-21 20:19:45'),(29,'product.create','2025-12-21 20:19:58','2025-12-21 20:19:58'),(30,'product.update','2025-12-21 20:20:05','2025-12-21 20:20:05'),(31,'product.delete','2025-12-21 20:20:14','2025-12-21 20:20:14');

#
# Structure for table "personal_access_tokens"
#

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "personal_access_tokens"
#


#
# Structure for table "products"
#

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "products"
#


#
# Structure for table "purchases"
#

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "purchases"
#


#
# Structure for table "purchase_items"
#

DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE `purchase_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_items_purchase_id_foreign` (`purchase_id`),
  KEY `purchase_items_product_id_foreign` (`product_id`),
  CONSTRAINT `purchase_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `purchase_items_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "purchase_items"
#


#
# Structure for table "roles"
#

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "roles"
#

INSERT INTO `roles` VALUES (1,'admin',NULL,NULL),(2,'user',NULL,NULL);

#
# Structure for table "permission_role"
#

DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`),
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "permission_role"
#

INSERT INTO `permission_role` VALUES (1,1),(16,1),(19,1),(18,1),(17,1),(15,1),(14,1),(12,1),(13,1),(11,1),(8,1),(10,1),(9,1),(7,1),(6,1),(5,1),(2,1),(4,1),(3,1),(21,1),(23,1),(22,1),(20,1),(25,1),(26,1),(24,1),(27,1),(29,1),(31,1),(30,1),(28,1);

#
# Structure for table "settings"
#

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "settings"
#

INSERT INTO `settings` VALUES (1,'app_developer','Laravel',NULL,NULL),(2,'site_name','Laravel',NULL,'2025-07-26 17:58:11'),(3,'site_description','Laravel',NULL,'2025-07-19 09:40:47'),(4,'site_logo','/images/logo.png',NULL,'2025-09-20 10:47:12'),(5,'site_background',NULL,NULL,NULL);

#
# Structure for table "users"
#

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_superadmin` tinyint(3) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "users"
#

INSERT INTO `users` VALUES (1,'Admin Demo','admin@test.com',NULL,'$2y$12$cZPQ2FZG7aphubUSDmL8lumyYowmPu.2SiJOlovT6eAzdo51n/rqK',NULL,1,1,'2025-06-24 09:33:02','2025-07-05 00:16:08'),(2,'User Demo','user@test.com',NULL,'$2y$12$L6XTS3mc/4xIFzf6sq7d2eVB10Kq/k6Hd2jG47VGzpOHYy6G7syvC','lxTXMbglskyfqXCvuny3WMAXnXi1qWZBRpnInnw4pWKox5aV9WMDedqL3Ikf',1,0,'2025-06-24 09:33:03','2025-07-29 09:56:32');

#
# Structure for table "role_user"
#

DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `role_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  KEY `role_user_role_id_foreign` (`role_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "role_user"
#

INSERT INTO `role_user` VALUES (1,1),(2,2);

#
# Structure for table "activity_logs"
#

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

#
# Data for table "activity_logs"
#

INSERT INTO `activity_logs` VALUES (1,1,'login','Auth','Login sukses oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:04:05','2026-04-01 14:04:05'),(2,1,'logout','Auth','Logout oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:09:32','2026-04-01 14:09:32'),(3,1,'login','Auth','Login sukses oleh Admin Demo','127.0.0.1','PostmanRuntime/7.36.0','2026-04-01 14:11:21','2026-04-01 14:11:21'),(4,1,'login','Auth','Login sukses oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:14:46','2026-04-01 14:14:46'),(5,1,'logout','Auth','Logout oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:18:35','2026-04-01 14:18:35'),(6,1,'login','Auth','Login sukses oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:18:46','2026-04-01 14:18:46'),(7,1,'logout','Auth','Logout oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:21:35','2026-04-01 14:21:35'),(8,1,'login','Auth','Login sukses oleh Admin Demo','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-01 14:21:44','2026-04-01 14:21:44'),(9,1,'login','Auth','Login sukses oleh Admin Demo','127.0.0.1','PostmanRuntime/7.36.0','2026-04-01 14:56:00','2026-04-01 14:56:00');

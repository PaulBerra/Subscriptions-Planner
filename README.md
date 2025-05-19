Créer la base planner_web :

```
CREATE DATABASE `planner_web`  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
```

Créer la table planner_events : 

```
CREATE TABLE `planner_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` decimal(10,2) DEFAULT '0.00',
  `rappel` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `categorie` enum('travail','amusement','investissement','vie courante') COLLATE utf8mb4_unicode_ci DEFAULT 'vie courante',
  `valide` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

Créer la table planner_users : 

```
CREATE TABLE `planner_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

Créer utilisateur administrateur : 

```
INSERT INTO planner_users (username, password, role) VALUES (
  'admin', /*user=admin*/
  'YourSecurePassword',
  'admin'/*role=admin*/
);
```

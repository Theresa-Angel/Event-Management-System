SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS activity_logs;

CREATE TABLE `activity_logs` (
  `activity` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_id` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS contact_messages;

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','read','replied','resolved') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS event_registrations;

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','pending','cancelled') DEFAULT 'confirmed',
  `reminder_sent` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS events;

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `current_attendees` int(11) DEFAULT NULL,
  `status` enum('active','pending','cancelled','draft','completed') DEFAULT 'active',
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `prizes` text DEFAULT NULL,
  `min_team_size` int(11) DEFAULT 1,
  `max_team_size` int(11) DEFAULT 1,
  PRIMARY KEY (`event_id`),
  KEY `organizer_id` (`organizer_id`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO events VALUES("1","Startup Pitch Competition","Startups pitch to investors","Other","2026-01-10 13:00:00","2026-01-20 18:00:00","Tech Hub","20","20","0","completed","","2026-01-18 17:17:48","","1","1");
INSERT INTO events VALUES("8","Art Exhibition","Modern art exhibition","Other","2026-02-15 10:00:00","2026-02-20 10:00:00","Art Gallery","20","35","0","active","","2026-01-18 17:17:48","","1","1");
INSERT INTO events VALUES("9","Cooking Workshop","Italian cooking class","Food & Drink","2026-02-05 11:00:00","2026-02-06 04:00:00","Culinary School","20","20","0","active","","2026-01-18 17:17:48","","1","1");



DROP TABLE IF EXISTS feedback;

CREATE TABLE `feedback` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `response` text DEFAULT NULL,
  `status` enum('pending','responded') DEFAULT 'pending',
  `submission_date` datetime NOT NULL,
  `response_date` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS notifications;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('event','reminder','system','alert') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_event_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `related_event_id` (`related_event_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`related_event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS participants;

CREATE TABLE `participants` (
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS registrations;

CREATE TABLE `registrations` (
  `user_id` int(10) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL,
  `ticket_id` int(25) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `registration_date` datetime DEFAULT NULL,
  `id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO registrations VALUES("0","0","2026-01-28 18:20:20","","0","","0","","");
INSERT INTO registrations VALUES("0","0","2026-01-28 18:21:20","","0","","0","","");



DROP TABLE IF EXISTS reminders;

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reminder_time` datetime DEFAULT NULL,
  `sent` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reminders_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  CONSTRAINT `reminders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS system_settings;

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO system_settings VALUES("1","site_name","Student Feedback System","general","2026-01-19 20:16:50","2026-01-19 20:16:50");
INSERT INTO system_settings VALUES("2","site_tagline","Student Feedback & Management System","general","2026-01-19 20:16:50","2026-01-19 20:16:50");
INSERT INTO system_settings VALUES("3","admin_email","admin@example.com","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("4","support_email","support@example.com","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("5","site_description","A comprehensive student feedback and management system","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("6","footer_text","© 2024 Student Feedback System. All rights reserved.","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("7","smtp_host","smtp.gmail.com","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("8","smtp_port","587","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("9","smtp_username","","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("10","smtp_password","","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("11","smtp_ssl","1","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("12","email_notifications","1","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("13","new_feedback_alerts","1","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("14","registration_alerts","1","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("15","system_update_alerts","1","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("16","backup_frequency","weekly","general","2026-01-19 20:16:51","2026-01-19 20:16:51");
INSERT INTO system_settings VALUES("17","max_backup_files","10","general","2026-01-19 20:16:51","2026-01-19 20:16:51");



DROP TABLE IF EXISTS users;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','organizer','student') DEFAULT 'student',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','pending','blocked') NOT NULL DEFAULT 'active',
  `phone` varchar(20) DEFAULT NULL,
  `verification_token` int(11) NOT NULL,
  `roll_number` varchar(25) NOT NULL,
  `department` varchar(30) NOT NULL,
  `year` varchar(25) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO users VALUES("1","Theresa Angel","theresathomas096@gmail.com","$2y$10$gKev.q1raHeu76yFsNezHeAgjDWi.TLn6Uc8HVh11VN8bfB5ogk8u","admin","","2025-12-16 19:32:41","active","8825766907","0","0","","");
INSERT INTO users VALUES("20","Atchaya","atchaya@gmail.com","$2y$10$YsGrU10VQOChniIoFaISDOnuwlMOgruy9nJW1EnE.lc1EKZ3Vy0qu","organizer","","2026-01-18 11:56:10","active","0","1998698","","","");
INSERT INTO users VALUES("21","Sivakami","sivakami37@gmail.com","$2y$10$uxpDab0sZ8XpX7NGeufrl.M.Kcda8/TZiO/XPgovC1vLhX3SZLsk.","student","","2026-01-18 11:56:45","active","0","700","23057571802112018","Computer Science","Third Year");
INSERT INTO users VALUES("22","Karthiga","karthiga@gmail.com","8272c00f19464225a45120489c5b4f620905400e1258b2c06d020242bf2e5676","student","","2026-01-18 21:21:06","active","0","0","23057571802112010","Computer Science","3rd Year");



SET FOREIGN_KEY_CHECKS=1;

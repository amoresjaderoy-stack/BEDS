-- BEDS Dispatch System — InfinityFree-safe import
-- Walay LOCK TABLES (bawal sa free hosting), parent-tables una.
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `incident_status_log`;
DROP TABLE IF EXISTS `dispatch_assignment`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `incidents`;
DROP TABLE IF EXISTS `responders`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `resources`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `uq_roles_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `date_created` date NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role_id` (`role_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `locations` (
  `location_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL,
  `zone_number` varchar(50) DEFAULT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `barangay` varchar(100) NOT NULL,
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `resources` (
  `resource_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(150) NOT NULL,
  `resource_type` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `responders` (
  `responder_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `specialization` varchar(150) DEFAULT NULL,
  `availability_status` varchar(50) NOT NULL,
  PRIMARY KEY (`responder_id`),
  KEY `idx_responders_user_id` (`user_id`),
  CONSTRAINT `fk_responders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `incidents` (
  `incident_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reporter_name` varchar(150) NOT NULL,
  `reporter_contact` varchar(30) DEFAULT NULL,
  `incident_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `reported_by` int(10) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `date_reported` datetime NOT NULL,
  `date_resolved` datetime DEFAULT NULL,
  PRIMARY KEY (`incident_id`),
  KEY `idx_incidents_location_id` (`location_id`),
  KEY `idx_incidents_reported_by` (`reported_by`),
  CONSTRAINT `fk_incidents_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`),
  CONSTRAINT `fk_incidents_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dispatch_assignment` (
  `assignment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `incident_id` int(10) unsigned NOT NULL,
  `responder_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  `dispatch_time` datetime NOT NULL,
  `arrival_time` datetime DEFAULT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `idx_dispatch_incident_id` (`incident_id`),
  KEY `idx_dispatch_responder_id` (`responder_id`),
  KEY `idx_dispatch_resource_id` (`resource_id`),
  CONSTRAINT `fk_dispatch_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispatch_resource` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`),
  CONSTRAINT `fk_dispatch_responder` FOREIGN KEY (`responder_id`) REFERENCES `responders` (`responder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `incident_status_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `incident_id` int(10) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_status_log_incident_id` (`incident_id`),
  KEY `idx_status_log_updated_by` (`updated_by`),
  CONSTRAINT `fk_status_log_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`incident_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_log_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reports` (
  `report_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `generated_by` int(10) unsigned NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `date_range_start` date NOT NULL,
  `date_range_end` date NOT NULL,
  `total_incidents` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_response_time` varchar(50) DEFAULT NULL,
  `date_generated` datetime NOT NULL,
  PRIMARY KEY (`report_id`),
  KEY `idx_reports_generated_by` (`generated_by`),
  CONSTRAINT `fk_reports_generated_by` FOREIGN KEY (`generated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` VALUES (1,'Dispatcher'),(2,'Responder'),(3,'Barangay Official');
INSERT INTO `users` VALUES (1,1,'Roberto Dela Cruz','rdelacruz','1234','0917-123-4501','r.delacruz@brgylibertad.gov.ph','2024-01-10'),(2,2,'Jenny Santos','jsantos','1234','0917-223-9981','j.santos@brgylibertad.gov.ph','2024-02-02'),(3,3,'Kagawad Mario Reyes','admin','1234','0917-777-2200','m.reyes@brgylibertad.gov.ph','2023-11-15'),(4,2,'Allan Bermejo','abermejo','1234','0918-445-2210','a.bermejo@brgylibertad.gov.ph','2024-03-19');
INSERT INTO `locations` VALUES (1,'Purok 3, Sitio Maligaya','Zone 2','Near Libertad Elementary School','Libertad'),(2,'Purok 7, National Highway','Zone 4','Beside Gasoline Station','Libertad'),(3,'Purok 1, Riverside','Zone 1','Near Wooden Bridge','Libertad'),(4,'Purok 5, Upper Libertad','Zone 3','Behind Barangay Chapel','Libertad');
INSERT INTO `resources` VALUES (1,'Ambulance Unit 1','Vehicle','Available'),(2,'Rescue Motorcycle','Vehicle','In Use'),(3,'First Aid Kit A','Medical Supply','Available'),(4,'Portable Radio Set','Communication','Available'),(5,'Fire Extinguisher Set','Rescue Gear','Maintenance');
INSERT INTO `responders` VALUES (1,2,'Jenny Santos','0917-223-9981','First Aid / EMT','Available'),(2,4,'Allan Bermejo','0918-445-2210','Fire & Rescue','On Duty'),(3,NULL,'Carlo Minoza','0919-556-0032','Traffic / Road Accident','Available'),(4,NULL,'Divine Ochoa','0920-334-8871','First Aid / EMT','Off Duty'),(5,NULL,'Aldren Amores','0919-556-0032','Patrolman','On Duty');
INSERT INTO `incidents` VALUES (1,'Maria Cortez','0917-000-1122','Medical Emergency','Elderly resident collapsed, difficulty breathing.',1,1,'On-site','2026-10-15 04:27:00',NULL),(2,'Pedro Lim','0918-222-3344','Road Accident','Motorcycle collision along the highway, one injured.',2,1,'Dispatched','2026-10-15 09:27:00',NULL),(3,'Ana Reyes','0919-444-5566','Fire','Small kitchen fire, contained but smoke reported.',4,1,'Verified','2026-10-15 11:07:00',NULL),(4,'Junjun Tan','0920-555-1290','Typhoon / Flooding','Rising floodwater near riverside houses.',3,3,'Reported','2026-10-15 11:47:00',NULL),(5,'Grace Nunez','0921-888-0021','Domestic Dispute','Loud altercation reported by neighbors, resolved peacefully.',1,1,'Resolved','2026-10-13 12:27:00','2026-10-13 11:27:00'),(6,'Fernando Cruz','0917-321-7788','Medical Emergency','Resident with high fever requesting transport to health center.',2,1,'Resolved','2026-10-11 12:27:00','2026-10-11 11:27:00');
INSERT INTO `dispatch_assignment` VALUES (1,1,1,1,'2026-10-15 04:27:00','2026-10-15 04:47:00'),(2,2,3,2,'2026-10-15 09:27:00',NULL),(3,5,2,4,'2026-10-13 12:27:00','2026-10-13 12:27:00'),(4,6,1,3,'2026-10-11 12:27:00','2026-10-11 12:27:00');
INSERT INTO `incident_status_log` VALUES (1,1,'Reported',1,'2026-10-15 04:17:00'),(2,1,'Verified',1,'2026-10-15 04:22:00'),(3,1,'Dispatched',1,'2026-10-15 04:27:00'),(4,1,'On-site',1,'2026-10-15 04:47:00'),(5,2,'Reported',1,'2026-10-15 09:12:00'),(6,2,'Verified',1,'2026-10-15 09:17:00'),(7,2,'Dispatched',1,'2026-10-15 09:27:00'),(8,3,'Reported',1,'2026-10-15 11:02:00'),(9,3,'Verified',1,'2026-10-15 11:07:00'),(10,4,'Reported',3,'2026-10-15 11:47:00'),(11,5,'Reported',1,'2026-10-13 11:57:00'),(12,5,'Resolved',1,'2026-10-13 11:27:00'),(13,6,'Reported',1,'2026-10-11 12:07:00'),(14,6,'Resolved',1,'2026-10-11 11:27:00');
INSERT INTO `reports` VALUES (1,3,'Monthly Summary','2026-09-01','2026-09-30',24,'14 mins','2026-09-19 12:27:00');

SET FOREIGN_KEY_CHECKS=1;

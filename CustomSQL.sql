CREATE TABLE `iwishco_bot`.`bot.users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `user_id` int UNIQUE NOT NULL,
  `full_name` varchar(255),
  `created_at` timestamp,
  `desc` varchar(255)
);

CREATE TABLE `iwishco_bot`.`bot.projects` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255),
  `due_at` timestamp,
  `manager_user-role_id` int,
  `supervisor_user-role_id` int,
  `percentage` int,
  `desc` varchar(255),
  `created_at` timestamp
);

CREATE TABLE `iwishco_bot`.`bot.goals` (
  `goal_id` int PRIMARY KEY AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `created_at` timestamp,
  `due_at` timestamp,
  `percentage` int,
  `desc` varchar(255)
);

CREATE TABLE `iwishco_bot`.`bot.steps` (
  `step_id` int PRIMARY KEY AUTO_INCREMENT,
  `goal_id` int NOT NULL,
  `created_at` timestamp,
  `due_at` timestamp,
  `percentage` int,
  `desc` varchar(255)
);

CREATE TABLE `iwishco_bot`.`bot.users_roles` (
  `user-role_id` int PRIMARY KEY AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `goal_id` int,
  `step_id` int,
  `user_id` BIGINT UNIQUE NOT NULL,
  `role` varchar(255),
  `desc` varchar(255)
);

ALTER TABLE `iwishco_bot`.`bot.users` ADD FOREIGN KEY (`user_id`) REFERENCES `iwishco_bot`.`user` (`id`);

ALTER TABLE `iwishco_bot`.`bot.users_roles` ADD FOREIGN KEY (`project_id`) REFERENCES `iwishco_bot`.`bot.projects` (`project_id`);

ALTER TABLE `iwishco_bot`.`bot.users_roles` ADD FOREIGN KEY (`user-role_id`) REFERENCES `iwishco_bot`.`bot.users` (`id`);

ALTER TABLE `iwishco_bot`.`bot.goals` ADD FOREIGN KEY (`project_id`) REFERENCES `iwishco_bot`.`bot.projects` (`project_id`);

ALTER TABLE `iwishco_bot`.`bot.steps` ADD FOREIGN KEY (`goal_id`) REFERENCES `iwishco_bot`.`bot.goals` (`goal_id`);

ALTER TABLE `iwishco_bot`.`bot.projects` ADD FOREIGN KEY (`manager_user_id`) REFERENCES `iwishco_bot`.`bot.users_roles` (`user-role_id`);

ALTER TABLE `iwishco_bot`.`bot.projects` ADD FOREIGN KEY (`supervisor_user_id`) REFERENCES `iwishco_bot`.`bot.users_roles` (`user-role_id`);
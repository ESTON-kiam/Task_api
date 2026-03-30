-- ============================================================
--  Task API — MySQL SQL Dump
--  Database: task_api
--  Generated: 2026-03-30
--  Run this after creating the `task_api` database:
--    mysql -u root -p task_api < task_api_dump.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+03:00';  -- Africa/Nairobi

-- ------------------------------------------------------------
-- Table: migrations (Laravel bookkeeping)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch`     int          NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES
(1, '2024_01_01_000000_create_tasks_table', 1);

-- ------------------------------------------------------------
-- Table: tasks
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `tasks`;

CREATE TABLE `tasks` (
  `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
  `title`      varchar(255)                              NOT NULL,
  `due_date`   date                                      NOT NULL,
  `priority`   enum('low','medium','high')               NOT NULL,
  `status`     enum('pending','in_progress','done')      NOT NULL DEFAULT 'pending',
  `created_at` timestamp                                 NULL DEFAULT NULL,
  `updated_at` timestamp                                 NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY  `tasks_title_due_date_unique` (`title`, `due_date`),
  KEY         `tasks_status_index`    (`status`),
  KEY         `tasks_due_date_index`  (`due_date`),
  KEY         `tasks_priority_index`  (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Sample seed data  (due dates relative to ~2026-04-01)
-- Feel free to adjust dates to be today-or-future when running
-- ------------------------------------------------------------
INSERT INTO `tasks`
  (`title`, `due_date`, `priority`, `status`, `created_at`, `updated_at`)
VALUES
  ('Set up CI/CD pipeline',           '2026-04-02', 'high',   'pending',     NOW(), NOW()),
  ('Fix login page bug',              '2026-04-02', 'high',   'in_progress', NOW(), NOW()),
  ('Write unit tests for auth module','2026-04-04', 'high',   'pending',     NOW(), NOW()),
  ('Migrate database to new schema',  '2026-04-08', 'high',   'pending',     NOW(), NOW()),
  ('Deploy hotfix to production',     '2026-04-01', 'high',   'done',        NOW(), NOW()),
  ('Code review for PR #42',          '2026-04-03', 'medium', 'in_progress', NOW(), NOW()),
  ('Update API documentation',        '2026-04-06', 'medium', 'pending',     NOW(), NOW()),
  ('Implement email notifications',   '2026-04-07', 'medium', 'done',        NOW(), NOW()),
  ('Update npm packages',             '2026-04-11', 'low',    'done',        NOW(), NOW()),
  ('Clean up unused CSS classes',     '2026-04-15', 'low',    'pending',     NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

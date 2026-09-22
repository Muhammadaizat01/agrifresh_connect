-- ============================================================================
-- AgriFresh Connect — Buyer Dashboard Fix
-- Run this ONCE in phpMyAdmin (SQL tab) on your existing `agrifresh_connect`
-- database. It only ADDS two missing columns that buyer_dashboard.php needs.
-- It does NOT delete or touch any existing data.
--
-- Why this is needed:
--   buyer_dashboard.php queries `avatar` from `users` and `user_id` from
--   `orders`, but the original database_setup.sql never created those two
--   columns. That mismatch is what was throwing the error / blank page
--   whenever the buyer dashboard tried to load.
-- ============================================================================

-- 1. Let buyers have a profile photo (same idea as farmers.avatar)
ALTER TABLE `users`
    ADD COLUMN `avatar` VARCHAR(500) NULL AFTER `address`;

-- 2. Let an order be linked to the logged-in buyer's account, not just by name
ALTER TABLE `orders`
    ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`,
    ADD INDEX `orders_user_id_idx` (`user_id`);

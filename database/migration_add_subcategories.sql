-- =============================================================
--  Migration: Add sub-category support
--  Run this on existing databases to add parent_id column
--  phpMyAdmin > select your DB > SQL > paste this > Go
-- =============================================================

-- Add parent_id column to categories table
ALTER TABLE categories ADD COLUMN parent_id INT DEFAULT NULL AFTER id;

-- Add foreign key constraint
ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE SET NULL;

-- Note: Existing categories will have parent_id = NULL (they become parent categories)
-- You can then add sub-categories through the admin panel

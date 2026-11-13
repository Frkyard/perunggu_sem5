ALTER TABLE `orders`
    ADD COLUMN `paid_at` DATETIME NULL AFTER `status`;

UPDATE `orders`
SET `paid_at` = `created_at`
WHERE `status` IN ('paid', 'shipped')
  AND `paid_at` IS NULL;

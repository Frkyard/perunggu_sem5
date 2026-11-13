ALTER TABLE `orders`
    ADD COLUMN `payment_status` ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending' AFTER `status`,
    ADD COLUMN `shipping_status` ENUM('pending', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending' AFTER `payment_status`,
    ADD INDEX `idx_orders_payment_status_paid_at` (`payment_status`, `paid_at`),
    ADD INDEX `idx_orders_shipping_status` (`shipping_status`);

-- COD shipments cannot reliably prove payment; leave them pending for admin verification.
UPDATE `orders`
SET
    `payment_status` = CASE
        WHEN `status` = 'paid' THEN 'paid'
        WHEN `status` = 'shipped' AND UPPER(`payment_method`) NOT LIKE '%COD%' THEN 'paid'
        WHEN `status` = 'cancelled'
            AND `paid_at` IS NOT NULL
            AND UPPER(`payment_method`) NOT LIKE '%COD%' THEN 'paid'
        ELSE 'pending'
    END,
    `shipping_status` = CASE
        WHEN `status` = 'shipped' THEN 'shipped'
        WHEN `status` = 'cancelled' THEN 'cancelled'
        ELSE 'pending'
    END;

UPDATE `orders`
SET `paid_at` = NULL
WHERE `status` IN ('shipped', 'cancelled')
  AND `payment_status` = 'pending'
  AND UPPER(`payment_method`) LIKE '%COD%';

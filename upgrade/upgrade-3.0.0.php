<?php
/**
 * 2007-2021 Ingenico
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@ingenico.com we can send you a copy immediately.
 *
 * @author    Ingenico <contact@ingenico.com>
 * @copyright Ingenico
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param \Ingenico_Epayments $module
 * @return bool
 */
function upgrade_module_3_0_0($module)
{
    $module->registerHook('actionEmailSendBefore');

    silentPaymentsMigration();
    silentAliasesMigration();

    return true;
}

function silentPaymentsMigration()
{
    // Check is table exists
    $rows = Db::getInstance()->executeS(sprintf("SHOW TABLES LIKE '%singenico_payments'", _DB_PREFIX_));
    if (count($rows) === 0) {
        return;
    }

    // Rename payid -> pay_id
    $sql = "
    ALTER TABLE `%singenico_payments`
    CHANGE COLUMN `payid` `pay_id` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Payment reference' AFTER `order_id`,
    CHANGE COLUMN `payidsub` `pay_id_sub` VARCHAR(50) NULL DEFAULT NULL COMMENT 'The history level ID of the maintenance operation on the PAYID' AFTER `pay_id`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Remove payid uniq index and add index
    $sql = "
    ALTER TABLE `%singenico_payments`
    DROP INDEX `payid`,
    ADD INDEX `pay_id` (`pay_id`);
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Remove primary key and add order_id index
    $sql = "
    ALTER TABLE `%singenico_payments`
    DROP PRIMARY KEY,
    ADD INDEX `order_id` (`order_id`);
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Create payment_id field and make it primary
    $sql = "
    ALTER TABLE `%singenico_payments`
    ADD COLUMN `payment_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Payment ID' FIRST,
    ADD PRIMARY KEY (`payment_id`);
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Create payment_data field
    $sql = "
    ALTER TABLE `%singenico_payments`
    ADD COLUMN `payment_data` TEXT NULL DEFAULT NULL COMMENT 'Transaction data' AFTER `currency`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Create created_at field
    $sql = "
    ALTER TABLE `%singenico_payments`
    ADD COLUMN `created_at` DATETIME NULL DEFAULT NULL AFTER `payment_data`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Create updated_at field
    $sql = "
    ALTER TABLE `%singenico_payments`
    ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Rename cardno -> card_no
    $sql = "
    ALTER TABLE `%singenico_payments`
    CHANGE COLUMN `cardno` `card_no` VARCHAR(50) NULL DEFAULT NULL COMMENT 'The masked credit card number' AFTER `brand`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

    // Create cn field
    $sql = "
    ALTER TABLE `%singenico_payments`
    ADD COLUMN `cn` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Customer Name' AFTER `card_no`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));
}

function silentAliasesMigration()
{
    // Check is table exists
    $rows = Db::getInstance()->executeS(sprintf("SHOW TABLES LIKE '%singenico_aliases'", _DB_PREFIX_));
    if (count($rows) === 0) {
        return;
    }

    // Create cn field
    $sql = "
    ALTER TABLE `%singenico_aliases`
    ADD COLUMN `CN` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Customer Name' AFTER `CARDNO`;
		";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));
}
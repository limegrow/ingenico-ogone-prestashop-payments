<?php
/**
 * 2007-2019 Ingenico
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

namespace Ingenico\Setup;

use Ingenico\PrestaShopConnector;
use Module;
use Configuration;
use OrderState;
use Language;
use Db;
use PrestaShopDatabaseException;
use PrestaShopException;

class Install extends Module
{
    const DB_VERSION_TOKEN = 'INGENICO_DB_VERSION';

    /**
     * Install.
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        $this->createOrderStates();

        // Check is table exists
        $rows = Db::getInstance()->executeS(sprintf("SHOW TABLES LIKE '%singenico_payments'", _DB_PREFIX_));
        if (count($rows) > 0) {
            // It seems tables are already exists
            return;
        }

        // Install tables
        $this->createDbTables();

        // Set the version
        Configuration::updateGlobalValue(self::DB_VERSION_TOKEN, '1');
    }

    /**
     * Upgrade.
     *
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function upgrade()
    {
        if (false === Configuration::get(self::DB_VERSION_TOKEN)) {
            $this->silentPaymentsMigration();
            $this->silentAliasesMigration();

            Configuration::updateGlobalValue(self::DB_VERSION_TOKEN, '1');
        }
    }

    /**
     * Create Order Statuses in PrestaShop
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function createOrderStates()
    {
        $defaultLang = Configuration::get('PS_LANG_DEFAULT');

        // Add Order Status: Pending
        if (!(Configuration::get('OS_INGENICO_PENDING') > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Pending payment';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = 'ingenico_epayments';
            $OrderState->color = 'lightblue';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();

            Configuration::updateValue('OS_INGENICO_PENDING', $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/9.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/9.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add Order Status: Authorized
        if (!(Configuration::get('OS_INGENICO_AUTHORIZED') > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Authorized';
            $OrderState->invoice = false;
            $OrderState->send_email = true;
            $OrderState->module_name = 'ingenico_epayments';
            $OrderState->color = '#FF8C00';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'order_changed';
            $OrderState->add();

            Configuration::updateValue('OS_INGENICO_AUTHORIZED', $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/10.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/10.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #3 Order Status: Capture processing
        if (!(Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Capture processing';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = 'ingenico_epayments';
            $OrderState->color = '#CCCC00';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();

            Configuration::updateValue(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/9.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/9.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #4 Order Status: Refund processing
        if (!(Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Refund processing';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = 'ingenico_epayments';
            $OrderState->color = '#FFFF00';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'order_changed';
            $OrderState->add();

            Configuration::updateValue(PrestaShopConnector::PS_OS_REFUND_PROCESSING, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/9.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/9.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #5 Order Status: Refund refused
        if (!(Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Refund refused';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = 'ingenico_epayments';
            $OrderState->color = '#FF3333';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'order_changed';  // @todo: There is no refund_refused mail in PrestaShop by default
            $OrderState->add();

            Configuration::updateValue(PrestaShopConnector::PS_OS_REFUND_REFUSED, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/6.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/6.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #6 Order Status: Partial Refund
        if (!(Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Partial Refund';
            $OrderState->invoice = true;
            $OrderState->send_email = true;
            $OrderState->module_name = 'ingenico_epayments';
            $OrderState->color = '#ec2e15';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'refund';
            $OrderState->add();

            Configuration::updateValue(PrestaShopConnector::PS_OS_REFUND_PARTIAL, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/7.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/7.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Install translations
        $languages = Language::getLanguages(false);
        // @todo Load translate with $this->trans('order_status.pending_payment', [], 'messages')
        // @todo $this->trans('order_status.authorized', [], 'messages')
        foreach ($languages as $lang) {
            switch ($lang['language_code']) {
                case 'en-us':
                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_PENDING'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Pending payment',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_AUTHORIZED'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Authorized',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Capture processing',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund processing',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund refused',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Partial Refund',
                        'refund'
                    );
                    break;
                case 'es-es':
                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_PENDING'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Pago pendiente',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_AUTHORIZED'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorizado',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Capture processing',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund processing',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund refused',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Reembolso parcial',
                        'refund'
                    );
                    break;
                case 'de-de':
                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_PENDING'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Ausstehende Zahlung',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_AUTHORIZED'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorisiert',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Erfassen Sie die Verarbeitung',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rückerstattungsabwicklung',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rückerstattung abgelehnt',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Teilerstattung',
                        'refund'
                    );
                    break;
                case 'fr-fr':
                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_PENDING'),
                        $lang['id_lang'],
                        'Ingenico ePayment: En attente de paiement',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_AUTHORIZED'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorisé',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Traitement de capture',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Traitement des remboursements',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Remboursement refusé',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Remboursement partiel',
                        'refund'
                    );
                    break;
                case 'it-it':
                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_PENDING'),
                        $lang['id_lang'],
                        'Ingenico ePayment: In attesa di Pagamento',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_AUTHORIZED'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorizzato',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Cattura l\\\'elaborazione',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Elaborazione del rimborso',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rimborso rifiutato',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rimborso parziale',
                        'refund'
                    );
                    break;
                case 'nl-nl':
                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_PENDING'),
                        $lang['id_lang'],
                        'Ingenico ePayment: In afwachting van betaling',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get('OS_INGENICO_AUTHORIZED'),
                        $lang['id_lang'],
                        'Ingenico ePayment: Erkende',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Verwerking van betalingen',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Verwerking van terugbetaling',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Restitutie geweigerd',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(PrestaShopConnector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Gedeeltelijke terugbetaling',
                        'refund'
                    );
                    break;
            }
        }

        return true;
    }

    /**
     * Install Ingenico Connector Tables
     *
     * @return bool
     */
    private function createDbTables()
    {
        // Payment logs
        $sql = "
          CREATE TABLE IF NOT EXISTS `%singenico_payments` (
            `payment_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Paymet',
            `order_id` int(11) NOT NULL COMMENT 'Order ID',
            `pay_id` varchar(50) DEFAULT NULL COMMENT 'Payment reference',
            `pay_id_sub` varchar(50) DEFAULT NULL COMMENT 'The history level ID of the maintenance operation on the PAYID',
            `status` int(11) DEFAULT NULL COMMENT 'Transaction status',
            `pm` varchar(50) DEFAULT NULL COMMENT 'Payment method',
            `brand` varchar(50) DEFAULT NULL COMMENT 'Card brand or similar information for other payment methods',
            `card_no` varchar(50) DEFAULT NULL COMMENT 'The masked credit card number',
            `cn` varchar(50) DEFAULT NULL,
            `amount` decimal(10,2) DEFAULT NULL COMMENT 'Order amount',
            `currency` varchar(50) DEFAULT NULL COMMENT 'Order currency',
            `payment_data` text DEFAULT NULL COMMENT 'Transaction data',
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`payment_id`),
            KEY `pay_id_sub` (`pay_id_sub`),
            KEY `pay_id` (`pay_id`),
            KEY `order_id` (`order_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

        // Totals
        $sql = "
          CREATE TABLE IF NOT EXISTS `%singenico_totals` (
          `order_id` int(11) NOT NULL,
          `captured_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Captured Amount',
          `cancelled_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Cancelled Amount',
          `refunded_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Refunded Amount',
          PRIMARY KEY (`order_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ";
        Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

        // Reminder
        $sql = "
          CREATE TABLE IF NOT EXISTS `%singenico_reminder` (
        `order_id` int(11) NOT NULL,
        `is_sent` int(11) DEFAULT NULL,
        `secure_token` varchar(50) DEFAULT NULL,
        `notification_date` datetime DEFAULT NULL,
        PRIMARY KEY (`order_id`),
        UNIQUE KEY `secure_token` (`secure_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		";
        Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

        // Aliases
        $sql = "
          CREATE TABLE IF NOT EXISTS `%singenico_aliases` (
        `alias_id` int(11) NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) DEFAULT NULL,
        `ALIAS` varchar(50) DEFAULT NULL,
        `BRAND` varchar(50) DEFAULT NULL,
        `CARDNO` varchar(50) DEFAULT NULL,
        `CN` varchar(50) DEFAULT NULL,
        `BIN` varchar(50) DEFAULT NULL,
        `PM` varchar(50) DEFAULT NULL,
        `ED` varchar(50) DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`alias_id`),
        UNIQUE KEY `ALIAS` (`ALIAS`),
        KEY `customer_id` (`customer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;    
        ";
        Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

        // Actualisation info for cron
        $sql = "
          CREATE TABLE IF NOT EXISTS `%singenico_cron` (
        `order_id` int(11) NOT NULL AUTO_INCREMENT,
        `is_actualised` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`order_id`),
        KEY `is_actualised` (`is_actualised`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));

        return true;
    }

    /**
     * Update Order State Language
     * @param $orderStateId
     * @param $langId
     * @param $name
     * @param $template
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    private function updateOrderStateLang($orderStateId, $langId, $name, $template)
    {
        return Db::getInstance()->insert(
            'order_state_lang',
            [
                'id_order_state' => (int) $orderStateId,
                'id_lang' => (int) $langId,
                'name' => $name,
                'template' => $template
            ],
            false,
            false,
            Db::ON_DUPLICATE_KEY
        );
    }

    private function silentPaymentsMigration()
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

    private function silentAliasesMigration()
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
}

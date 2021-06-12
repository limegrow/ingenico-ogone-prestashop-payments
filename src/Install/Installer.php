<?php

declare(strict_types=1);

namespace Ingenico\Payment\Install;

use Db;
use Ingenico\Payment\Connector;
use Module;
use PrestaShop\PrestaShop\Core\Domain\Order\Status\OrderStatusColor;
use Configuration;
use OrderState;
use Language;
use PrestaShopDatabaseException;

class Installer
{
    /**
     * @var Db
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * Module's installation entry point.
     *
     * @param Module $module
     *
     * @return bool
     */
    public function install(Module $module): bool
    {
        if (!$this->registerHooks($module)) {
            return false;
        }

        if (!$this->installDatabase()) {
            return false;
        }

        if (!$this->createOrderStates($module)) {
            return false;
        }

        return true;
    }

    /**
     * Module's uninstallation entry point.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return true;
    }

    /**
     * Install the database modifications required for this module.
     *
     * @return bool
     */
    private function installDatabase(): bool
    {
        $queries = [
            // Payment logs
            "CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ . "ingenico_payments` (
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
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",

            // Totals
            "CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ . "ingenico_totals` (
              `order_id` int(11) NOT NULL,
              `captured_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Captured Amount',
              `cancelled_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Cancelled Amount',
              `refunded_total` decimal(10,2) DEFAULT '0.00' COMMENT 'Refunded Amount',
              PRIMARY KEY (`order_id`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",

            // Reminder
            "CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ . "ingenico_reminder` (
              `order_id` int(11) NOT NULL,
              `is_sent` int(11) DEFAULT NULL,
              `secure_token` varchar(50) DEFAULT NULL,
              `notification_date` datetime DEFAULT NULL,
              PRIMARY KEY (`order_id`),
              UNIQUE KEY `secure_token` (`secure_token`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",

            // Aliases
            "CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ . "ingenico_aliases` (
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
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",

            // Actualisation info for cron
            "CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ . "ingenico_cron` (
              `order_id` int(11) NOT NULL AUTO_INCREMENT,
              `is_actualised` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`order_id`),
              KEY `is_actualised` (`is_actualised`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;",

            // Payment ID in cart
            "CREATE TABLE IF NOT EXISTS `". _DB_PREFIX_ . "ingenico_cart` (
              `id_cart` int DEFAULT NULL,
              `payment_id` varchar(50) DEFAULT NULL,
              UNIQUE KEY `id_cart` (`id_cart`)
            ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;"
        ];

        return $this->executeQueries($queries);
    }

    /**
     * Register hooks for the module.
     *
     * @param Module $module
     *
     * @return bool
     */
    private function registerHooks(Module $module): bool
    {
        $hooks = [
            'header',
            'backOfficeHeader',
            'displayBackOfficeOrderActions', // @deprecated since 1.7.7
            'displayAdminOrderSide',
            'displayAdminOrder',
            'actionCronJob',
            'paymentOptions',
            'paymentReturn',
            'actionEmailSendBefore',
            'actionGetAdminOrderButtons',
            'displayCustomerAccount'
        ];

        return (bool) $module->registerHook($hooks);
    }

    /**
     * A helper that executes multiple database queries.
     *
     * @param array $queries
     *
     * @return bool
     */
    private function executeQueries(array $queries): bool
    {
        foreach ($queries as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create Order Statuses in PrestaShop
     *
     * @return bool
     */
    private function createOrderStates(Module $module)
    {
        $defaultLang = Configuration::get('PS_LANG_DEFAULT');

        // Add Order Status: Pending
        if (!(Configuration::get(Connector::PS_OS_PENDING) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Pending payment';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $module->name;
            $OrderState->color = class_exists('OrderStatusColor') ? OrderStatusColor::AWAITING_PAYMENT : '#34209E';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();

            Configuration::updateValue(Connector::PS_OS_PENDING, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/9.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/9.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add Order Status: Authorized
        if (!(Configuration::get(Connector::PS_OS_AUTHORIZED) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Authorized';
            $OrderState->invoice = false;
            $OrderState->send_email = true;
            $OrderState->module_name = $module->name;
            $OrderState->color = class_exists('OrderStatusColor') ? OrderStatusColor::ACCEPTED_PAYMENT : '#3498D8';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'order_changed';
            $OrderState->add();

            Configuration::updateValue(Connector::PS_OS_AUTHORIZED, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/10.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/10.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #3 Order Status: Capture processing
        if (!(Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Capture processing';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $module->name;
            $OrderState->color = class_exists('OrderStatusColor') ? OrderStatusColor::ACCEPTED_PAYMENT : '#3498D8';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'preparation';
            $OrderState->add();

            Configuration::updateValue(Connector::PS_OS_CAPTURE_PROCESSING, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/9.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/9.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #4 Order Status: Refund processing
        if (!(Configuration::get(Connector::PS_OS_REFUND_PROCESSING) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Refund processing';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $module->name;
            $OrderState->color = class_exists('OrderStatusColor') ? OrderStatusColor::ACCEPTED_PAYMENT : '#3498D8';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'order_changed';
            $OrderState->add();

            Configuration::updateValue(Connector::PS_OS_REFUND_PROCESSING, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/9.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/9.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #5 Order Status: Refund refused
        if (!(Configuration::get(Connector::PS_OS_REFUND_REFUSED) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Refund refused';
            $OrderState->invoice = false;
            $OrderState->send_email = false;
            $OrderState->module_name = $module->name;
            $OrderState->color = class_exists('OrderStatusColor') ? OrderStatusColor::SPECIAL : '#2C3E50';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = true;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'order_changed';  // @todo: There is no refund_refused mail in PrestaShop by default
            $OrderState->add();

            Configuration::updateValue(Connector::PS_OS_REFUND_REFUSED, $OrderState->id);

            if (file_exists(_PS_ROOT_DIR_ . '/img/os/6.gif')) {
                @copy(_PS_ROOT_DIR_ . '/img/os/6.gif', _PS_ROOT_DIR_ . '/img/os/' . $OrderState->id . '.gif');
            }
        }

        // Add #6 Order Status: Partial Refund
        if (!(Configuration::get(Connector::PS_OS_REFUND_PARTIAL) > 0)) {
            $OrderState = new OrderState(null, $defaultLang);
            $OrderState->name = 'Ingenico ePayment: Partial Refund';
            $OrderState->invoice = true;
            $OrderState->send_email = true;
            $OrderState->module_name = $module->name;
            $OrderState->color = class_exists('OrderStatusColor') ? OrderStatusColor::COMPLETED : '#01b887';
            $OrderState->unremovable = true;
            $OrderState->hidden = false;
            $OrderState->logable = false;
            $OrderState->delivery = false;
            $OrderState->shipped = false;
            $OrderState->paid = false;
            $OrderState->deleted = false;
            $OrderState->template = 'refund';
            $OrderState->add();

            Configuration::updateValue(Connector::PS_OS_REFUND_PARTIAL, $OrderState->id);

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
                        Configuration::get(Connector::PS_OS_PENDING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Pending payment',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_AUTHORIZED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Authorized',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Capture processing',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund processing',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund refused',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Partial Refund',
                        'refund'
                    );
                    break;
                case 'es-es':
                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_PENDING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Pago pendiente',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_AUTHORIZED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorizado',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Capture processing',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund processing',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Refund refused',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Reembolso parcial',
                        'refund'
                    );
                    break;
                case 'de-de':
                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_PENDING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Ausstehende Zahlung',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_AUTHORIZED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorisiert',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Erfassen Sie die Verarbeitung',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rückerstattungsabwicklung',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rückerstattung abgelehnt',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Teilerstattung',
                        'refund'
                    );
                    break;
                case 'fr-fr':
                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_PENDING),
                        $lang['id_lang'],
                        'Ingenico ePayment: En attente de paiement',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_AUTHORIZED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorisé',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Traitement de capture',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Traitement des remboursements',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Remboursement refusé',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Remboursement partiel',
                        'refund'
                    );
                    break;
                case 'it-it':
                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_PENDING),
                        $lang['id_lang'],
                        'Ingenico ePayment: In attesa di Pagamento',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_AUTHORIZED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Autorizzato',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Cattura l\\\'elaborazione',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Elaborazione del rimborso',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rimborso rifiutato',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PARTIAL),
                        $lang['id_lang'],
                        'Ingenico ePayment: Rimborso parziale',
                        'refund'
                    );
                    break;
                case 'nl-nl':
                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_PENDING),
                        $lang['id_lang'],
                        'Ingenico ePayment: In afwachting van betaling',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_AUTHORIZED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Erkende',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Verwerking van betalingen',
                        'preparation'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PROCESSING),
                        $lang['id_lang'],
                        'Ingenico ePayment: Verwerking van terugbetaling',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_REFUSED),
                        $lang['id_lang'],
                        'Ingenico ePayment: Restitutie geweigerd',
                        'order_changed'
                    );

                    $this->updateOrderStateLang(
                        Configuration::get(Connector::PS_OS_REFUND_PARTIAL),
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
}

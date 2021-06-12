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

use PrestaShop\PrestaShop\Core\Domain\Order\Status\OrderStatusColor;
use Ingenico\Payment\Connector;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param \Ingenico_Epayments $module
 * @return bool
 */
function upgrade_module_5_0_0($module)
{
    $module->registerHook('actionEmailSendBefore');
    $module->registerHook('actionGetAdminOrderButtons');
    $module->registerHook('displayAdminOrderSide');
    $module->registerHook('displayCustomerAccount');

    // Payment ID in cart
    silentCartTableMigration();

    // Order statuses update
    orderStatusesUpdate();

    return true;
}

function silentCartTableMigration()
{
    // Check is table exists
    $rows = Db::getInstance()->executeS(sprintf("SHOW TABLES LIKE '%singenico_cart'", _DB_PREFIX_));
    if (count($rows) === 0) {
        return;
    }

    // Payment ID in cart
    $sql = "
          CREATE TABLE IF NOT EXISTS `%singenico_cart` (
        `id_cart` int DEFAULT NULL,
        `payment_id` varchar(50) DEFAULT NULL,
        UNIQUE KEY `id_cart` (`id_cart`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
    Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_));
}

function orderStatusesUpdate()
{
    if (Configuration::get(Connector::PS_OS_PENDING) > 0) {
        $color = class_exists('OrderStatusColor') ? OrderStatusColor::AWAITING_PAYMENT : '#34209E';

        Db::getInstance()->update(
            'order_state',
            [
                'color' => $color
            ],
            sprintf('id_order_state = %d', (int) Configuration::get(Connector::PS_OS_PENDING))
        );
    }

    if (Configuration::get(Connector::PS_OS_AUTHORIZED) > 0) {
        $color = class_exists('OrderStatusColor') ? OrderStatusColor::ACCEPTED_PAYMENT : '#3498D8';

        Db::getInstance()->update(
            'order_state',
            [
                'color' => $color
            ],
            sprintf('id_order_state = %d', (int) Configuration::get(Connector::PS_OS_AUTHORIZED))
        );
    }

    if (Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING) > 0) {
        $color = class_exists('OrderStatusColor') ? OrderStatusColor::ACCEPTED_PAYMENT : '#3498D8';

        Db::getInstance()->update(
            'order_state',
            [
                'color' => $color
            ],
            sprintf('id_order_state = %d', (int) Configuration::get(Connector::PS_OS_CAPTURE_PROCESSING))
        );
    }

    if (Configuration::get(Connector::PS_OS_REFUND_PROCESSING) > 0) {
        $color = class_exists('OrderStatusColor') ? OrderStatusColor::ACCEPTED_PAYMENT : '#3498D8';

        Db::getInstance()->update(
            'order_state',
            [
                'color' => $color
            ],
            sprintf('id_order_state = %d', (int) Configuration::get(Connector::PS_OS_REFUND_PROCESSING))
        );
    }

    if (Configuration::get(Connector::PS_OS_REFUND_REFUSED) > 0) {
        $color = class_exists('OrderStatusColor') ? OrderStatusColor::SPECIAL : '#2C3E50';

        Db::getInstance()->update(
            'order_state',
            [
                'color' => $color
            ],
            sprintf('id_order_state = %d', (int) Configuration::get(Connector::PS_OS_REFUND_REFUSED))
        );
    }

    if (Configuration::get(Connector::PS_OS_REFUND_PARTIAL) > 0) {
        $color = class_exists('OrderStatusColor') ? OrderStatusColor::COMPLETED : '#01b887';

        Db::getInstance()->update(
            'order_state',
            [
                'color' => $color
            ],
            sprintf('id_order_state = %d', (int) Configuration::get(Connector::PS_OS_REFUND_PARTIAL))
        );
    }
}
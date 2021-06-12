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

namespace Ingenico\Payment;

use Db;
use DbQuery;
use Tools;
use PrestaShopDatabaseException;

class Reminder
{
    private $connector;

    /**
     * Reminder constructor.
     * @param $connector
     */
    public function __construct($connector)
    {
        $this->connector = $connector;
    }

    /**
     * Set Reminder for OrderID
     * @param $orderId
     * @param $timestamp
     * @param bool $is_sent
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function setReminder($orderId, $timestamp, $is_sent = false)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        $token = Tools::strtoupper(Tools::passwdGen(9, 'NO_NUMERIC'));
        return Db::getInstance()->insert(
            'ingenico_reminder',
            [
                'order_id' => (int) $orderId,
                'notification_date' => date('Y-m-d H:i:s', $timestamp),
                'is_sent' => $is_sent ? 1 : 0,
                'secure_token' => $token,
            ],
            false,
            false,
            Db::INSERT_IGNORE
        );
    }

    /**
     * Get Reminder for OrderID
     * @param $orderId
     * @return array|bool|object|null
     */
    public function getReminder($orderId)
    {
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('ingenico_reminder', 'r');
        $sql->where(sprintf('r.order_id = %d', (int) $orderId));

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Get Reminder By Key
     * @param $key
     * @return array|bool|object|null
     */
    public function getReminderByKey($key)
    {
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('ingenico_reminder', 'r');
        $sql->where(sprintf('r.secure_token = "%s"', pSQL($key)));

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Update Reminder for OrderID
     * @param $orderId
     * @param $data
     */
    public function updateReminder($orderId, $data)
    {
        Db::getInstance()->update(
            'ingenico_reminder',
            $data,
            sprintf('order_id = %d', (int) $orderId)
        );
    }

    /**
     * Get Complete Payment Link
     * @param $orderId
     * @return string
     * @throws PrestaShopDatabaseException
     */
    public function getCompletePaymentLink($orderId)
    {
        $data = $this->getReminder($orderId);
        if (count($data) === 0) {
            // Generate secret key
            $days = abs($this->connector->coreLibrary->getConfiguration()->getSettingsReminderemailDays());
            $timestamp = strtotime("+{$days} days");
            $this->setReminder($orderId, $timestamp, false);
        }

        $data = $this->getReminder($orderId);

        return \Context::getContext()->link->getModuleLink(
            'ingenico_epayments',
            'pay',
            [
                'secret_key' => $data['secure_token']
            ]
        );
    }

    /**
     * Get Pending Reminders
     * @return array
     */
    public function getPendingReminders()
    {
        $sql = new DbQuery();
        $sql->select('r.*');
        $sql->from('ingenico_reminder', 'r');
        $sql->where(sprintf('r.is_sent = %d', 0));

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Check if Reminder is sent
     * @param $orderId
     * @return bool
     */
    public function isReminderSent($orderId)
    {
        $sql = new DbQuery();
        $sql->select('r.is_sent');
        $sql->from('ingenico_reminder', 'r');
        $sql->where(sprintf('r.order_id = %d', (int) $orderId));

        if ($value = Db::getInstance()->getValue($sql)) {
            return (bool) $value;
        }

        return false;
    }
}

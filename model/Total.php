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

namespace Ingenico\Model;

use Db;
use Order;

class Total
{
    /** @var \Ingenico_epayments */
    public $module;

    /**
     * Reminder constructor.
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Add Captured Amount
     * @param int $orderId
     * @param float $canceledAmount
     *
     * @return bool
     */
    public function addCancelledAmount($orderId, $canceledAmount)
    {
        $sql = "
          INSERT INTO %singenico_totals (order_id, cancelled_total) VALUES (%s, %s)
          ON DUPLICATE KEY UPDATE cancelled_total = IFNULL(cancelled_total, 0) + %s;
        ";

        return Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_, (int) $orderId, (float) $canceledAmount, (float) $canceledAmount));
    }

    /**
     * Get Cancelled Amount
     * @param $orderId
     * @return float
     */
    public function getCancelledAmount($orderId)
    {
        $sql = sprintf('SELECT cancelled_total FROM `%singenico_totals` WHERE order_id = %d;', _DB_PREFIX_, (int) $orderId);

        return (float) Db::getInstance()->getValue($sql, false);
    }

    /**
     * Get Available Amount for Cancel
     * @param int $orderId
     *
     * @return float
     */
    public function getAvailableCancelAmount($orderId)
    {
        return (float) bcsub($this->getOrderTotal($orderId), $this->getCancelledAmount($orderId), 2);
    }

    /**
     * Add Captured Amount
     * @param int $orderId
     * @param float $capturedAmount
     *
     * @return bool
     */
    public function addCapturedAmount($orderId, $capturedAmount)
    {
        $sql = "
          INSERT INTO %singenico_totals (order_id, captured_total) VALUES (%s, %s)
          ON DUPLICATE KEY UPDATE captured_total = IFNULL(captured_total, 0) + %s;
        ";

        return Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_, (int) $orderId, (float) $capturedAmount, (float) $capturedAmount));
    }

    /**
     * Get Captured Amount
     * @param $orderId
     * @return float
     */
    public function getCapturedAmount($orderId)
    {
        $sql = sprintf('SELECT captured_total FROM `%singenico_totals` WHERE order_id = %d;', _DB_PREFIX_, (int) $orderId);

        return (float) Db::getInstance()->getValue($sql, false);
    }

    /**
     * Get Available Amount for Capture
     * @param int $orderId
     *
     * @return float
     */
    public function getAvailableCaptureAmount($orderId)
    {
        return (float) bcsub($this->getOrderTotal($orderId), $this->getCapturedAmount($orderId), 2);
    }

    /**
     * Add Refunded Amount
     * @param int $orderId
     * @param float $refundedAmount
     *
     * @return bool
     */
    public function addRefundedAmount($orderId, $refundedAmount)
    {
        $sql = "
          INSERT INTO %singenico_totals (order_id, refunded_total) VALUES (%s, %s)
          ON DUPLICATE KEY UPDATE refunded_total = IFNULL(refunded_total, 0) + %s;
        ";

        return Db::getInstance()->execute(sprintf($sql, _DB_PREFIX_, (int) $orderId, (float) $refundedAmount, (float) $refundedAmount));
    }

    /**
     * Get Refunded Amount
     * @param $orderId
     * @return float
     */
    public function getRefundedAmount($orderId)
    {
        $sql = sprintf('SELECT refunded_total FROM `%singenico_totals` WHERE order_id = %d;', _DB_PREFIX_, (int) $orderId);

        return (float) Db::getInstance()->getValue($sql, false);
    }

    /**
     * Get Available Amount for Refund
     * @param int $orderId
     *
     * @return float
     */
    public function getAvailableRefundAmount($orderId)
    {
        return (float) bcsub($this->getOrderTotal($orderId), $this->getRefundedAmount($orderId), 2);
    }

    /**
     * Get Order Amount
     * @param $orderId
     * @return float
     */
    private function getOrderTotal($orderId)
    {
        $order = new Order($orderId);
        return $order->total_paid_tax_incl;
    }
}

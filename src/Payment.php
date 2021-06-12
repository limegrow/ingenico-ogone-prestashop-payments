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

class Payment
{
    /**
     * Save Payment data.
     *
     * @param $orderId
     * @param \IngenicoClient\Payment $data
     *
     * @return bool
     */
    public function logIngenicoPayment($orderId, \IngenicoClient\Payment $data)
    {
        // Check payments by pay_id and pay_id_sub
        $sql = new DbQuery();
        $sql->select('p.*');
        $sql->from('ingenico_payments', 'p');
        $sql->where('p.pay_id = "' . pSQL($data->getPayId()) . '"');
        $sql->where('p.pay_id_sub = "' . pSQL($data->getPayIdSub()) . '"');
        $rows = Db::getInstance()->executeS($sql);

        if (count($rows) > 0) {
            // Update
            $row = array_shift($rows);

            return Db::getInstance()->update(
                'ingenico_payments',
                [
                    'order_id' => (int) $orderId,
                    'pay_id' => $data->getPayId(),
                    'pay_id_sub' => $data->getPayIdSub(),
                    'status' => $data->getStatus(),
                    'pm' => $data->getPm(),
                    'brand' => $data->getBrand(),
                    'card_no' => $data->getCardNo(),
                    'cn' => $data->getCn(),
                    'amount' => $data->getAmount(),
                    'currency' => $data->getCurrency(),
                    'payment_data' => json_encode($data->getData(), true),
                    'updated_at' => date('Y-m-d H:i:s', time())
                ],
                sprintf('payment_id = %d', (int) $row['payment_id'])
            );
        }

        return Db::getInstance()->insert(
            'ingenico_payments',
            [
                'order_id' => (int) $orderId,
                'pay_id' => $data->getPayId(),
                'pay_id_sub' => $data->getPayIdSub(),
                'status' => $data->getStatus(),
                'pm' => $data->getPm(),
                'brand' => $data->getBrand(),
                'card_no' => $data->getCardNo(),
                'cn' => $data->getCn(),
                'amount' => $data->getAmount(),
                'currency' => $data->getCurrency(),
                'payment_data' => json_encode($data->getData(), true),
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time())
            ],
            false,
            false,
            Db::INSERT
        );
    }

    /**
     * Get Payment Data By OrderId.
     *
     * @param $orderId
     *
     * @return array
     */
    public function getIngenicoPaymentLog($orderId)
    {
        $sql = new DbQuery();
        $sql->select('p.*');
        $sql->from('ingenico_payments', 'p');
        $sql->where(sprintf('p.order_id = %d', (int) $orderId));
        $sql->orderBy('p.pay_id_sub DESC');

        $row = Db::getInstance()->getRow($sql, false);
        unset($row['payment_id'], $row['payment_data']);

        return $row;
    }

    /**
     * Get Payment Data By PayId.
     *
     * @param $payId
     * @return array
     */
    public function getIngenicoPaymentById($payId)
    {
        $sql = new DbQuery();
        $sql->select('p.*');
        $sql->from('ingenico_payments', 'p');
        $sql->where(sprintf('p.pay_id = %d', (int) $payId));
        $sql->orderBy('p.pay_id_sub DESC');

        $row = Db::getInstance()->getRow($sql, false);
        unset($row['payment_id'], $row['payment_data']);

        return $row;
    }

    /**
     * Get PayId By OrderId.
     *
     * @param $orderId
     * @return string|false
     */
    public function getIngenicoPayIdByOrderId($orderId)
    {
        $sql = new DbQuery();
        $sql->select('p.pay_id');
        $sql->from('ingenico_payments', 'p');
        $sql->where(sprintf('p.order_id = %d', (int) $orderId));

        return Db::getInstance()->getValue($sql, false);
    }
}

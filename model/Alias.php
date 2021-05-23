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

namespace Ingenico\Model;

use Db;
use DbQuery;

class Alias
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
     * Get Alias by CustomerId
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId)
    {
        $sql = new DbQuery();
        $sql->select('a.*');
        $sql->from('ingenico_aliases', 'a');
        $sql->where(sprintf('a.customer_id = %d', (int) $customerId));
        if ($rows = Db::getInstance()->executeS($sql)) {
            return $rows;
        }

        return [];
    }

    /**
     * Get Alias
     * @param $aliasId
     * @return array|false
     */
    public function getAlias($aliasId)
    {
        $sql = new DbQuery();
        $sql->select('a.*');
        $sql->from('ingenico_aliases', 'a');
        $sql->where(sprintf('a.alias_id = %d', (int) $aliasId));

        if ($row = Db::getInstance()->getRow($sql)) {
            // Update last access date
            Db::getInstance()->update(
                'ingenico_aliases',
                [
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                sprintf('alias_id = %d', (int) $row['alias_id'])
            );

            return $row;
        }

        return false;
    }

    /**
     * Save Customer Alias
     * @param int $customerId
     * @param array $data
     * @return bool
     */
    public function saveAlias($customerId, $data)
    {
        if (empty($data['ALIAS'])) {
            throw new \Exception('Alias can\'t be empty.');
        }

        $alias = $data['ALIAS'] ?? '';
        $brand = $data['BRAND'] ?? '';
        $cardNo = $data['CARDNO'] ?? '';
        $cn = $data['CN'] ?? '';
        $bin = $data['BIN'] ?? '';
        $pm = $data['PM'] ?? '';
        $ed = $data['ED'] ?? '';

        // Check Alias
        $sql = new DbQuery();
        $sql->select('a.*');
        $sql->from('ingenico_aliases', 'a');
        $sql->where('a.ALIAS = "' . pSQL($alias) . '"');
        $rows = Db::getInstance()->executeS($sql);

        if (count($rows) > 0) {
            // Update
            $row = array_shift($rows);

            return Db::getInstance()->update(
                'ingenico_aliases',
                [
                    'customer_id' => (int) $customerId,
                    'ALIAS' => $alias,
                    'BRAND' => $brand,
                    'CARDNO' => $cardNo,
                    'CN' => $cn,
                    'BIN' => $bin,
                    'PM' => $pm,
                    'ED' => $ed,
                    'updated_at' => date('Y-m-d H:i:s', time())
                ],
                sprintf('ALIAS = %d', (int) $row['alias_id'])
            );
        }

        return Db::getInstance()->insert(
            'ingenico_aliases',
            [
                'customer_id' => (int) $customerId,
                'ALIAS' => $alias,
                'BRAND' => $brand,
                'CARDNO' => $cardNo,
                'CN' => $cn,
                'BIN' => $bin,
                'PM' => $pm,
                'ED' => $ed,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            false,
            false,
            Db::INSERT_IGNORE
        );
    }
}

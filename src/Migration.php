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

use Ingenico\Payment\Utils;
use Module;
use Configuration;
use Exception;
use Db;

class Migration extends Module
{
    /**
     * If Old Module is installed
     *
     * @return bool
     */
    public static function isOldModuleInstalled()
    {
        return Module::isInstalled('ogone') & Module::isEnabled('ogone');
    }

    /**
     * If the Migration was successfully performed
     *
     * @return bool
     */
    public static function isMigrationWasPerformed()
    {
        return (bool) Configuration::get('INGENICO_MIGRATION_DONE');
    }

    /**
     * Set the Migration was performed
     */
    public static function setIsMigrationPerformed()
    {
        Configuration::updateValue('INGENICO_MIGRATION_DONE', 1);
    }

    /**
     * Get Ingenico Account Details
     *
     * @param $pspid
     * @return array
     */
    public function getIngenicoDetails($pspid)
    {
        // @todo Do HTTP Call
        return [];
    }

    /**
     * Update the Ingenico Account details
     *
     * @param array $details
     * @return bool
     */
    public function updateIngenicoDetails(array $details)
    {
        // @todo Do HTTP Call
        return true;
    }

    /**
     * Generate Hash
     * @param int $length
     * @return string
     * @throws Exception
     */
    public static function generateHash($length = 40)
    {
        $rand = '';

        while (true) {
            $byte = random_bytes(1);
            $matches = [];
            preg_match('/[a-zA-Z0-9_\-\+\.]/', $byte, $matches);
            if (isset($matches[0])) {
                $rand .= $matches[0];
            }

            if (strlen($rand) === $length) {
                break;
            }
        }

        return $rand;
    }

    /**
     * Save Conf
     * @param $key
     * @param $data
     */
    public static function saveConf($key, $data)
    {
        if (is_array($data)) {
            foreach ($data as $key1 => $value) {
                if (is_scalar($value)) {
                    $data[$key1] = base64_encode($value);
                }
            }
        }

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        // Save Configuration
        $success = Configuration::updateValue($key, $data);
        if (!$success) {
            throw new Exception('Could not update configuration');
        }
    }

    /**
     * Get Conf
     * @param $key
     * @return array|mixed|string
     * @throws Exception
     */
    public static function getConf($key)
    {
        // Load Saved Configuration
        $data = Configuration::get($key);
        if (empty($data)) {
            throw new Exception('No saved data.');
        }

        $data = json_decode($data, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception('Failed to decode saved value.');
        }

        if (is_array($data)) {
            foreach ($data as $key1 => $value) {
                if (is_string($value)) {
                    $decoded = @base64_decode($value);
                    if ($decoded) {
                        $data[$key1] = $decoded;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get IDs of Aliases
     *
     * @return array
     */
    public function getAliasesIDs()
    {
        $query = 'SELECT id_ogone_alias 
                    FROM ' . _DB_PREFIX_ . 'ogone_alias 
                    WHERE active = 1 AND is_temporary = 0 AND (expiry_date > DATE(NOW()) OR expiry_date = "0000-00-00 00:00:00")';

        $aliases = Db::getInstance()->executeS($query);
        return array_column($aliases, 'id_ogone_alias');
    }

    /**
     * Get Alias by ID
     *
     * @param mixed $id
     * @return array|false
     * @throws Exception
     */
    public function getAlias($id)
    {
        $sql = new \DbQuery();
        $sql->select('oa.*');
        $sql->from('ogone_alias', 'oa');
        $sql->where(sprintf('oa.id_ogone_alias = %s', pSQL($id)));
        if ($row = Db::getInstance()->getRow($sql)) {
            $row['alias'] = $this->decryptAlias($row['alias']);

            return $row;
        }

        return false;
    }

    /**
     * Decrypt Alias
     *
     * @param string $alias
     * @return bool|string
     * @throws Exception
     */
    private function decryptAlias($alias)
    {
        if (\Tools::substr($alias, 0, 1) === '!') {
            $encrypted = \Tools::substr($alias, 1);
            return (new \PhpEncryption(_NEW_COOKIE_KEY_))->decrypt($encrypted);
        }

        return $alias;
    }
}

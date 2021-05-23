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

namespace Ingenico;

use Configuration;
use Tools;

class Utils
{
    const MODULE_NAME = 'ingenico_epayments';

    /**
     * Returns configuration value
     *
     * @param $key
     * @return string
     */
    public static function getConfig($key)
    {
        return Configuration::get(self::prefixed($key));
    }

    /**
     * @param $key
     * @return string
     */
    public static function prefixed($key)
    {
        return Tools::strtoupper(self::MODULE_NAME . '_' . str_replace('.', '_', $key));
    }

    /**
     * Updates configuration values
     *
     * @param $key
     * @param $value
     * @return string
     */
    public static function updateConfig($key, $value)
    {
        return Configuration::updateValue(self::prefixed($key), $value, true);
    }

    /**
     * Set Value in Session
     * @param $key
     * @param $value
     */
    public static function setSessionValue($key, $value)
    {
        //if (session_status() == PHP_SESSION_NONE) {
            //session_start();
        //}

        //$_SESSION[$key] = $value;
        // Workaround for Safari iFrame Cookie drama (Reference: https://gist.github.com/iansltx/18caf551baaa60b79206)
        // When saving token into cookies, save it into db as well
        // so that when we compare token from inside an iFrame then Safari can get the token from db
        if ($key === 'ingenico_token' && \Tools::getIsset('Alias_OrderId')) {
            self::updateConfig($key . '_' . \Tools::getValue('Alias_OrderId'), $value);
        }

        \Context::getContext()->cookie->{'ingenico_' . $key} = $value;
    }

    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public static function getSessionValues()
    {
        $result = [];
        $values = \Context::getContext()->cookie->getAll();
        foreach ($values as $key => $value) {
            if (strpos($key, 'ingenico_') !== false) {
                $result[str_replace('ingenico_', '', $key)] = $value;
            }
        }

        return $result;
    }

    /**
     * Get Value from Session
     * @param $key
     * @return bool|mixed
     */
    public static function getSessionValue($key)
    {
        //if (session_status() == PHP_SESSION_NONE) {
        //    return false;
        //}

        //if (isset($_SESSION[$key])) {
        //    return $_SESSION[$key];
        //}
        $value = \Context::getContext()->cookie->{'ingenico_' . $key};

        // Workaround for Safari iFrame Cookie drama (Reference: https://gist.github.com/iansltx/18caf551baaa60b79206)
        // When saving token into cookies, save it into db as well
        // so that when we compare token from inside an iFrame then Safari can get the token from db
        if ($key === 'ingenico_token' && empty($value) && \Tools::getIsset('order_id')) {
            $value = self::getConfig($key . '_' . \Tools::getValue('order_id'));
        }

        return $value;
    }

    /**
     * Unset Session's value
     * @param $key
     */
    public static function unsetSessionValue($key)
    {
        //if (session_status() == PHP_SESSION_NONE) {
        //    return;
        //}

        //if (isset($_SESSION[$key])) {
            //unset($_SESSION[$key]);
        //}

        // Workaround for Safari iFrame Cookie drama (Reference: https://gist.github.com/iansltx/18caf551baaa60b79206)
        // When saving token into cookies, save it into db as well
        // so that when we compare token from inside an iFrame then Safari can get the token from db
        if ($key === 'ingenico_token' && \Tools::getIsset('order_id')) {
            \Configuration::deleteByName($key . '_' . \Tools::getValue('order_id'));
        }

        if (\Context::getContext()->cookie->{'ingenico_' . $key}) {
            unset(\Context::getContext()->cookie->{'ingenico_' . $key});
        }
    }
}

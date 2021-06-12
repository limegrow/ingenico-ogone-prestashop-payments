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

use Ingenico\Payment\Connector;

class Ingenico_EpaymentsCronModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    /**
     * @var Connector
     */
    public $connector;

    public function display()
    {
        return false;
    }

    public function initContent()
    {
        $this->connector = $this->module->get('ingenico.payment.connector');
        $token = $this->getCronToken();

        if ($token != Tools::getValue('token')) {
            http_response_code(403);
            exit;
        }

        $this->connector->cronHandler();
    }

    /**
     * Get Token for Cron request verification
     * @return bool|string
     */
    private function getCronToken()
    {
        $settings = $this->connector->requestSettings($this->connector->requestSettingsMode());
        $signature = $settings['connection_' . $this->connector->mode . '_signature'];
        return Tools::substr($signature, -5);
    }
}

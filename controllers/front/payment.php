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

use Ingenico\Utils;

class Ingenico_EpaymentsPaymentModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    public function initContent()
    {
        parent::initContent();

        // Set up Controller for Connector
        $this->module->controller = $this;

        $aliasId = Tools::getValue('alias', null);
        $paymentId = Tools::getValue('payment_id');
        $pm = Tools::getValue('pm');
        $brand = Tools::getValue('brand');

        // Get Order
        $orderId = (int) Utils::getSessionValue('ingenico_order');
        if (!$orderId) {
            // Place Order
            $order = $this->module->confirmOrder();
            $orderId = $order->id;
            Utils::setSessionValue('ingenico_order', $orderId);
        }

        if ($aliasId && $aliasId !== \IngenicoClient\IngenicoCoreLibrary::ALIAS_CREATE_NEW) {
            $alias = $this->module->getAlias($aliasId);
            $pm = $alias['PM'];
            $brand = $alias['BRAND'];
        }

        if (($pm && $brand) || $paymentId) {
            // @todo Get PM and Brand by PaymentMethod Id
            $data = $this->module->coreLibrary->getSpecifiedRedirectPaymentRequest(
                $orderId,
                $aliasId,
                $pm,
                $brand,
                $paymentId
            );

            $this->module->showPaymentListRedirectTemplate([
                'order_id' => $orderId,
                'url' => $data->getUrl(),
                'fields' => $data->getFields()
            ]);
        } else {
            $this->module->coreLibrary->processPaymentRedirect($orderId, $aliasId);
            // @see self::showPaymentListRedirectTemplate()
        }
    }
}

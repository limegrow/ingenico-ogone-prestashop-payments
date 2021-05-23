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

use Ingenico\Utils;

class Ingenico_EpaymentsSuccessModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    public function initContent()
    {
        parent::initContent();

        // Set up Controller for Connector
        $this->module->controller = $this;

        $orderId = Tools::getValue('order_id');
        if ($orderId && strpos($orderId, 'cartId') !== false) {
            $cartId = str_replace('cartId', '', $orderId);
            $cart = new \Cart((int) $cartId);
            if (Tools::getValue('return_state') != $this->module::RETURN_STATE_EXCEPTION && !$cart->orderExists()) {
                // Place order with Pending state
                $this->module->validateOrder(
                    $cart->id,
                    Configuration::get($this->module::PS_OS_PENDING),
                    $cart->getOrderTotal(),
                    $this->module->displayName,
                    null,
                    null,
                    $cart->id_currency,
                    false,
                    false
                );

                // Store OrderId value in session
                Utils::setSessionValue('ingenico_order', $this->module->currentOrder);

                // Override
                $_GET['order_id'] = $this->module->currentOrder;
                $_REQUEST['order_id'] = $this->module->currentOrder;
            }
        }

        $this->module->processSuccessUrls();
    }
}

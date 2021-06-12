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

use Ingenico\Payment\Utils;
use Ingenico\Payment\Connector;

class Ingenico_EpaymentsPayment_listModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    /**
     * @var Connector
     */
    public $connector;

    public function initContent()
    {
        parent::initContent();

        $this->connector = $this->module->get('ingenico.payment.connector');

        // Set up Controller for Connector
        $this->connector->controller = $this;

        $orderId = Utils::getSessionValue('ingenico_order');
        if (!$orderId) {
            // Initialize Order
            if ($this->context->cart->id) {
                $orderId = Order::getIdByCartId($this->context->cart->id);
                if ($orderId) {
                    Utils::setSessionValue('ingenico_order', $orderId);
                } else {
                    // Place Order
                    $order = $this->module->confirmOrder();
                    $orderId = $order->id;
                    Utils::setSessionValue('ingenico_order', $orderId);

                    // Prestashop empties the cart when order is being placed.
                    // But we need to save the cart if Inline method is active.
                    // So we are going to restore the cart.
                    $this->connector->restoreCart($orderId);
                }
            } else {
                throw new PrestaShopException('Your shopping cart is empty.');
            }
        } elseif ($this->context->cart->id) {
            // Verify cart amount because order amount can be changed
            $order1 = new Order(Order::getIdByCartId($this->context->cart->id));
            $order2 = new Order($orderId);

            // Compare order amounts
            if ($order1->total_paid_tax_incl !== $order2->total_paid_tax_incl) {
                // Place Order again
                $order = $this->module->confirmOrder();
                $orderId = $order->id;
                Utils::setSessionValue('ingenico_order', $orderId);

                // Prestashop empties the cart when order is being placed.
                // But we need to save the cart if Inline method is active.
                // So we are going to restore the cart.
                $this->connector->restoreCart($orderId);
            }
        }

        $this->connector->processPayment();
    }
}

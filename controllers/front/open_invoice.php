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

class Ingenico_EpaymentsOpen_invoiceModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    public function initContent()
    {
        parent::initContent();

        // Set up Controller for Connector
        $this->module->controller = $this;

        // Get Order
        $orderId = Utils::getSessionValue('ingenico_order');
        if (!$orderId) {
            // Place Order
            $order = $this->module->confirmOrder();
            $orderId = $order->id;
            Utils::setSessionValue('ingenico_order', $orderId);
        } else {
            // Get Order
            $order = new \Order($orderId);
            if (!$order->reference) {
                // @todo
                throw new \Exception('No Order');
            }
        }

        $payment_id = Tools::getValue('payment_id');
        $pm = Tools::getValue('pm');
        $brand = Tools::getValue('brand');

        // Build Alias with PaymentMethod and Brand
        /** @var \IngenicoClient\Alias $alias */
        $alias = (new \IngenicoClient\Alias())
            ->setIsPreventStoring(true)
            ->setPm($pm)
            ->setBrand($brand);

        // Add Payment Method ID for Alias. It allows return correct PM instance
        if ($payment_id) {
            $alias->setPaymentId($payment_id);
        }

        // Initiate Open Invoice Payment
        $fields = Tools::getAllValues();

        // @see Connector::showPaymentListRedirectTemplate()
        // @see Connector::clarifyOpenInvoiceAdditionalFields()
        // Expect an argument like ['payment_id' => '', 'brand' => '', 'pm' => '', 'customer_dob' => ''...]
        $this->module->processOpenInvoicePayment($orderId, $alias, $fields);
    }
}
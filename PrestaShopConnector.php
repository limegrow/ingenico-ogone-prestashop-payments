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

namespace Ingenico;

require dirname(__FILE__) . '/setup/Migration.php';
require dirname(__FILE__) . '/model/Reminder.php';
require dirname(__FILE__) . '/model/Total.php';
require dirname(__FILE__) . '/model/Payment.php';
require dirname(__FILE__) . '/model/Alias.php';

use Gelf\Publisher;
use Gelf\Transport\TcpTransport;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;
use Ingenico\Setup\Migration;
use Ingenico\Utils;
use Ingenico\Model\Reminder;
use Ingenico\Model\Total;
use Ingenico\Model\Payment;
use Ingenico\Model\Alias;
use IngenicoClient\Connector;
use IngenicoClient\IngenicoCoreLibrary;
use IngenicoClient\ConnectorInterface;
use IngenicoClient\LoggerBuilder;
use IngenicoClient\PaymentMethod\PaymentMethod;
use IngenicoClient\OrderItem;
use IngenicoClient\OrderField;

class PrestaShopConnector extends \PaymentModule implements ConnectorInterface
{
    /**
     * Order Statuses
     */
    const PS_OS_PENDING = 'OS_INGENICO_PENDING';
    const PS_OS_AUTHORIZED = 'OS_INGENICO_AUTHORIZED';
    const PS_OS_CAPTURE_PROCESSING = 'OS_INGENICO_CAPTURE_PROCESSING';
    const PS_OS_CAPTURED = 'PS_OS_PAYMENT';
    const PS_OS_CANCELLED = 'PS_OS_CANCELED';
    const PS_OS_REFUNDED = 'PS_OS_REFUND';
    const PS_OS_REFUND_PARTIAL = 'OS_INGENICO_REFUND_PARTIAL';
    const PS_OS_REFUND_PROCESSING = 'OS_INGENICO_REFUND_PROCESSING';
    const PS_OS_REFUND_REFUSED = 'OS_INGENICO_REFUND_REFUSED';
    const PS_OS_ERROR = 'PS_OS_ERROR';

    /**
     * Payment Modes
     */
    const PAYMENT_MODE_REDIRECT = 'REDIRECT';
    const PAYMENT_MODE_INLINE = 'INLINE';
    const PAYMENT_MODE_ALIAS = 'ALIAS';

    /**
     * Return States
     */
    const RETURN_STATE_ACCEPT = 'ACCEPT';
    const RETURN_STATE_DECLINE = 'DECLINE';
    const RETURN_STATE_CANCEL = 'CANCEL';
    const RETURN_STATE_EXCEPTION = 'EXCEPTION';
    const RETURN_STATE_BACK = 'BACK';

    /**
     * @var IngenicoCoreLibrary
     */
    public $coreLibrary;

    /** @var Reminder */
    public $reminder;

    /** @var Total */
    public $total;

    /** @var Payment */
    public $payment;

    /** @var Alias */
    public $alias;

    /** @var Psr\Log\LoggerInterface */
    protected $logger;

    /** @var string live|test */
    public $mode;

    /**
     * Controller
     * @var ModuleFrontController
     */
    public $controller;

    /**
     * Configuration HTML
     * @var string
     */
    protected $form_html = '';

    /**
     * PrestaShopConnector constructor.
     * @param null $name
     * @param \Context|null $context
     */
    public function __construct($name = null, \Context $context = null)
    {
        parent::__construct($name, $context);

        // Initialize Logger
        $this->logger = new Logger('ingenico');

        if (is_writable(_PS_ROOT_DIR_ . '/var/logs')) {
            $this->logger->pushHandler(new StreamHandler(_PS_ROOT_DIR_ . '/var/logs/ingenico_epayments.log'), Logger::DEBUG);
        }

        if (_PS_MODE_DEV_ && file_exists(_PS_ROOT_DIR_ . '/config/logger.ini')) {
            // Initialize Logger for Development mode
            if ($data = parse_ini_file(_PS_ROOT_DIR_ . '/config/logger.ini', true)) {
                $transport = new TcpTransport($data['gelf']['host'], $data['gelf']['port']);
                $publisher = new Publisher($transport);
                $handler = new GelfHandler($publisher, Logger::DEBUG);
                $handler->setFormatter(new GelfMessageFormatter());

                $this->logger->pushHandler($handler, Logger::DEBUG);
            }
        }

        $this->logger->pushProcessor(new WebProcessor());

        if ($this->active == 1) {
            try {
                $this->mode = $this->requestSettingsMode() ? 'live' : 'test';
                $this->coreLibrary = (new IngenicoCoreLibrary($this))
                    ->setLogger($this->logger)
                    ->setMailTemplatesDirectory(_THEME_DIR_ . '/ingenico_epayments/templates');
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }

        // Models
        $this->reminder = new Reminder($this);
        $this->total = new Total($this);
        $this->payment = new Payment($this);
        $this->alias = new Alias($this);

        //Redirect admin to order detail.
        $this->redirectAdminOrderDetails();

        // Initialise sessions
        //if (session_status() == PHP_SESSION_NONE) {
            //session_start();
        //}
    }

    /**
     * Returns Shopping Cart Extension Id.
     *
     * @return string
     */
    public function requestShoppingCartExtensionId()
    {
        return sprintf(
            'PS%sV%s',
            str_replace('.', '', _PS_VERSION_),
            str_replace('.', '', $this->version)
        );
    }

    /**
     * Returns activated Ingenico environment mode.
     * False for Test (transactions will go through the Ingenico sandbox).
     * True for Live (transactions will be real).
     *
     * @return bool
     */
    public function requestSettingsMode()
    {
        $fieldValue = Utils::getConfig('connection_mode');
        if ($fieldValue === 'off') {
            $fieldValue = false;
        }

        if ($fieldValue === 'on') {
            $fieldValue = true;
        }

        return $fieldValue;
    }

    /**
     * Returns the complete list of all settings as an array.
     *
     * @param bool $mode False for Test. True for Live.
     *
     * @return array
     */
    public function requestSettings($mode)
    {
        $suffix = $mode ? 'live' : 'test';

        $settings = [];
        $default = \IngenicoClient\Configuration::getDefault();
        foreach ($default as $fieldKey => $value) {
            $fieldValue = Utils::getConfig($fieldKey . '_' . $suffix);
            if ($fieldKey === 'connection_mode') {
                // Connection mode value doesn't have mode suffix
                $fieldValue = Utils::getConfig($fieldKey);
            }

            if ($fieldValue) {
                if ($fieldValue === 'off') {
                    $fieldValue = false;
                }

                if ($fieldValue === 'on') {
                    $fieldValue = true;
                }

                // Decode JSON
                if ($fieldKey === 'selected_payment_methods') {
                    $fieldValue = array_unique((array) @json_decode($fieldValue, true));
                }

                $settings[$fieldKey] = $fieldValue;
            } else {
                // Default value
                $settings[$fieldKey] = $value;
            }
        }

        // Additional settings
        $settings['notification_order_paid'] = Utils::getConfig('notification_order_paid_' . $suffix) === 'on';
        $settings['notification_order_paid_email'] = Utils::getConfig('notification_order_paid_email_' . $suffix);
        $settings['notification_refund_failed'] = Utils::getConfig('notification_refund_failed_' . $suffix) === 'on';
        $settings['notification_refund_failed_email'] = Utils::getConfig('notification_refund_failed_email_' . $suffix);

        return $settings;
    }

    /**
     * Check whether an order with given ID is created in Magento
     *
     * @param $orderId
     * @return bool
     */
    public function isOrderCreated($orderId) {
        if (strpos($orderId, 'cartId') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array with the order details in a standardised way for all connectors.
     * Matches platform specific fields to the fields that are understood by the CL.
     *
     * @param mixed $orderId
     * @return array
     */
    public function requestOrderInfo($orderId = null)
    {
        if (!$orderId) {
            $orderId = (int) Utils::getSessionValue('ingenico_order');
        }

        $order = new \Order($orderId);
        if (!$order->reference) {
            return false;
        }

        $currency = new \Currency($order->id_currency);
        $currentState = $order->getCurrentState();

        // Mapping between PrestaShop Order status and Ingenico Payment status
        switch ($currentState) {
            case \Configuration::get(self::PS_OS_PENDING):
                $status = IngenicoCoreLibrary::STATUS_PENDING;
                break;
            case \Configuration::get(self::PS_OS_AUTHORIZED):
                $status = IngenicoCoreLibrary::STATUS_AUTHORIZED;
                break;
            case \Configuration::get(self::PS_OS_CAPTURE_PROCESSING):
                $status = IngenicoCoreLibrary::STATUS_CAPTURE_PROCESSING;
                break;
            case \Configuration::get(self::PS_OS_CAPTURED):
                $status = IngenicoCoreLibrary::STATUS_CAPTURED;
                break;
            case \Configuration::get(self::PS_OS_CANCELLED):
                $status = IngenicoCoreLibrary::STATUS_CANCELLED;
                break;
            case \Configuration::get(self::PS_OS_REFUND_PROCESSING):
                $status = IngenicoCoreLibrary::STATUS_REFUND_PROCESSING;
                break;
            case \Configuration::get(self::PS_OS_REFUND_REFUSED):
                $status = IngenicoCoreLibrary::STATUS_REFUND_REFUSED;
                break;
            case \Configuration::get(self::PS_OS_REFUNDED):
                $status = IngenicoCoreLibrary::STATUS_REFUNDED;
                break;
            case \Configuration::get(self::PS_OS_ERROR):
                $status = IngenicoCoreLibrary::STATUS_ERROR;
                break;
            default:
                $status = IngenicoCoreLibrary::STATUS_UNKNOWN;
                break;
        }

        $billingAddress = new \Address((int) $order->id_address_invoice);
        $shippingAddress = new \Address((int) $order->id_address_delivery);
        $customer = new \Customer((int) $billingAddress->id_customer);

        // Calculate refunded, cancelled, and captured totals
        $totalAmount = (float) \Tools::ps_round($order->total_paid_tax_incl, 2);
        $refundedAmount = $this->total->getRefundedAmount($orderId);
        $cancelledAmount = $this->total->getCancelledAmount($orderId);
        $capturedAmount = $this->total->getCapturedAmount($orderId);

        // Get Shipping Details
        $shippingTitle = null;
        $shippingCost = $order->total_shipping_tax_incl;
        $shippingTaxRate = 0;
        $shippingTaxAmount = $shippingCost - (float) $order->total_shipping_tax_excl;
        if ((int) $order->id_carrier > 0) {
            $carrier = new \Carrier((int) $order->id_carrier);
            $shippingTitle = $carrier->name;
            $shippingTaxRate = \Tax::getCarrierTaxRate((int) $carrier->id, $order->id_address_invoice);
        }

        // Get order items
        $items = [];
        $order_details = $order->getOrderDetailList();
        foreach ($order_details as $order_detail) {
            $taxAmount = $order_detail['total_price_tax_incl'] - $order_detail['total_price_tax_excl'];
            $taxPercent = ($taxAmount > 0) ? round(100 / ($order_detail['total_price_tax_excl'] / $taxAmount)) : 0;
            $unitPrice = $order_detail['total_price_tax_incl'] / $order_detail['product_quantity'];

            $items[] = [
                OrderItem::ITEM_TYPE => OrderItem::TYPE_PRODUCT,
                OrderItem::ITEM_ID => $order_detail['product_reference'],
                OrderItem::ITEM_NAME => $order_detail['product_name'],
                OrderItem::ITEM_DESCRIPTION => $order_detail['product_name'],
                OrderItem::ITEM_UNIT_PRICE => (float) \Tools::ps_round($unitPrice, 2),
                OrderItem::ITEM_QTY => $order_detail['product_quantity'],
                OrderItem::ITEM_UNIT_VAT => (float) \Tools::ps_round($taxAmount / $order_detail['product_quantity'], 2),
                OrderItem::ITEM_VATCODE => (float) \Tools::ps_round($taxPercent),
                OrderItem::ITEM_VAT_INCLUDED => 1,
            ];
        }

        // Add Discount Order line
        if ((float) $order->total_discounts_tax_incl > 0) {
            $taxAmount = $order->total_discounts_tax_incl - $order->total_discounts_tax_excl;
            $taxPercent = ($taxAmount > 0) ? round(100 / ($order->total_discounts_tax_excl / $taxAmount)) : 0;

            $items[] = [
                OrderItem::ITEM_TYPE => OrderItem::TYPE_DISCOUNT,
                OrderItem::ITEM_ID => 'discount',
                OrderItem::ITEM_NAME => 'Discount',
                OrderItem::ITEM_DESCRIPTION => 'Discount',
                OrderItem::ITEM_UNIT_PRICE => -1 * $order->total_discounts_tax_incl,
                OrderItem::ITEM_QTY => 1,
                OrderItem::ITEM_UNIT_VAT => (float) \Tools::ps_round($taxAmount, 2),
                OrderItem::ITEM_VATCODE => (float) \Tools::ps_round($taxPercent),
                OrderItem::ITEM_VAT_INCLUDED => 1
            ];
        }

        // Add Shipping Order Line
        if ((float) $order->total_shipping_tax_incl > 0) {
            $carrier = new \Carrier((int) $order->id_carrier);

            $items[] = [
                OrderItem::ITEM_TYPE => OrderItem::TYPE_SHIPPING,
                OrderItem::ITEM_ID => 'shipping',
                OrderItem::ITEM_NAME => $carrier->name,
                OrderItem::ITEM_DESCRIPTION => $carrier->name,
                OrderItem::ITEM_UNIT_PRICE => $order->total_shipping_tax_incl,
                OrderItem::ITEM_QTY => 1,
                OrderItem::ITEM_UNIT_VAT => 0,
                OrderItem::ITEM_VATCODE => 0,
                OrderItem::ITEM_VAT_INCLUDED => 1
            ];
        }

        // @see More order fields in OrderField
        // @see More order items fields in OrderItems

        return [
            OrderField::ORDER_ID => $orderId,
            OrderField::PAY_ID => $this->getIngenicoPayIdByOrderId($orderId),
            OrderField::AMOUNT => $totalAmount,
            OrderField::TOTAL_CAPTURED => $capturedAmount,
            OrderField::TOTAL_REFUNDED => $refundedAmount,
            OrderField::TOTAL_CANCELLED => $cancelledAmount,
            OrderField::CURRENCY => $currency->iso_code,
            OrderField::STATUS => $status,
            OrderField::CREATED_AT => $order->date_add, // Y-m-d H:i:s
            OrderField::HTTP_ACCEPT => isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null,
            OrderField::HTTP_USER_AGENT => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            OrderField::BILLING_COUNTRY => \Country::getNameById($order->id_lang, $billingAddress->id_country),
            OrderField::BILLING_COUNTRY_CODE => \Country::getIsoById($billingAddress->id_country),
            OrderField::BILLING_ADDRESS1 => $billingAddress->address1,
            OrderField::BILLING_ADDRESS2 => $billingAddress->address2,
            OrderField::BILLING_ADDRESS3 => null,
            OrderField::BILLING_CITY => $billingAddress->city,
            OrderField::BILLING_STATE => \State::getNameById($billingAddress->id_state),
            OrderField::BILLING_POSTCODE => $billingAddress->postcode,
            OrderField::BILLING_PHONE => $billingAddress->phone,
            OrderField::BILLING_EMAIL => $customer->email,
            OrderField::BILLING_FIRST_NAME => $billingAddress->firstname,
            OrderField::BILLING_LAST_NAME => $billingAddress->lastname,
            OrderField::IS_SHIPPING_SAME => false,
            OrderField::SHIPPING_COUNTRY => \Country::getNameById($order->id_lang, $shippingAddress->id_country),
            OrderField::SHIPPING_COUNTRY_CODE => \Country::getIsoById($shippingAddress->id_country),
            OrderField::SHIPPING_ADDRESS1 => $shippingAddress->address1,
            OrderField::SHIPPING_ADDRESS2 => $shippingAddress->address2,
            OrderField::SHIPPING_ADDRESS3 => null,
            OrderField::SHIPPING_CITY => $shippingAddress->city,
            OrderField::SHIPPING_STATE => \State::getNameById($shippingAddress->id_state),
            OrderField::SHIPPING_POSTCODE => $shippingAddress->postcode,
            OrderField::SHIPPING_PHONE => $shippingAddress->phone,
            OrderField::SHIPPING_EMAIL => $customer->email,
            OrderField::SHIPPING_FIRST_NAME => $shippingAddress->firstname,
            OrderField::SHIPPING_LAST_NAME => $shippingAddress->lastname,
            OrderField::CUSTOMER_ID => (int) $order->id_customer,
            OrderField::CUSTOMER_IP => \Tools::getRemoteAddr(),
            OrderField::CUSTOMER_DOB => ($customer->birthday === '0000-00-00') ? null : strtotime($customer->birthday),
            OrderField::ITEMS => $items,
            OrderField::LOCALE => $this->getLocale($orderId),
            OrderField::SHIPPING_METHOD => $shippingTitle,
            OrderField::SHIPPING_AMOUNT => $shippingCost,
            OrderField::SHIPPING_TAX_AMOUNT => $shippingTaxAmount,
            OrderField::SHIPPING_TAX_CODE => $shippingTaxRate,
            OrderField::COMPANY_NAME => $billingAddress->company,
            OrderField::COMPANY_VAT => $billingAddress->vat_number,
            OrderField::CHECKOUT_TYPE => \IngenicoClient\Checkout::TYPE_B2C,
        ];
    }

    /**
     * Same As requestOrderInfo()
     * But Order Object Cannot Be Used To Fetch The Required Info
     *
     * @param mixed $reservedOrderId
     * @return array
     */
    public function requestOrderInfoBeforePlaceOrder($reservedOrderId)
    {
        $cartId = str_replace('cartId', '', $reservedOrderId);
        $cart = new \Cart((int) $cartId);

        if ($cart->orderExists()) {
            $order = \Order::getByCartId($cartId);
            return $this->requestOrderInfo($order->id);
        }

        $currency = new \Currency((int) $cart->id_currency);
        $billingAddress = new \Address((int) $cart->id_address_invoice);
        $shippingAddress = new \Address((int) $cart->id_address_delivery);
        $customer = new \Customer((int) $cart->id_customer);
        $locale = str_replace('-', '_', \Language::getLocaleByIso(\Language::getIsoById((int) $cart->id_lang)));

        // Get Shipping Details
        $shippingTitle = null;
        $shippingCost = (float) $cart->getTotalShippingCost();
        $shippingTaxRate = 0;
        $shippingTaxAmount = 0;
        if ((int) $cart->id_carrier > 0) {
            $carrier = new \Carrier((int) $cart->id_carrier);
            $shippingTitle = $carrier->name;
            $shippingTaxRate = \Tax::getCarrierTaxRate((int) $carrier->id, $cart->id_address_invoice);
            $totalShippingTaxExcl = $shippingCost / (($shippingTaxRate / 100) + 1);
            $shippingTaxAmount = $shippingCost - $totalShippingTaxExcl;
        }

        // Get order items
        $items = [];
        $products = $cart->getProducts();
        foreach ($products as $product) {
            $unitPrice = (float) \Tools::ps_round($product['price_wt'], 2);
            $totalWithoutTax = (float) \Tools::ps_round($product['total'], 2);
            $totalWithTax = (float) \Tools::ps_round($product['total_wt'], 2);
            $taxAmount = $totalWithTax - $totalWithoutTax;
            $taxPercent = ($taxAmount > 0) ? round(100 / ($totalWithoutTax / $taxAmount)) : 0;

            $items[] = [
                OrderItem::ITEM_TYPE => OrderItem::TYPE_PRODUCT,
                OrderItem::ITEM_ID => $product['reference'],
                OrderItem::ITEM_NAME => $product['name'],
                OrderItem::ITEM_DESCRIPTION => $product['legend'],
                OrderItem::ITEM_UNIT_PRICE => (float) \Tools::ps_round($unitPrice, 2),
                OrderItem::ITEM_QTY => $product['cart_quantity'],
                OrderItem::ITEM_UNIT_VAT => (float) \Tools::ps_round($taxAmount / $product['cart_quantity'], 2),
                OrderItem::ITEM_VATCODE => (float) \Tools::ps_round($taxPercent),
                OrderItem::ITEM_VAT_INCLUDED => 1,
            ];
        }

        // Add Discount Order line
        $totalDiscountsTaxIncl = (float) abs($cart->getOrderTotal(
            true,
            \Cart::ONLY_DISCOUNTS,
            $cart->getProducts(),
            (int) $cart->id_carrier
        ));

        if ($totalDiscountsTaxIncl > 0) {
            $totalDiscountsTaxExcl = (float) abs($cart->getOrderTotal(
                false,
                \Cart::ONLY_DISCOUNTS,
                $cart->getProducts(),
                (int)$cart->id_carrier
            ));
            $totalDiscountsTaxRate = (($totalDiscountsTaxIncl / $totalDiscountsTaxExcl) - 1) * 100;

            $items[] = [
                OrderItem::ITEM_TYPE => OrderItem::TYPE_DISCOUNT,
                OrderItem::ITEM_ID => 'discount',
                OrderItem::ITEM_NAME => 'Discount',
                OrderItem::ITEM_DESCRIPTION => 'Discount',
                OrderItem::ITEM_UNIT_PRICE => -1 * $totalDiscountsTaxIncl,
                OrderItem::ITEM_QTY => 1,
                OrderItem::ITEM_UNIT_VAT => (float) \Tools::ps_round($totalDiscountsTaxIncl - $totalDiscountsTaxExcl, 2),
                OrderItem::ITEM_VATCODE => (float) \Tools::ps_round($totalDiscountsTaxRate),
                OrderItem::ITEM_VAT_INCLUDED => 1
            ];
        }

        // Add Shipping Order Line
        if ($cart->getTotalShippingCost() > 0) {
            $carrier = new \Carrier((int) $cart->id_carrier);
            $carrierTaxRate = \Tax::getCarrierTaxRate((int) $carrier->id, $cart->id_address_invoice);
            $totalShippingTaxIncl = (float) $cart->getTotalShippingCost();
            $totalShippingTaxExcl = $totalShippingTaxIncl / (($carrierTaxRate / 100) + 1);

            $items[] = [
                OrderItem::ITEM_TYPE => OrderItem::TYPE_SHIPPING,
                OrderItem::ITEM_ID => 'shipping',
                OrderItem::ITEM_NAME => $carrier->name,
                OrderItem::ITEM_DESCRIPTION => $carrier->name,
                OrderItem::ITEM_UNIT_PRICE => $totalShippingTaxIncl,
                OrderItem::ITEM_QTY => 1,
                OrderItem::ITEM_UNIT_VAT => $totalShippingTaxIncl - $totalShippingTaxExcl,
                OrderItem::ITEM_VATCODE => $carrierTaxRate,
                OrderItem::ITEM_VAT_INCLUDED => 1
            ];
        }

        return [
            OrderField::ORDER_ID => $reservedOrderId,
            OrderField::PAY_ID => null,
            OrderField::AMOUNT => $cart->getOrderTotal(true, \Cart::BOTH),
            OrderField::TOTAL_CAPTURED => 0,
            OrderField::TOTAL_REFUNDED => 0,
            OrderField::TOTAL_CANCELLED => 0,
            OrderField::CURRENCY => $currency->iso_code,
            OrderField::STATUS => $this->coreLibrary::STATUS_UNKNOWN,
            OrderField::CREATED_AT => $cart->date_add, // Y-m-d H:i:s
            OrderField::HTTP_ACCEPT => isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null,
            OrderField::HTTP_USER_AGENT => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            OrderField::BILLING_COUNTRY => \Country::getNameById($cart->id_lang, $billingAddress->id_country),
            OrderField::BILLING_COUNTRY_CODE => \Country::getIsoById($billingAddress->id_country),
            OrderField::BILLING_ADDRESS1 => $billingAddress->address1,
            OrderField::BILLING_ADDRESS2 => $billingAddress->address2,
            OrderField::BILLING_ADDRESS3 => null,
            OrderField::BILLING_CITY => $billingAddress->city,
            OrderField::BILLING_STATE => \State::getNameById($billingAddress->id_state),
            OrderField::BILLING_POSTCODE => $billingAddress->postcode,
            OrderField::BILLING_PHONE => $billingAddress->phone,
            OrderField::BILLING_EMAIL => $customer->email,
            OrderField::BILLING_FIRST_NAME => $billingAddress->firstname,
            OrderField::BILLING_LAST_NAME => $billingAddress->lastname,
            OrderField::IS_SHIPPING_SAME => false,
            OrderField::SHIPPING_COUNTRY => \Country::getNameById($cart->id_lang, $shippingAddress->id_country),
            OrderField::SHIPPING_COUNTRY_CODE => \Country::getIsoById($shippingAddress->id_country),
            OrderField::SHIPPING_ADDRESS1 => $shippingAddress->address1,
            OrderField::SHIPPING_ADDRESS2 => $shippingAddress->address2,
            OrderField::SHIPPING_ADDRESS3 => null,
            OrderField::SHIPPING_CITY => $shippingAddress->city,
            OrderField::SHIPPING_STATE => \State::getNameById($shippingAddress->id_state),
            OrderField::SHIPPING_POSTCODE => $shippingAddress->postcode,
            OrderField::SHIPPING_PHONE => $shippingAddress->phone,
            OrderField::SHIPPING_EMAIL => $customer->email,
            OrderField::SHIPPING_FIRST_NAME => $shippingAddress->firstname,
            OrderField::SHIPPING_LAST_NAME => $shippingAddress->lastname,
            OrderField::CUSTOMER_ID => (int) $cart->id_customer,
            OrderField::CUSTOMER_IP => \Tools::getRemoteAddr(),
            OrderField::CUSTOMER_DOB => ($customer->birthday === '0000-00-00') ? null :
                date('dmY', strtotime($customer->birthday)),
            OrderField::ITEMS => $items,
            OrderField::LOCALE => $locale,
            OrderField::SHIPPING_METHOD => $shippingTitle,
            OrderField::SHIPPING_AMOUNT => $shippingCost,
            OrderField::SHIPPING_TAX_AMOUNT => $shippingTaxAmount,
            OrderField::SHIPPING_TAX_CODE => $shippingTaxRate,
            OrderField::COMPANY_NAME => $billingAddress->company,
            OrderField::COMPANY_VAT => $billingAddress->vat_number,
            OrderField::CHECKOUT_TYPE => \IngenicoClient\Checkout::TYPE_B2C,
        ];
    }

    /**
     * Get Field Label
     *
     * @param string $field
     * @return string
     */
    public function getOrderFieldLabel($field)
    {
        // @todo Set labels for fields

        // Typical fields of address
        $map = [
            'billing_address1' => $this->trans('Billing address', [], 'messages') . ' 1',
            'billing_address2' => $this->trans('Billing address', [], 'messages') . ' 2',
            'billing_address3' => $this->trans('Billing address', [], 'messages') . ' 3',
            'shipping_address1' => $this->trans('Shipping address', [], 'messages') . ' 1',
            'shipping_address2' => $this->trans('Shipping address', [], 'messages') . ' 2',
            'shipping_address3' => $this->trans('Shipping address', [], 'messages') . ' 3',
        ];

        if (isset($map[$field])) {
            return $map[$field];
        }

        return $this->trans(ucfirst(str_replace('_', ' ', $field)), [], 'messages');
    }

    /**
     * Save Platform's setting (key-value couple depending on the mode).
     *
     * @param bool $mode
     * @param string $key
     * @param mixed $value
     * @return void
     * @SuppressWarnings("all")
     */
    public function saveSetting($mode, $key, $value)
    {
        $mode = $mode ? 'live' : 'test';

        // Convert on/off to boolean
        if ($value === 'on') {
            $value = true;
        } elseif ($value === 'off') {
            $value = false;
        }

        switch ($key) {
            case 'connection_mode':
                // Save boolean value
                Utils::updateConfig($key, $value ? 'on' : 'off');
                break;
            case 'settings_advanced':
            case 'settings_tokenisation':
            case 'settings_oneclick':
            case 'settings_skip3dscvc':
            case 'settings_skipsecuritycheck':
            case 'secure':
            case 'settings_directsales':
            case 'settings_orderfreeze':
            case 'settings_reminderemail':
            case 'fraud_notifications':
            case 'direct_sale_email_option':
            case 'instalments_enabled':
                // Save boolean value
                Utils::updateConfig($key . '_' . $mode, $value ? 'on' : 'off');
                break;
            case 'paymentpage_template_localfilename':
                // Upload file
                if (isset($_FILES['paymentpage_template_localfilename']['tmp_name'])) {
                    $uploadedFile = $_FILES['paymentpage_template_localfilename']['tmp_name'];
                    if (file_exists($uploadedFile) &&
                        is_uploaded_file($uploadedFile)
                    ) {
                        $targetDir = _PS_ROOT_DIR_ . '/modules/' . $this->name . '/uploads/';
                        $targetFile = $targetDir . basename($_FILES['paymentpage_template_localfilename']['name']);

                        // Security: check mime type
                        if (mime_content_type($uploadedFile) !== 'text/html') {
                            throw new \Exception($this->trans('validator.mime_html_only', [], 'messages'));
                        }

                        // Security: check mime extension
                        $templateFileType = \Tools::strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                        if (!in_array($templateFileType, ['htm', 'html'])) {
                            throw new \Exception($this->trans('validator.extension_html_only', [], 'messages'));
                        }

                        // Upload file
                        if (!move_uploaded_file($uploadedFile, $targetFile)) { //NOSONAR
                            throw new \Exception($this->trans('exceptions.upload_filed', [], 'messages'));
                        }

                        Utils::updateConfig('paymentpage_template_localfilename_' . $mode, $_FILES['paymentpage_template_localfilename']['name']);
                    }
                }

                break;
            case 'selected_payment_methods':
                // Save array value
                Utils::updateConfig($key . '_' . $mode, json_encode($value));
                break;
            default:
                Utils::updateConfig($key . '_' . $mode, trim($value));
        }
    }

    /**
     * Retrieves orderId from checkout session.
     *
     * @return mixed
     */
    public function requestOrderId()
    {
        return (int) Utils::getSessionValue('ingenico_order');
    }

    /**
     * Retrieves Customer (buyer) ID on the platform side.
     * Zero for guests.
     * Needed for retrieving customer aliases (if saved any).
     *
     * @return int
     */
    public function requestCustomerId()
    {
        return (int) \Context::getContext()->customer->id;
    }

    /**
     * Returns callback URLs where Ingenico must call after the payment processing. Depends on the context of the callback.
     * Following cases are required:
     *  CONTROLLER_TYPE_PAYMENT
     *  CONTROLLER_TYPE_SUCCESS
     *  CONTROLLER_TYPE_ORDER_SUCCESS
     *  CONTROLLER_TYPE_ORDER_CANCELLED
     *
     * @param $type
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function buildPlatformUrl($type, array $params = [])
    {
        switch ($type) {
            case IngenicoCoreLibrary::CONTROLLER_TYPE_PAYMENT:
                return $this->getControllerUrl('payment', $params);
            case IngenicoCoreLibrary::CONTROLLER_TYPE_SUCCESS:
                return $this->getControllerUrl('success', $params);
            case IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_SUCCESS:
                return $this->getSuccessPageUrl($params['order_id']);
            case IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_CANCELLED:
                return $this->getControllerUrl('canceled', $params);
            default:
                throw new \Exception('Unknown page type.');
        }
    }

    /**
     * This method is a generic callback gate.
     * Depending on the URI it redirects to the corresponding action which is done already on the CL level.
     * CL takes responsibility for the data processing and initiates rendering of the matching GUI (template, page etc.).
     *
     * @return void
     */
    public function processSuccessUrls()
    {
        try {
            $this->coreLibrary->processReturnUrls();
        } catch (\IngenicoClient\Exception $e) {
            Utils::unsetSessionValue('ingenico_order');

            // Redirect to "Order cancelled" page
            if ($e->getCode() === \IngenicoClient\Exception::ERROR_DECLINED) {
                //Tools::redirect($this->buildPlatformUrl(IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_CANCELLED));
            }

            // Show Error Page
            $this->setOrderErrorPage($e->getMessage());
        }
    }

    /**
     * Executed on the moment when a buyer submits checkout form with an intention to start the payment process.
     * Depending on the payment mode (Inline vs. Redirect) CL will initiate the right processes and render the corresponding GUI.
     *
     * @return void
     */
    public function processPayment()
    {
        $orderId = Utils::getSessionValue('ingenico_order');
        $aliasId = isset($_REQUEST['alias']) ? $_REQUEST['alias'] : null;

        try {
            $this->coreLibrary->processPayment($orderId, $aliasId);
        } catch (\IngenicoClient\Exception $e) {
            Utils::unsetSessionValue('ingenico_order');

            // Show Error Page
            $this->setOrderErrorPage($e->getMessage());
        }
    }

    /**
     * Executed on the moment when customer's alias saved, and we're should charge payment.
     * Used in Inline payment mode.
     *
     * @return array
     */
    public function finishReturnInline()
    {
        // Check form token
        if (\Tools::getValue('token') !== Utils::getSessionValue('ingenico_token')) {
            return [
                'status' => 'error',
                'message' => 'Invalid token'
            ];
        }

        $orderId = $this->getOrderId(\Tools::getValue('order_id'));
        $cardBrand = \Tools::getValue('card_brand');
        $aliasId = \Tools::getValue('alias_id');

        return $this->coreLibrary->finishReturnInline($orderId, $cardBrand, $aliasId);
    }

    /**
     * Matches Ingenico payment statuses to the platform's order statuses.
     *
     * @param mixed $orderId
     * @param \IngenicoClient\Payment|string $paymentResult
     * @param string|null $message
     * @return void
     */
    public function updateOrderStatus($orderId, $paymentResult, $message = null)
    {
        $order = new \Order($orderId);
        $currentState = $order->getCurrentState();
        $paymentStatus = is_string($paymentResult) ? $paymentResult : $paymentResult->getPaymentStatus();

        // Mapping between Ingenico Payment statuses and PrestaShop Order Statuses
        switch ($paymentStatus) {
            case IngenicoCoreLibrary::STATUS_PENDING:
                if ($currentState !== \Configuration::get(self::PS_OS_PENDING)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_PENDING));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_AUTHORIZED:
                if ($currentState !== \Configuration::get(self::PS_OS_AUTHORIZED)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_AUTHORIZED));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_CAPTURE_PROCESSING:
                if ($currentState !== \Configuration::get(self::PS_OS_CAPTURE_PROCESSING)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_CAPTURE_PROCESSING));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_CAPTURED:
                if ($currentState !== \Configuration::get(self::PS_OS_CAPTURED)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_CAPTURED));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_CANCELLED:
                if ($currentState !== \Configuration::get(self::PS_OS_CANCELLED)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_CANCELLED));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_REFUND_PROCESSING:
                if ($currentState !== \Configuration::get(self::PS_OS_REFUND_PROCESSING)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_REFUND_PROCESSING));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_REFUND_REFUSED:
                if ($currentState !== \Configuration::get(self::PS_OS_REFUND_REFUSED)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_REFUND_REFUSED));
                    $order->save();
                }

                break;
            case IngenicoCoreLibrary::STATUS_REFUNDED:
                // Is it the partial refund?
                $totalAmount = (float) \Tools::ps_round($order->total_paid_tax_incl, 2);
                $refundedAmount = $this->total->getRefundedAmount($orderId);

                if (bccomp($refundedAmount , $totalAmount, 2) === -1) {
                    // Partial refund
                    if ($currentState !== \Configuration::get(self::PS_OS_REFUND_PARTIAL)) {
                        $order->setCurrentState(\Configuration::get(self::PS_OS_REFUND_PARTIAL));
                        $order->save();
                    }
                } else {
                    // Full refund
                    if ($currentState !== \Configuration::get(self::PS_OS_REFUNDED)) {
                        $order->setCurrentState(\Configuration::get(self::PS_OS_REFUNDED));
                        $order->save();
                    }
                }

                break;
            case IngenicoCoreLibrary::STATUS_ERROR:
                if ($currentState !== \Configuration::get(self::PS_OS_ERROR)) {
                    $order->setCurrentState(\Configuration::get(self::PS_OS_ERROR));
                    $order->save();
                }

                break;
            default:
                throw new \Exception('Unknown Payment Status');
        }

        // Add order note
        if ($message) {
            $msg = new \Message();
            $message = strip_tags($message, '<br>');
            if (\Validate::isCleanHtml($message)) {
                $msg->message = $message;
                $msg->id_cart = (int) $order->id_cart;
                $msg->id_customer = (int) $order->id_customer;
                $msg->id_order = (int) $order->id;
                $msg->private = 1;
                $msg->add();
            }

            // Add this message in the customer thread
            $customer_thread = new \CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = (int) $order->id_customer;
            $customer_thread->id_shop = (int) $order->id_shop;
            $customer_thread->id_order = (int) $order->id;
            $customer_thread->id_lang = (int) $order->id_lang;

            $customer_thread->email = $order->getCustomer()->email;
            $customer_thread->status = 'open';
            $customer_thread->token = \Tools::passwdGen(12);
            $customer_thread->add();

            $customer_message = new \CustomerMessage();
            $customer_message->id_customer_thread = $customer_thread->id;
            $customer_message->id_employee = 0;
            $customer_message->message = $msg->message;
            $customer_message->private = 1;
            $customer_message->add();
        }
    }

    /**
     * Check if Shopping Cart has orders that were paid (via other payment integrations, i.e. PayPal module)
     * It's to cover the case where payment was initiated through Ingenico but at the end, user went back and paid by other
     * payment provider. In this case we know not to send order reminders etc.
     *
     * @param $orderId
     * @return bool
     */
    public function isCartPaid($orderId)
    {
        $order = new \Order($orderId);
        $collection = new \PrestaShopCollection('order');
        $collection->where('id_cart', '=', $order->id_cart);
        $orders = $collection->getResults();
        foreach ($orders as $orderId) {
            $order = new \Order($orderId);
            if ($order->hasBeenPaid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sends an e-mail using platform's email engine.
     *
     * @param \IngenicoClient\MailTemplate $template
     * @param string $to
     * @param string $toName
     * @param string $from
     * @param string $fromName
     * @param string $subject
     * @param array $attachedFiles Array like [['name' => 'attached.txt', 'mime' => 'plain/text', 'content' => 'Body']]
     * @return bool|int
     * @throws \Exception
     */
    public function sendMail(
        $template,
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $attachedFiles = []
    ) {
        if (!$template instanceof \IngenicoClient\MailTemplate) {
            throw new \Exception('Template variable must be instance of MailTemplate');
        }

        $data = [
            '{html}' => $template->getHtml(),
            '{text}' => $template->getPlainText(),
        ];

        return \Mail::Send(
            null,
            'placeholder',
            $subject,
            $data,
            $to,
            $toName,
            $from,
            $fromName,
            $attachedFiles,
            null,
            dirname(__FILE__) . '/mails/',
            false,
            null
        );
    }

    /**
     * Get the platform's actual locale code.
     * Returns code in a format: en_US.
     *
     * @param int|null $orderId
     * @return string
     */
    public function getLocale($orderId = null)
    {
        // Returns local of admin backend
        if (defined('PS_ADMIN_DIR')) {
            return str_replace('-', '_', \Context::getContext()->language->locale);
        }

        if ($orderId) {
            $order = new \Order($orderId);
            $isoCode = \Language::getIsoById((int) $order->id_lang);
        } elseif ($user_id = (int) \Context::getContext()->customer->id) {
            $customer = new \Customer($user_id);
            $isoCode = \Language::getIsoById((int) $customer->id_lang);
        } else {
            $isoCode = \Language::getIsoById((int) \Configuration::get('PS_LANG_DEFAULT'));
        }

        try {
            $locale = \Language::getLocaleByIso($isoCode);
        } catch (\Exception $e) {
            $locale = 'en_US';
        }

        return str_replace('-', '_', $locale);
    }

    /**
     * Adds cancelled amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $canceledAmount
     * @return void
     */
    public function addCancelledAmount($orderId, $canceledAmount)
    {
        $this->total->addCancelledAmount($orderId, $canceledAmount);
    }

    /**
     * Adds captured amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $capturedAmount
     * @return void
     */
    public function addCapturedAmount($orderId, $capturedAmount)
    {
        $this->total->addCapturedAmount($orderId, $capturedAmount);
    }

    /**
     * Adds refunded amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $refundedAmount
     * @return void
     */
    public function addRefundedAmount($orderId, $refundedAmount)
    {
        $this->total->addRefundedAmount($orderId, $refundedAmount);
    }

    /**
     * Send "Order paid" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendOrderPaidCustomerEmail($orderId)
    {
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);

        // Get Customer's locale
        $locale = $this->getLocale($orderId);

        return $this->coreLibrary->sendMailNotificationPaidOrder(
            pSQL($customer->email),
            null,
            null,
            null,
            $this->coreLibrary->__('order_paid.subject', [], 'email', $locale),
            [
                Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                Connector::PARAM_NAME_ORDER_REFERENCE => $order->getUniqReference(),
                Connector::PARAM_NAME_ORDER_URL => \Context::getContext()->link->getPageLink('order-detail', true, null, 'id_order='.$order->id)
            ],
            $locale
        );
    }

    /**
     * Send "Order paid" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendOrderPaidAdminEmail($orderId)
    {
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);

        $settings = $this->requestSettings($this->requestSettingsMode());
        if ($settings['notification_order_paid'] &&
            filter_var($settings['notification_order_paid_email'], FILTER_VALIDATE_EMAIL)
        ) {
            try {
                $this->coreLibrary->sendMailNotificationAdminPaidOrder(
                    $settings['notification_order_paid_email'],
                    null,
                    null,
                    null,
                    $this->coreLibrary->__('admin_order_paid.subject', [], 'email', $this->getLocale()),
                    [
                        Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                        Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                        Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                        Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                        Connector::PARAM_NAME_ORDER_REFERENCE => $order->getUniqReference(),
                        'path_uri' => $this->getPathUri(),
                        Connector::PARAM_NAME_INGENICO_LOGO => $this->getPath(true) . 'views/imgs/logo.png',
                        Connector::PARAM_NAME_ORDER_VIEW_URL => $this->getOrderViewUrl($orderId)
                    ],
                    $this->getLocale()
                );
            } catch (\Exception $e) {
                $this->logger->debug('Mail sending failed: ' . $e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * Send "Payment Authorized" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendNotificationAuthorization($orderId)
    {
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);

        // Get Customer's locale
        $locale = $this->getLocale($orderId);

        return $this->coreLibrary->sendMailNotificationAuthorization(
            pSQL($customer->email),
            null,
            null,
            null,
            $this->coreLibrary->__('authorization.subject', [], 'email', $locale),
            [
                Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                Connector::PARAM_NAME_ORDER_REFERENCE => $order->getUniqReference(),
                Connector::PARAM_NAME_ORDER_URL => \Context::getContext()->link->getPageLink('order-detail', true, null, 'id_order=' . $order->id)
            ],
            $locale
        );
    }

    /**
     * Send "Payment Authorized" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendNotificationAdminAuthorization($orderId)
    {
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);

        // Get admin's locale
        if (defined('PS_ADMIN_DIR')) {
            $locale = str_replace('-', '_', \Context::getContext()->language->locale);
        } else {
            $isoCode = \Language::getIsoById((int) \Configuration::get('PS_LANG_DEFAULT'));
            $locale = str_replace('-', '_', \Language::getLocaleByIso($isoCode));
        }

        // Get email from Settings
        $email = Utils::getConfig('direct_sale_email_' . $this->mode);
        if (empty($email)) {
            $email = \Configuration::get('PS_SHOP_EMAIL');
        }

        // Get employee's locale by email if exists
        $employeeObj = new \Employee();
        if ($employee = $employeeObj->getByEmail($email, null, false)) {
            $isoCode = \Language::getIsoById((int) $employee->id_lang);
            $locale = str_replace('-', '_', \Language::getLocaleByIso($isoCode));
        }

        return $this->coreLibrary->sendMailNotificationAdminAuthorization(
            pSQL($email),
            null,
            null,
            null,
            $this->coreLibrary->__('admin_authorization.subject', [], 'email', $locale),
            [
                Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                Connector::PARAM_NAME_ORDER_REFERENCE => $order->getUniqReference(),
                Connector::PARAM_NAME_ORDER_VIEW_URL => $this->getOrderViewUrl($orderId),
                'path_uri' => $this->getPath(true),
                Connector::PARAM_NAME_INGENICO_LOGO => $this->getPath(true) . 'views/imgs/logo.png'
            ],
            $locale
        );
    }

    /**
     * Sends payment reminder email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendReminderNotificationEmail($orderId)
    {
        $order = new \Order($orderId);
        $currency = new \Currency($order->id_currency);
        $customer = new \Customer($order->id_customer);

        // Get products
        $products = [];
        $order_details = $order->getOrderDetailList();
        foreach ($order_details as $order_detail) {
            $image = \Product::getCover($order_detail['product_id']);
            $image = new \Image($image['id_image']);
            $product_image = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . '-' . \ImageType::getFormattedName('small') . '.jpg';
            $products[] = [
                'image' => $product_image,
                'name' => $order_detail['product_name'],
                'price' => \Tools::displayPrice($order_detail['total_price_tax_incl'], $currency)
            ];
        }

        // Get Customer's locale
        $locale = $this->getLocale($orderId);

        return $this->coreLibrary->sendMailNotificationReminder(
            pSQL($customer->email),
            null,
            null,
            null,
            $this->coreLibrary->__('reminder.subject', [], 'email', $locale),
            [
                Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                Connector::PARAM_NAME_PRODUCTS => $products,
                Connector::PARAM_NAME_ORDER_TOTAL => \Tools::displayPrice($order->total_products_wt, $currency),
                Connector::PARAM_NAME_PAYMENT_LINK => $this->reminder->getCompletePaymentLink($orderId)
            ],
            $locale
        );
    }

    /**
     * Send "Refund failed" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendRefundFailedCustomerEmail($orderId)
    {
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);

        // Get Customer's locale
        $locale = $this->getLocale($orderId);

        return $this->coreLibrary->sendMailNotificationRefundFailed(
            pSQL($customer->email),
            null,
            null,
            null,
            $this->coreLibrary->__('refund_failed.subject', [], 'email', $locale),
            [
                Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                Connector::PARAM_NAME_ORDER_REFERENCE => $order->getUniqReference(),
                Connector::PARAM_NAME_ORDER_URL => \Context::getContext()->link->getPageLink('order-detail', true, null, 'id_order='.$order->id)
            ],
            $locale
        );
    }

    /**
     * Send "Refund failed" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendRefundFailedAdminEmail($orderId)
    {
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);

        $settings = $this->requestSettings($this->requestSettingsMode());
        if ($settings['notification_refund_failed'] &&
            filter_var($settings['notification_refund_failed_email'], FILTER_VALIDATE_EMAIL)
        ) {
            try {
                $this->coreLibrary->sendMailNotificationAdminRefundFailed(
                    $settings['notification_refund_failed_email'],
                    null,
                    null,
                    null,
                    $this->coreLibrary->__('admin_refund_failed.subject', [], 'email', $this->getLocale()),
                    [
                        Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                        Connector::PARAM_NAME_SHOP_LOGO => _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
                        Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                        Connector::PARAM_NAME_CUSTOMER_NAME => sprintf('%s %s', $customer->firstname, $customer->lastname),
                        Connector::PARAM_NAME_ORDER_REFERENCE => $order->getUniqReference(),
                        'path_uri' => $this->getPathUri(),
                        Connector::PARAM_NAME_INGENICO_LOGO => $this->getPath(true) . 'views/imgs/logo.png'
                    ],
                    $this->getLocale()
                );
            } catch (\Exception $e) {
                $this->logger->debug('Mail sending failed: ' . $e->getMessage());

                return false;
            }
        }

        return true;
    }

    /**
     * Send "Request Support" email to Ingenico Support
     * @param $email
     * @param $subject
     * @param array $fields
     * @param null $file
     * @return bool
     * @throws \Exception
     */
    public function sendSupportEmail(
        $email,
        $subject,
        array $fields = [],
        $file = null
    ) {
        // Attached files
        $attachedFiles = [];
        if ($file && file_exists($file)) {
            $attachedFiles = [
                ['name' => basename($file), 'mime' => 'plain/text', 'content' => \Tools::file_get_contents($file)]
            ];
        }

        // Default Mail template fields
        $fields = array_merge(
            [
                Connector::PARAM_NAME_PLATFORM => $this->requestShoppingCartExtensionId(),
                Connector::PARAM_NAME_SHOP_URL => \Context::getContext()->link->getPageLink('index', true),
                Connector::PARAM_NAME_SHOP_NAME => \Configuration::get('PS_SHOP_NAME'),
                Connector::PARAM_NAME_TICKET => '',
                Connector::PARAM_NAME_DESCRIPTION => ''
            ],
            $fields
        );

        // Send E-mail
        return $this->coreLibrary->sendMailSupport(
            $this->coreLibrary->getWhiteLabelsData()->getSupportEmail(),
            $this->coreLibrary->getWhiteLabelsData()->getSupportName(),
            $email,
            \Configuration::get('PS_SHOP_NAME'),
            $subject,
            $fields,
            $this->getLocale(),
            $attachedFiles
        );
    }

    /**
     * Returns categories of the payment methods.
     *
     * @return array
     */
    public function getPaymentCategories()
    {
        return $this->coreLibrary->getPaymentCategories();
    }

    /**
     * Returns all payment methods with the indicated category
     *
     * @param $category
     * @return array
     */
    public function getPaymentMethodsByCategory($category)
    {
        return IngenicoCoreLibrary::getPaymentMethodsByCategory($category);
    }

    /**
     * Returns all supported countries with their popular payment methods mapped
     * Returns array like ['DE' => 'Germany']
     *
     * @return array
     */
    public function getAllCountries()
    {
        return $this->coreLibrary->getAllCountries();
    }

    /**
     * Get Country by Code.
     *
     * @param $code
     * @return string|false
     */
    public function getCountryByCode($code)
    {
        $countries = $this->getAllCountries();

        return isset($countries[$code]) ? $countries[$code] : false;
    }

    /**
     * Returns all payment methods as PaymentMethod objects.
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        if (!$this->coreLibrary) {
            return [];
        }

        return IngenicoCoreLibrary::getPaymentMethods();
    }

    /**
     * Get Unused Payment Methods (not selected ones).
     * Returns an array with PaymentMethod objects.
     * Used in the modal window in the plugin Settings in order to list Payment methods that are not yet added.
     *
     * @return array
     */
    public function getUnusedPaymentMethods()
    {
        return $this->coreLibrary->getUnusedPaymentMethods();
    }

    /**
     * Settings page content.
     * PrestaShop use this method to render module configuration.
     *
     * @return string HTML
     */
    public function getContent()
    {
        if (\Tools::isSubmit('submit' . $this->name)) {
            $this->saveSettings();
        }

        // Submit Support Request
        if (\Tools::isSubmit('submitSupportRequest')) {
            $ticket = \Tools::getValue('support_ticket');
            $email = \Tools::getValue('support_email');
            $description = \Tools::getValue('support_description');

            $hasErrors = false;
            if (!empty($ticket) && !preg_match(\Tools::cleanNonUnicodeSupport('/^[^<>]*$/u'), $ticket)) {
                $this->form_html .= $this->displayError($this->trans('form.support.validation.ticket_failed', [], 'messages'));
                $hasErrors = true;
            }

            if (empty($email)) {
                $this->form_html .= $this->displayError($this->trans('form.support.validation.email_required', [], 'messages'));
                $hasErrors = true;
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->form_html .= $this->displayError($this->trans('form.support.validation.email_invalid', [], 'messages'));
                $hasErrors = true;
            }

            if (!$hasErrors) {
                // Export settings to temporary file
                $filename = sprintf('settings_%s_%s.json', \Tools::getShopDomain(), date('dmY_H_i_s'));
                if (!empty($ticket)) {
                    $filename = sprintf('settings_%s_%s_%s.json', \Tools::getShopDomain(), $ticket, date('dmY_H_i_s'));
                }

                $data = $this->coreLibrary->getConfiguration()->export();
                $contents = json_encode($data, JSON_PRETTY_PRINT);
                file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename, $contents);

                // Get Platform
                $platform = $this->requestShoppingCartExtensionId();

                // Prepare subject
                if (!empty($ticket)) {
                    $subject = sprintf('Exported settings related to the ticket nr [%s]', $ticket);
                } else {
                    $subject = sprintf('%s: Issues configuring the site %s', $platform, \Tools::getShopDomain());
                }

                // Send E-mail
                $result = $this->sendSupportEmail(
                    $email,
                    $subject,
                    [
                        Connector::PARAM_NAME_PLATFORM => $platform,
                        Connector::PARAM_NAME_TICKET => $ticket,
                        Connector::PARAM_NAME_DESCRIPTION => $description
                    ],
                    sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename
                );

                // Remove temporary file
                @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename);

                if ($result) {
                    $this->form_html .= $this->displayConfirmation($this->trans('form.support.validation.mail_sent', [], 'messages'));
                } else {
                    $this->form_html .= $this->displayError($this->trans('form.support.validation.mail_failed', [], 'messages'));
                }
            }
        }

        // Import Settings
        if (\Tools::isSubmit('submitImportSettings')) {
            // Upload file
            if (empty($_FILES['support-import']['tmp_name'])) {
                $this->form_html .= $this->displayError($this->trans('form.support.validation.file_required', [], 'messages'));
            } elseif (file_exists($_FILES['support-import']['tmp_name']) &&
                is_uploaded_file($_FILES['support-import']['tmp_name'])
            ) {
                try {
                    // Security: check mime type
                    $mime = mime_content_type($_FILES['support-import']['tmp_name']);
                    if ($mime !== 'text/plain') {
                        throw new \Exception($this->trans('validator.mime_text_only', [], 'messages'));
                    }

                    $contents = \Tools::file_get_contents($_FILES['support-import']['tmp_name']);
                    $data = @json_decode($contents, true);

                    // Validate data
                    if (!is_array($data) || !isset($data['test']) || !isset($data['production'])) {
                        $this->form_html .= $this->displayError($this->trans('form.support.invalid_json_data', [], 'messages'));
                    } else {
                        $this->coreLibrary->getConfiguration()->import($data);
                        $this->form_html .= $this->displayConfirmation($this->trans('form.support.import_success', [], 'messages'));
                    }
                } catch (\Exception $e) {
                    $this->form_html .= $this->displayError($e->getMessage());
                }
            }
        }

        // Export Settings
        if (\Tools::isSubmit('submitExportSettings')) {
            $filename = sprintf('settings_%s_%s.json', \Tools::getShopDomain(), date('dmY_H_i_s'));
            $data = $this->coreLibrary->getConfiguration()->export();
            $contents = json_encode($data, JSON_PRETTY_PRINT);

            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '";');
            echo $contents;
            exit();
        }

        // Account creation language
        $lang = 1; // Default, english
        if (isset(IngenicoCoreLibrary::$accountCreationLangCodes[$this->context->language->iso_code])) {
            $lang = IngenicoCoreLibrary::$accountCreationLangCodes[$this->context->language->iso_code];
        }

        // Countries which available to chose
        $payment_countries = $this->getAllCountries();

        // Countries which available to create account
        $create_account_countries = $this->getAllCountries();
        unset(
            $create_account_countries['SE'],
            $create_account_countries['FI'],
            $create_account_countries['DK'],
            $create_account_countries['NO']
        );

        // Assign Smarty values
        $this->smarty->assign(
            array_merge(
                $this->requestSettings($this->requestSettingsMode()),
                [
                    'module' => $this,
                    'path' => $this->getPathUri(),
                    //'is_migration_available' => Migration::isOldModuleInstalled() && !Migration::isMigrationWasPerformed(),
                    'is_migration_available' => false,
                    'migration_ajax_url' => $this->getControllerUrl('migrate'),
                    'installed' => (bool) Utils::getConfig('installed'),
                    'installation' => Utils::getConfig('installation'),
                    'action' => \AdminController::$currentIndex .
                                '&configure=' . $this->name .
                                '&token=' . \Tools::getAdminTokenLite('AdminModules'),
                    'webhook_url' => $this->getControllerUrl('webhook'),
                    'payment_methods' => $this->coreLibrary->getPaymentMethods(),
                    'payment_categories' => $this->getPaymentCategories(),
                    'payment_countries' => $payment_countries,
                    'create_account_countries' => $create_account_countries,
                    'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
                    'template_dir' => dirname(__FILE__) . '/views/templates/',
                    'module_name' => $this->name,
                    'account_creation_lang' => $lang,
                    'admin_email' => \Context::getContext()->employee->email,

                    // WhiteLabels
                    'logo_url' => $this->coreLibrary->getWhiteLabelsData()->getLogoUrl(),
                    'ticket_placeholder' => $this->coreLibrary->getWhiteLabelsData()->getSupportTicketPlaceholder(),
                    'template_guid_ecom' => $this->coreLibrary->getWhiteLabelsData()->getTemplateGuidEcom(),
                    'template_guid_flex' => $this->coreLibrary->getWhiteLabelsData()->getTemplateGuidFlex(),
                    'template_guid_paypal' => $this->coreLibrary->getWhiteLabelsData()->getTemplateGuidPaypal()
                ]
            )
        );

        // Render templates
        foreach (['settings-header', 'form'] as $template) {
            $this->form_html .= $this->display(
                str_replace(
                    __FILE__,
                    'PrestaShopConnector.php',
                    $this->name . '.php'
                ),
                $template . '.tpl'
            );
        }

        return $this->form_html;
    }

    /**
     * Validate plugin settings (in the admin)
     *
     * @return array
     */
    private function validateSettings()
    {
        $errors = [];

        // Get settings
        $default = \IngenicoClient\Configuration::getDefault();
        foreach ($default as $fieldKey => $value) {
            $fieldValue = \Tools::getValue($fieldKey);

            // Validate field
            $error = $this->coreLibrary->getConfiguration()->validate($fieldKey, $fieldValue);
            if (is_string($error)) {
                $errors[] = $error;
                continue;
            }

            // Validate Upload
            if ($fieldKey === 'paymentpage_template_localfilename' &&
                file_exists($_FILES['paymentpage_template_localfilename']['tmp_name']) &&
                is_uploaded_file($_FILES['paymentpage_template_localfilename']['tmp_name'])
            ) {
                $target_dir = _PS_ROOT_DIR_ . '/modules/' . $this->name . '/uploads/';
                $target_file = $target_dir . basename($_FILES['paymentpage_template_localfilename']['name']);
                $template_file_type = \Tools::strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                if ($template_file_type !== 'html') {
                    $errors[] = $this->trans('validator.extension_html_only', [], 'messages');
                }

                $mime = mime_content_type($_FILES['paymentpage_template_localfilename']['tmp_name']);
                if ($mime !== 'text/html') {
                    $errors[] = $this->trans('validator.mime_html_only', [], 'messages');
                    unset($_FILES['paymentpage_template_localfilename']['tmp_name']);
                }
            }
        }

        // Validate additional settings
        if (\Tools::getValue('notification_order_paid') &&
            !filter_var(\Tools::getValue('notification_order_paid_email'), FILTER_VALIDATE_EMAIL)
        ) {
            $errors[] = $this->trans('Invalid e-mail address', [], 'messages');
        }

        if (\Tools::getValue('notification_refund_failed') &&
            !filter_var(\Tools::getValue('notification_refund_failed_email'), FILTER_VALIDATE_EMAIL)
        ) {
            $errors[] = $this->trans('Invalid e-mail address', [], 'messages');
        }

        return $errors;
    }

    /**
     * Save plugin settings.
     *
     * @return void
     */
    private function saveSettings()
    {
        // Validate settings
        $errors = $this->validateSettings();
        foreach ($errors as $error) {
            $this->form_html .= $this->displayError($error);
        }

        // Get settings
        $default = \IngenicoClient\Configuration::getDefault();
        foreach ($default as $fieldKey => $value) {
            $fieldValue = \Tools::getValue($fieldKey);

            // Set field's value
            $this->coreLibrary->getConfiguration()->setData($fieldKey, $fieldValue);
        }

        // Save configuration
        try {
            $this->coreLibrary->getConfiguration()->save();
        } catch (\Exception $e) {
            // Configuration saving errors here
        }

        // Save additional settings
        if (count($errors) === 0) {
            $suffix = $this->coreLibrary->getConfiguration()->getMode() ? 'live' : 'test';
            $additional = [
                'notification_order_paid',
                'notification_order_paid_email',
                'notification_refund_failed',
                'notification_refund_failed_email'
            ];

            foreach ($additional as $fieldKey) {
                $fieldValue = \Tools::getValue($fieldKey);

                switch ($fieldKey) {
                    case 'notification_order_paid':
                    case 'notification_refund_failed':
                        if (is_bool($fieldValue)) {
                            $fieldValue = $fieldValue ? 'on' : 'off';
                        }

                        Utils::updateConfig($fieldKey . '_' . $suffix, $fieldValue);
                        break;
                    default:
                        Utils::updateConfig($fieldKey . '_' . $suffix, $fieldValue);
                }
            }
        }

        // Copy test settings to live
        // Checks if test settings are already copied to live
        $testToLive = Utils::getConfig('test_to_live');
        if (!$testToLive && \Tools::getValue('connection_mode') === 'on') {
            $this->coreLibrary->getConfiguration()->copyToLive();
            Utils::updateConfig('test_to_live', 1);
        }

        // Mark as installed
        if (!Utils::getConfig('installed') && count($errors) === 0) {
            $this->form_html .= $this->displayConfirmation($this->trans('form.install.success', [], 'messages'));
            Utils::updateConfig('installed', 1);
        }

        // Save mode flag
        Utils::updateConfig('mode', \Tools::getValue('mode'));
    }

    /**
     * Set Generic Merchant Country.
     *
     * @param $country
     * @return void
     * @throws \IngenicoClient\Exception
     */
    public function setGenericCountry($country)
    {
        $this->coreLibrary->setGenericCountry($country);
    }

    /**
     * Filters countries based on the search string.
     *
     * @param $query
     * @param $selected_countries array of selected countries iso codes
     * @return array
     */
    public function filterCountries($query, $selected_countries)
    {
        $countries_list = $this->getAllCountries();
        foreach ($countries_list as $iso_code => $country) {
            if (\Tools::substr(\Tools::strtolower($country), 0, \Tools::strlen($query)) != \Tools::strtolower($query)) {
                if (!in_array($iso_code, $selected_countries)) {
                    unset($countries_list[$iso_code]);
                }
            }
        }

        return $countries_list;
    }

    /**
     * Filters payment methods based on the search string.
     *
     * @param $query
     * @return array
     */
    public function filterPaymentMethods($query)
    {
        $payment_methods = $this->getPaymentMethods();
        //$selected_payment_methods = $this->getSelectedPaymentMethods();

        /** @var \IngenicoClient\PaymentMethod\PaymentMethod $payment_method */
        foreach ($payment_methods as $key => $payment_method) {
            if (\Tools::substr(\Tools::strtolower($payment_method->getName()), 0, \Tools::strlen($query)) != \Tools::strtolower($query)) {
                unset($payment_methods[$key]);
            }
        }

        return $payment_methods;
    }

    /**
     * Retrieves payment method by Brand value.
     *
     * @param $brand
     * @return PaymentMethod|false
     */
    public function getPaymentMethodByBrand($brand)
    {
        return $this->coreLibrary->getPaymentMethodByBrand($brand);
    }

    /**
     * Save Payment data.
     * This data helps to avoid constant pinging of Ingenico to get PAYID and other information
     *
     * @param $orderId
     * @param \IngenicoClient\Payment $data
     *
     * @return bool
     */
    public function logIngenicoPayment($orderId, \IngenicoClient\Payment $data)
    {
        return $this->payment->logIngenicoPayment($orderId, $data);
    }

    /**
     * Retrieves payment log for the specified order ID.
     *
     * @param $orderId
     *
     * @return \IngenicoClient\Payment
     */
    public function getIngenicoPaymentLog($orderId)
    {
        $result = $this->payment->getIngenicoPaymentLog($orderId);

        return new \IngenicoClient\Payment($result ? $result : []);
    }

    /**
     * Retrieves payment log entry by the specified Pay ID (PAYID).
     *
     * @param $payId
     *
     * @return \IngenicoClient\Payment
     */
    public function getIngenicoPaymentById($payId)
    {
        $result = $this->payment->getIngenicoPaymentById($payId);

        return new \IngenicoClient\Payment($result ? $result : []);
    }

    /**
     * Retrieves Ingenico Pay ID by the specified platform order ID.
     *
     * @param $orderId
     * @return string|false
     */
    public function getIngenicoPayIdByOrderId($orderId)
    {
        return $this->payment->getIngenicoPayIdByOrderId($orderId);
    }

    /**
     * Retrieves buyer (customer) aliases by the platform's customer ID.
     *
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId)
    {
        return $this->alias->getCustomerAliases($customerId);
    }

    /**
     * Retrieves an Alias object with the fields as an array by the Alias ID (platform's entity identifier).
     * Fields list: alias_id, customer_id, ALIAS, ED, BRAND, CARDNO, BIN, PM.
     *
     * @param $aliasId
     * @return array|false
     */
    public function getAlias($aliasId)
    {
        return $this->alias->getAlias($aliasId);
    }

    /**
     * Saves the buyer (customer) Alias entity.
     * Important fields that are provided by Ingenico: ALIAS, BRAND, CARDNO, BIN, PM, ED.
     *
     * @param int $customerId
     * @param array $data
     * @return bool
     */
    public function saveAlias($customerId, array $data)
    {
        return $this->alias->saveAlias($customerId, $data);
    }

    /**
     * Delegates cron jobs handling to the CL.
     *
     * @return void
     */
    public function cronHandler()
    {
        $this->coreLibrary->cronHandler();
    }

    /**
     * Retrieves the list of orders that have no payment status at all or have an error payment status.
     * Used for the cron job that is proactively updating orders statuses.
     * Returns an array with order IDs.
     *
     * @return array
     */
    public function getNonactualisedOrdersPaidWithIngenico()
    {
        $sql = new \DbQuery();
        $sql->select('o.*');
        $sql->from('orders', 'o');
        $sql->where('o.module = "' . pSQL($this->name) . '"');

        $result = [];
        $rows = \Db::getInstance()->executeS($sql);
        foreach ($rows as $key => $row) {
            if ($this->isPaymentStatusActualised($row['id_order'])) {
                continue;
            }

            $result[] = $row['id_order'];
        }

        return $result;
    }

    /**
     * Sets PaymentStatus.Actualised Flag.
     * Used for the cron job that is proactively updating orders statuses.
     *
     * @param $orderId
     * @param bool $value
     * @return bool
     */
    public function setIsPaymentStatusActualised($orderId, $value)
    {
        return \Db::getInstance()->insert(
            'ingenico_cron',
            [
                'order_id' => $orderId,
                'is_actualised' => (int) $value
            ],
            false,
            false,
            \Db::ON_DUPLICATE_KEY
        );
    }

    /**
     * Checks if PaymentStatus is actualised (up to date)
     *
     * @param $orderId
     * @return bool
     */
    private function isPaymentStatusActualised($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('c.is_actualised');
        $sql->from('ingenico_cron', 'c');
        $sql->where(sprintf('c.order_id = %d', (int) $orderId));

        if ($value = \Db::getInstance()->getValue($sql)) {
            return (bool) $value;
        }

        return false;
    }

    /**
     * Retrieves the list of orders for the reminder email.
     *
     * @return array
     */
    public function getPendingReminders()
    {
        $reminders = $this->reminder->getPendingReminders();

        $result = [];
        foreach ($reminders as $reminder) {
            $result[] = $reminder['order_id'];
        }

        return $result;
    }

    /**
     * Sets order reminder flag as "Sent".
     *
     * @param $orderId
     *
     * @return void
     */
    public function setReminderSent($orderId)
    {
        $reminders = $this->reminder->getPendingReminders();
        foreach ($reminders as $reminder) {
            if ($orderId === $reminder['order_id']) {
                $this->reminder->updateReminder($orderId, [
                    'is_sent' => 1
                ]);
            }
        }
    }

    /**
     * Enqueues the reminder for the specified order.
     * Used for the cron job that is sending payment reminders.
     *
     * @param mixed $orderId
     * @return void
     */
    public function enqueueReminder($orderId)
    {
        if (!$this->reminder->getReminder($orderId)) {
            $order = new \Order($orderId);
            $timestamp = strtotime($order->date_add);
            $this->reminder->setReminder($orderId, $timestamp, false);
        }
    }

    /**
     * Retrieves the list of orders that are candidates for the reminder email.
     * Returns an array with orders IDs.
     *
     * @return array
     */
    public function getOrdersForReminding()
    {
        $statuses = [
            \Configuration::get(self::PS_OS_PENDING),
            \Configuration::get(self::PS_OS_CANCELLED)
        ];

        $sql = new \DbQuery();
        $sql->select('o.*');
        $sql->from('orders', 'o');
        $sql->where('o.module = "' . pSQL($this->name) . '"');
        $sql->where('o.current_state IN (' . implode(',', $statuses). ')');
        $sql->where('id_order NOT IN ((SELECT order_id FROM ' . _DB_PREFIX_ . 'ingenico_reminder))');

        $result = [];
        $rows = \Db::getInstance()->executeS($sql);
        foreach ($rows as $key => $row) {
            $result[] = $row['id_order'];
        }

        return $result;
    }

    /**
     * Redirects merchant to order detail view.
     * Merchant receives the link with order_id from manual capture notification e-mail.
     */
    private function redirectAdminOrderDetails()
    {
        $order_cookie_name = $this->name.'_order';
        if (\Tools::getValue($order_cookie_name)) {
            $orderId = \Tools::getValue($order_cookie_name);
            if ($this->context->employee->id) {
                $order_link = \Context::getContext()->link->getAdminLink('AdminOrders', true).'&id_order='.$orderId.'&vieworder';
                \Tools::redirect($order_link);
            } else {
                $this->context->cookie->{$order_cookie_name} = \Tools::getValue($order_cookie_name);
            }
        }
        if ($this->context->cookie->{$order_cookie_name}) {
            if ($this->context->employee->id) {
                $orderId = $this->context->cookie->{$order_cookie_name};
                $order_link = \Context::getContext()->link->getAdminLink('AdminOrders', true).'&id_order='.$orderId.'&vieworder';
                $this->context->cookie->{$order_cookie_name} = false;
                \Tools::redirect($order_link);
            }
        }
    }

    /**
     * Generates the order view URL.
     *
     * @param $orderId
     * @return string URL
     */
    private function getOrderViewUrl($orderId)
    {
        $adminDir =  Utils::getConfig('admin_dir');

        return \Tools::getShopDomain(true).'/'.$adminDir.'/index.php?controller=AdminOrders&'.$this->name.'_order='.$orderId;
    }

    /**
     * Delegates to the CL the complete processing of the onboarding data and dispatching email to the corresponding
     *  Ingenico sales representative.
     *
     * @param string $companyName
     * @param string $email
     * @param string $countryCode
     *
     * @throws \IngenicoClient\Exception
     */
    public function submitOnboardingRequest($companyName, $email, $countryCode)
    {
        $this->coreLibrary->submitOnboardingRequest(
            $companyName,
            $email,
            $countryCode,
            'PrestaShop',
            $this->version,
            \Configuration::get('PS_SHOP_NAME'),
            _PS_IMG_DIR_ . \Configuration::get('PS_LOGO'),
            \Context::getContext()->link->getPageLink('index', true),
            $this->getPath(true) . 'views/imgs/logo.png',
            $this->getLocale()
        );
    }

    /**
     * Renders page with Inline's Loader template.
     * This template should include code that allow charge payment asynchronous.
     *
     * @param array $fields
     * @return void
     */
    public function showInlineLoaderTemplate(array $fields)
    {
        // Create form token
        $token = md5(uniqid());
        Utils::setSessionValue('ingenico_token', $token);

        // Assign data for Smarty
        $this->context->smarty->assign([
            'suffix' => _PS_MODE_DEV_ ? '' : '.min',
            'module_dir' => $this->getPath(true),
            'ajax_url' => $this->getControllerUrl('ajax'),
            'token' => $token,
            'order_id' => $fields[Connector::PARAM_NAME_ORDER_ID],
            'alias_id' => $fields[Connector::PARAM_NAME_ALIAS_ID],
            'card_brand' => $fields[Connector::PARAM_CARD_BRAND],
            'card_no' => $fields[Connector::PARAM_CARD_CN],
        ]);

        // Render template
        $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/inline-loader.tpl');
    }

    /**
     * Renders the template of the payment success page.
     *
     * @param array $fields
     * @param \IngenicoClient\Payment $payment
     *
     * @return void
     */
    public function showSuccessTemplate(array $fields, \IngenicoClient\Payment $payment)
    {
        // Empty Cart
        $this->context->cart->delete();
        $this->context->cookie->id_cart = 0;
        Utils::unsetSessionValue('ingenico_order');
        Utils::unsetSessionValue('ingenico_token');

        if (isset($fields['order_id'])) {
            $params = $this->getSessionValue('delayed_order_conf_' . $fields['order_id']);
            if ($params) {
                // Unlock the confirmation sending
                $this->setSessionValue('mail_order_conf_enabled', true);

                // Send the confirmation
                \Mail::Send(
                    $params['idLang'],
                    $params['template'],
                    $params['subject'],
                    $params['templateVars'],
                    $params['to'],
                    $params['toName'],
                    $params['from'],
                    $params['fromName'],
                    $params['fileAttachment'],
                    $params['mode_smtp'],
                    $params['templatePath'],
                    false,
                    $params['idShop']
                );
            }
        }

        // Assign data for Smarty
        $this->context->smarty->assign([
            'status' => 'success',
            'suffix' => _PS_MODE_DEV_ ? '' : '.min',
            'path' => $this->getPath(true),
            'module_name' => $this->name,
            'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
            'success_page' => $this->buildPlatformUrl(IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_SUCCESS, [
                'order_id' => $fields['order_id']
            ]),
        ]);

        $this->context->smarty->assign($fields);

        // Render template
        $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/success.tpl');
    }

    /**
     * Renders the template with 3Ds Security Check.
     *
     * @param array $fields
     * @param \IngenicoClient\Payment $payment
     *
     * @return void
     */
    public function showSecurityCheckTemplate(array $fields, \IngenicoClient\Payment $payment)
    {
        // Assign data for Smarty
        $this->context->smarty->assign($fields);

        // Render template
        $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/secure.tpl');
    }

    /**
     * Renders the template with the order cancellation.
     *
     * @param array $fields
     * @param \IngenicoClient\Payment $payment
     *
     * @return void
     */
    public function showCancellationTemplate(array $fields, \IngenicoClient\Payment $payment)
    {
        // Reset checkout session
        Utils::unsetSessionValue('ingenico_order');
        Utils::unsetSessionValue('ingenico_token');

        // Restore cart and redirect customer to cart page
        if (isset($fields['order_id'])) {
            $this->updateOrderStatus(
                $fields['order_id'],
                $payment,
                $this->trans('checkout.payment_cancelled', [], 'messages')
            );

            $this->restoreCart($fields['order_id']);
        }

        $this->controller->warning[] = $this->trans('checkout.payment_cancelled', [], 'messages');
        $this->controller->redirectWithNotifications($this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            array(
                'action' => 'show'
            ),
            false,
            null,
            false
        ));
    }

    /**
     * Renders the template with the payment error.
     *
     * @param array $fields
     * @param \IngenicoClient\Payment $payment
     *
     * @return void
     */
    public function showPaymentErrorTemplate(array $fields, \IngenicoClient\Payment $payment)
    {
        // Reset checkout session
        Utils::unsetSessionValue('ingenico_order');
        Utils::unsetSessionValue('ingenico_token');

        // Restore cart and redirect customer to cart page
        if (isset($fields['order_id']) && $fields['order_id']) {
            $this->updateOrderStatus(
                $fields['order_id'],
                $payment,
                $this->trans('checkout.payment_cancelled', [], 'messages')
            );

            $this->restoreCart($fields['order_id']);
        }

        // Inline cc form: Cancellation
        if (array_key_exists('order_id', $fields) && is_null($fields['order_id'])) {
            $this->context->smarty->assign([
                'message' => $this->trans(
                    'Please %s try again %s or choose another payment method.',
                    [
                        "<a href='javascript:;' id='ingenico-cc-iframe-retry'>",
                        "</a>"
                    ],
                    'messages'
                ),
                'aliasId' => $_REQUEST[\IngenicoClient\IngenicoCoreLibrary::ALIAS_ID],
                'cardBrand' => $_REQUEST[\IngenicoClient\IngenicoCoreLibrary::CARD_BRAND],
                'iFrameUrl' => $this->coreLibrary->getCcIFrameUrlBeforePlaceOrder(\Tools::getValue('order_id')),
            ]);

            $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/cc_cancellation.tpl');
            return;
        }

        $this->setOrderErrorPage($fields['message']);
    }

    /**
     * Renders the template of payment methods list for the redirect mode.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListRedirectTemplate(array $fields)
    {
        // Assign data for Smarty
        $this->context->smarty->assign([
            'type' => self::PAYMENT_MODE_REDIRECT,
            'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
            'module_dir' => $this->getPath(true),
            'url' => null,
            'fields' => []
        ]);

        // Assign data for Smarty
        $this->context->smarty->assign($fields);

        // Render template
        $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/payment-list.tpl');
    }

    /**
     * Renders the template with the payment methods list for the inline mode.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListInlineTemplate(array $fields)
    {
        // Assign data for Smarty
        $this->context->smarty->assign([
            'type' => self::PAYMENT_MODE_INLINE,
            'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
            'open_invoice_url' => $this->getControllerUrl('open_invoice'),
            'module_dir' => $this->getPath(true),
        ]);

        // Assign data for Smarty
        $this->context->smarty->assign($fields);

        // Render template
        $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/payment-list.tpl');
    }

    /**
     * Renders the template with the payment methods list for the alias selection.
     * It does require by CoreLibrary.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListAliasTemplate(array $fields)
    {
        // Empty Cart
        $this->context->cart->delete();
        $this->context->cookie->id_cart = 0;

        // Reset checkout session
        Utils::unsetSessionValue('ingenico_order');

        // Redirect to "Order success" page
        \Tools::redirect(
            $this->buildPlatformUrl(IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_SUCCESS, [
                'order_id' => $fields['order_id']
            ])
        );
    }

    /**
     * In case of error, display error page.
     *
     * @param $message
     * @return void
     */
    public function setOrderErrorPage($message)
    {
        $this->context->smarty->assign([
            'message' => $message
        ]);

        $this->controller->setTemplate('module:' . $this->name . '/views/templates/front/canceled.tpl');
    }

    /**
     * Handles incoming requests from Ingenico.
     * Passes execution to CL.
     * From there it updates order's statuses.
     * This method must return HTTP status 200/400.
     *
     * @return void
     */
    public function webhookListener()
    {
        $this->coreLibrary->webhookListener();
    }

    /**
     * Initiates payment page from the reminder email link.
     *
     *
     * @return void
     */
    public function showReminderPayOrderPage()
    {
        $secret_key = \Tools::getValue('secret_key');
        $reminder = $this->reminder->getReminderByKey($secret_key);
        if (!$reminder) {
            $this->setOrderErrorPage('Security check failed.');
            return;
        }

        // Get Order
        $orderId = $reminder['order_id'];
        $order = new \Order($orderId);

        // Check order status
        if (!in_array($order->getCurrentState(), [
            \Configuration::get(self::PS_OS_PENDING),
            \Configuration::get(self::PS_OS_CANCELLED)
        ])) {
            $this->setOrderErrorPage('Unable to pay order. Wrong order status.');
            return;
        }

        // Require login
        if ((int) $this->context->customer->id === 0 && (int) $order->id_customer > 0) {
            $backUrl = $this->getControllerUrl('pay', ['secret_key' => $secret_key]);
            \Tools::redirect($this->context->link->getPageLink('auth', true, null, 'back=' . $backUrl));
            return;
        } elseif ((int) $this->context->customer->id > 0 && (int) $this->context->customer->id !== (int) $order->id_customer) {
            $this->setOrderErrorPage('This link is linked to another user. You cannot access it.');
            return;
        } else {
            // There is guest
        }

        // Restore cart
        // This order will be changed to "Cancelled" status
        $this->updateOrderStatus(
            $orderId,
            IngenicoCoreLibrary::STATUS_CANCELLED,
            'Reminder: Cart restored.'
        );
        $this->restoreCart($orderId);

        \Tools::redirect($this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            array(
                'action' => 'show'
            ),
            false,
            null,
            false
        ));
    }

    /**
     * Empty Shopping Cart and reset session.
     *
     * @return void
     */
    public function emptyShoppingCart()
    {
        // Empty Cart
        $this->context->cart->delete();
        $this->context->cookie->id_cart = 0;
        Utils::unsetSessionValue('ingenico_order');
        Utils::unsetSessionValue('ingenico_token');
    }

    /**
     * Restore Shopping Cart.
     */
    public function restoreShoppingCart()
    {
        if ($orderId = Utils::getSessionValue('ingenico_order')) {
            $this->restoreCart($orderId);
        }
    }

    /**
     * Process OpenInvoice Payment.
     *
     * @param mixed $orderId
     * @param \IngenicoClient\Alias $alias
     * @param array $fields Form fields
     * @return void
     */
    public function processOpenInvoicePayment($orderId, \IngenicoClient\Alias $alias, array $fields = [])
    {
        try {
            $this->coreLibrary->initiateOpenInvoicePayment($orderId, $alias, $fields);
        } catch (\Exception $e) {
            // Show Error Page
            $this->setOrderErrorPage($e->getMessage());
        }
    }

    /**
     * Process if have invalid fields of OpenInvoice.
     *
     * @param $orderId
     * @param \IngenicoClient\Alias $alias
     * @param array $fields
     */
    public function clarifyOpenInvoiceAdditionalFields($orderId, \IngenicoClient\Alias $alias, array $fields)
    {
        $paymentMethod = $alias->getPaymentMethod();
        $missingFields = $this->coreLibrary->validateOpenInvoiceCheckoutAdditionalFields(
            $orderId,
            $paymentMethod
        );

        $paymentMethod->setMissingFields($missingFields);

        // Show page with list of payment methods
        $this->showPaymentListInlineTemplate([
            Connector::PARAM_NAME_ORDER_ID => $orderId,
            Connector::PARAM_NAME_CATEGORIES => $this->getPaymentCategories(),
            Connector::PARAM_NAME_METHODS => [$paymentMethod],
            Connector::PARAM_NAME_CC_URL => null
        ]);
    }

    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public function getSessionValues()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION;
    }

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue($key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return false;
    }

    /**
     * Store value in Session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSessionValue($key, $value)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[$key] = $value;
    }

    /**
     * Remove value from Session.
     *
     * @param $key
     * @return void
     */
    public function unsetSessionValue($key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Get Platform Environment.
     *
     * @return string
     */
    public function getPlatformEnvironment()
    {
        return IngenicoCoreLibrary::PLATFORM_INGENICO;
    }

    /**
     * Returns OrderID.
     * If it contains "cartId" then retrieves OrderID by CartID.
     *
     * @param $orderId
     * @return int
     */
    protected function getOrderId($orderId)
    {
        if (strpos($orderId, 'cartId') !== false) {
            $cartId = str_replace('cartId', '', $orderId);
            $cart = new \Cart((int) $cartId);
            if ($cart->orderExists()) {
                $order = \Order::getByCartId($cartId);
                return $order->id;
            }
        }

        return $orderId;
    }
}

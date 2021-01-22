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
if (!class_exists('\\IngenicoClient\\IngenicoCoreLibrary', false)) {
    require dirname(__FILE__) . '/vendor/autoload.php';
}

require dirname(__FILE__) . '/setup/Install.php';
require dirname(__FILE__) . '/utils/Utils.php';
require dirname(__FILE__) . '/PrestaShopConnector.php';

use Ingenico\PrestaShopConnector;
use Ingenico\Utils;
use Ingenico\Setup\Install;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;
use IngenicoClient\IngenicoCoreLibrary;

class Ingenico_Epayments extends PrestaShopConnector
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * Ingenico_epayments constructor.
     */
    public function __construct()
    {
        $this->name = 'ingenico_epayments';
        $this->tab = 'payments_gateways';
        $this->version = '3.0.0';
        $this->author = 'Ingenico Group';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->is_configurable = 1;
        $this->module_key = '8b809d47d078b595c59c7661bd405f54';
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        $this->controllers = ['ajax', 'canceled', 'cron', 'pay', 'payment', 'payment_list', 'success', 'webhook'];

        $this->displayName = 'Ingenico ePayments';
        $this->description = 'Payment Gateway by Ingenico Group';

        parent::__construct();

        // Initialize translations
        $lang = new Language((int) $this->context->language->id);
        $this->translator = Context::getContext()->getTranslator();
        $this->translator->addLoader('po', new PoFileLoader());
        $this->translator->setFallbackLocales([$lang->locale, 'en-us']);
        $this->translator->setLocale($lang->locale);

        // Load translations of the module
        $languages = Language::getLanguages(true);
        $directory = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'translations';
        $files = scandir($directory);
        foreach ($files as $file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;
            $info = pathinfo($file);
            if ($info['extension'] !== 'po') {
                continue;
            }

            // Locale of the module i.e. en-us
            $moduleLocale = mb_strtolower($info['filename']);

            // Load the locale that was installed in the shop
            foreach ($languages as $language) {
                $shopLocale = mb_strtolower(mb_substr($language['locale'], 0, 2, 'UTF-8'), 'UTF-8');
                if (mb_substr($moduleLocale, 0, 2, 'UTF-8') === $shopLocale) {
                    $this->translator->addResource('po', $directory . DIRECTORY_SEPARATOR . $info['basename'], $language['locale'], 'messages');
                    break;
                }
            }
        }

        // Install or upgrade
        $install = new Install($this->name);
        $install->install();
        $install->upgrade();

        // Install actionEmailSendBefore hook if it's missing
        $hookInstalled = false;
        $hooks = Hook::getHookModuleExecList('actionEmailSendBefore');
        if ($hooks) {
            foreach ($hooks as $hook) {
                if ($hook['module'] === $this->name) {
                    $hookInstalled = true;
                }
            }
        }

        if (!$hookInstalled) {
            $this->registerHook('actionEmailSendBefore');
        }
    }

    /**
     * Module install.
     * @return bool
     */
    public function install()
    {
        $install = new Install();
        if (!parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('backOfficeHeader') ||
            !$this->registerHook('displayBackOfficeOrderActions') ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionCronJob') ||
            !$this->registerHook('paymentOptions') ||
            !$this->registerHook('paymentReturn') ||
            !$this->registerHook('actionEmailSendBefore') ||
            !$install->install() ||
            !$this->saveAdminDir()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Module uninstall.
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Hook: Header
     */
    public function hookHeader()
    {
        // Add JS and CSS
        $this->context->controller->addJquery();

        if (_PS_MODE_DEV_) {
            $this->context->controller->addJS($this->getPath(true) . 'views/js/front.js');
            $this->context->controller->addCSS($this->getPath(true) . 'views/css/front.css');
        } else {
            $this->context->controller->addJS($this->getPath(true) . 'views/js/front.min.js');
            $this->context->controller->addCSS($this->getPath(true) . 'views/css/front.min.css');
        }
    }

    /**
     * Hook: BackOfficeHeader
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') === $this->name || $this->context->controller->controller_name === 'AdminOrders') {
            // Add JS and CSS in admin backend
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryPlugin('ui.slider');

            if (_PS_MODE_DEV_) {
                $this->context->controller->addJS($this->getPath(true) . 'views/js/async.js');
                $this->context->controller->addJS($this->getPath(true) . 'views/js/backoffice.js');
                $this->context->controller->addCSS($this->getPath(true) . 'views/css/backoffice.css');
            } else {
                $this->context->controller->addJS($this->getPath(true) . 'views/js/async.min.js');
                $this->context->controller->addJS($this->getPath(true) . 'views/js/backoffice.min.js');
                $this->context->controller->addCSS($this->getPath(true) . 'views/css/backoffice.min.css');
            }

            // ViewOrder Page
            if (Tools::getValue('vieworder') !== false &&  Tools::getValue('id_order') !== false) {
                $order = new Order(Tools::getValue('id_order'));
                if ($order->module === $this->name) {
                    // Ingenico Actions
                    if (Tools::isSubmit('ingenico_action') && Tools::getValue('order_id')) {
                        $action = Tools::getValue('ingenico_action');
                        $orderId = Tools::getValue('order_id');
                        $payId = Tools::getValue('pay_id');

                        try {
                            switch ($action) {
                                case 'capture':
                                    $captureAmount = (float) Tools::getValue('capture_amount');
                                    if ($captureAmount > $this->total->getAvailableCaptureAmount($orderId)) {
                                        throw new \Exception($this->trans('order.action.capture_too_much', [], 'messages'));
                                    }

                                    if ($captureAmount <= 0) {
                                        throw new \Exception($this->trans('order.action.capture_too_little', [], 'messages'));
                                    }

                                    if ($captureAmount == $order->total_paid_tax_incl) {
                                        $result = $this->coreLibrary->capture($orderId, $payId);
                                    } else {
                                        $result = $this->coreLibrary->capture($orderId, $payId, $captureAmount);
                                    }

                                    switch ($result->getPaymentStatus()) {
                                        case IngenicoCoreLibrary::STATUS_CAPTURE_PROCESSING:
                                            $message = $this->trans('order.action.capture_pending', [], 'messages');
                                            break;
                                        case IngenicoCoreLibrary::STATUS_CAPTURED:
                                            $message = $this->trans('order.action.captured', [], 'messages');
                                            break;
                                        default:
                                            $message = '';
                                            break;
                                    }


                                    header('Content-Type: application/json');
                                    echo json_encode([
                                        'status' => 'ok',
                                        'message' => $message
                                    ]);
                                    exit();
                                case 'cancel':
                                    $this->coreLibrary->cancel($orderId, $payId);

                                    header('Content-Type: application/json');
                                    echo json_encode([
                                        'status' => 'ok',
                                        'message' => $this->trans('order.action.cancelled', [], 'messages')
                                    ]);
                                    exit();
                                case 'refund':
                                    $refundAmount = (float) Tools::getValue('refund_amount');
                                    if ($refundAmount > $this->total->getAvailableRefundAmount($orderId)) {
                                        throw new \Exception($this->trans('order.action.refund_too_much', [], 'messages'));
                                    }

                                    if ($refundAmount <= 0) {
                                        throw new \Exception($this->trans('order.action.refund_too_little', [], 'messages'));
                                    }

                                    $result = $this->coreLibrary->refund($orderId, $payId, $refundAmount);
                                    switch ($result->getPaymentStatus()) {
                                        case IngenicoCoreLibrary::STATUS_REFUND_PROCESSING:
                                            $message = $this->trans('order.action.refund_pending', [], 'messages');
                                            break;
                                        case IngenicoCoreLibrary::STATUS_REFUNDED:
                                            $message = $this->trans('order.action.refunded', [], 'messages');
                                            break;
                                        default:
                                            $message = '';
                                            break;
                                    }

                                    header('Content-Type: application/json');
                                    echo json_encode([
                                        'status' => 'ok',
                                        'message' => $message
                                    ]);
                                    exit();
                            }
                        } catch (Exception $e) {
                            header('Content-Type: application/json');

                            /**
                             * 50001111 - Operation is not allowed : check user privileges
                             * 50001218 - Operation not permitted for the merchant
                             * 50001046 - Operation not permitted for the merchant
                             * 50001186 - Operation not permitted
                             * 50001187 - Operation not permitted
                             */
                            if ($action === 'refund' &&
                                in_array((string) $e->getCode(), ['50001111', '50001218', '50001046', '50001186', '50001187'])
                            ) {
                                echo json_encode([
                                    'status' => 'action_required',
                                    'message' => $e->getMessage()
                                ]);
                                exit();
                            }

                            echo json_encode([
                                'status' => 'error',
                                'message' => $e->getMessage()
                            ]);
                            exit();
                        }
                    }
                }
            }
        }
    }

    /**
     * Hook: DisplayBackOfficeOrderActions
     * @param $params
     */
    public function hookDisplayBackOfficeOrderActions($params)
    {
        $orderId = $params['id_order'];

        // Get Payment Details
        $data = $this->getIngenicoPaymentLog($orderId);
        if (!$data->getOrderId()) {
            // Fetch payment info
            try {
                $data = $this->coreLibrary->getPaymentInfo($orderId);
                if ($data->isTransactionSuccessful()) {
                    $this->logIngenicoPayment($orderId, $data);
                }
            } catch (Exception $e) {
                $this->logger->debug($e->getMessage());
                return;
            }
        }

        // Get available refund amount
        $refundAmount = $this->total->getAvailableRefundAmount($orderId);
        $captureAmount = $this->total->getAvailableCaptureAmount($orderId);
        $cancelAmount = $this->total->getAvailableCancelAmount($orderId);

        $this->context->smarty->assign([
            'template_dir' => dirname(__FILE__) . '/views/templates/',
            'can_refund' => $this->coreLibrary->canRefund($data->getOrderId(), $data->getPayId(), $refundAmount),
            'can_capture' => $this->coreLibrary->canCapture($data->getOrderId(), $data->getPayId(), $captureAmount),
            'can_cancel' => $this->coreLibrary->canVoid($data->getOrderId(), $data->getPayId(), $cancelAmount),
            'refund_amount' => $refundAmount,
            'capture_amount' => $captureAmount,
            'cancel_amount' => $cancelAmount,
            'order_id' => $orderId,
            'status' => $data->getStatus(),
            'pay_id' => $data->getPayId(),

            // WhiteLabels
            'support_email' => $this->coreLibrary->getWhiteLabelsData()->getSupportEmail(),
            'support_phone' => $this->coreLibrary->getWhiteLabelsData()->getSupportPhone(),
        ]);

        echo $this->display(__FILE__, 'views/templates/admin/order-actions.tpl');
    }

    /**
     * Hook: DisplayAdminOrder
     */
    public function hookDisplayAdminOrder($params)
    {
        // Add info section to Order view in admin backend
        $orderId = Tools::getValue('id_order', 0);
        $order = new Order($orderId);

        if ($order->module === $this->name) {
            // Get Payment Details
            $data = $this->getIngenicoPaymentLog($orderId);
            if (!$data->getOrderId()) {
                // Fetch payment info
                try {
                    $data = $this->coreLibrary->getPaymentInfo($orderId);
                    $this->logIngenicoPayment($orderId, $data);
                } catch (Exception $e) {
                    $this->logger->debug($e->getMessage());
                    return;
                }
            }

            $this->context->smarty->assign([
                'order_id' => $orderId,
                'order_amount' => $data->getOrderId(),
                'status' => $data->getStatus(),
                'pay_id' => $data->getPayId(),
                'pay_id_sub' => $data->getPayIdSub(),
                'payment_method' => $data->getPm(),
                'brand' => $data->getBrand(),
                'card_no' => $data->getCardNo(),
                'cn' => $data->getCn(),
            ]);

            echo $this->display(__FILE__, 'views/templates/admin/admin-order.tpl');
        }
    }

    /**
     * Hook: actionCronJob
     */
    public function hookActionCronJob()
    {
        $signature = $this->coreLibrary->getConfiguration()->getPassphrase();

        // Trigger Cron Controller
        $cronJobUrl = $this->getControllerUrl('cron', [
            'token' => Tools::substr($signature, -5)
        ]);

        Tools::file_get_contents($cronJobUrl);
    }

    /**
     * Get Cron Frequency
     * @return array
     */
    public function getCronFrequency()
    {
        return [
            'hour'=> -1,
            'day' => -1,
            'month' => -1,
            'day_of_week' => -1
        ];
    }

    /**
     * HooK: paymentOptions
     * @param $params
     * @return array
     */
    public function hookPaymentOptions($params)
    {
        $paymentOptions = [];
        $selectedPaymentMethods = $this->coreLibrary->getSelectedPaymentMethods();
        $ccMethods = [];

        /** @var \IngenicoClient\PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($selectedPaymentMethods as $paymentMethod) {
            $paymentMethodsText[$paymentMethod->getId()] = $paymentMethod->getName();

            switch ($paymentMethod->getId()) {
                case \IngenicoClient\PaymentMethod\Amex::CODE:
                case \IngenicoClient\PaymentMethod\Bancontact::CODE:
                case \IngenicoClient\PaymentMethod\CarteBancaire::CODE:
                case \IngenicoClient\PaymentMethod\DinersClub::CODE:
                case \IngenicoClient\PaymentMethod\Discover::CODE:
                case \IngenicoClient\PaymentMethod\Jcb::CODE:
                case \IngenicoClient\PaymentMethod\Maestro::CODE:
                case \IngenicoClient\PaymentMethod\Mastercard::CODE:
                case \IngenicoClient\PaymentMethod\Visa::CODE:
                    $ccMethods[] = $paymentMethod;
                    break;
                case \IngenicoClient\PaymentMethod\Klarna::CODE:
                case \IngenicoClient\PaymentMethod\Afterpay::CODE:
                case \IngenicoClient\PaymentMethod\KlarnaPayNow::CODE:
                case \IngenicoClient\PaymentMethod\KlarnaPayLater::CODE:
                case \IngenicoClient\PaymentMethod\KlarnaBankTransfer::CODE:
                case \IngenicoClient\PaymentMethod\KlarnaDirectDebit::CODE:
                case \IngenicoClient\PaymentMethod\KlarnaFinancing::CODE:
                    // Check country of billing address
                    /** @var Cart $cart */
                    $cart = $params['cart'];
                    $billingAddress = new \Address((int) $cart->id_address_invoice);
                    $customerCountry = \Country::getIsoById($billingAddress->id_country);
                    if (!in_array($customerCountry, array_keys($paymentMethod->getCountries()))) {
                        break;
                    }

                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($paymentMethod->getName())
                        ->setLogo($paymentMethod->getEmbeddedLogo())
                        ->setAction($this->context->link->getModuleLink(
                            $this->name,
                            'open_invoice',
                            [
                                'payment_id' => $paymentMethod->getId(),
                                'pm' => $paymentMethod->getPM(),
                                'brand' => $paymentMethod->getBrand()
                            ],
                        true
                        ));

                        $paymentOptions[] = $paymentOption;
                    break;
                default:
                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($paymentMethod->getName())
                        ->setLogo($paymentMethod->getEmbeddedLogo())
                        ->setAction($this->context->link->getModuleLink(
                            $this->name,
                            'payment',
                            [
                                'payment_id' => $paymentMethod->getId(),
                                'pm' => $paymentMethod->getPM(),
                                'brand' => $paymentMethod->getBrand()
                            ],
                            true
                        ));

                    $paymentOptions[] = $paymentOption;
            }
        }

        // Add CC method
        if (count($ccMethods) > 0) {
            $customerId = (int) Context::getContext()->customer->id;
            $paymentPageType = $this->coreLibrary->getConfiguration()->getPaymentpageType();
            $oneClickPayments = $this->coreLibrary->getConfiguration()->getSettingsOneclick();

            // Load aliases
            $aliases = [];
            if ($oneClickPayments && $customerId > 0) {
                $aliases = $this->coreLibrary->getCustomerAliases($customerId);
                foreach ($aliases as $alias) {
                    $alias->setTranslatedName($this->trans('%brand% ends with %cardno%, expires on %month%/%year%', [
                        '%brand%' => $alias->getBrand(),
                        '%cardno%' => substr($alias->getCardno(),-4,4),
                        '%month%' => substr($alias->getEd(), 0, 2),
                        '%year%' => substr($alias->getEd(), 2, 4),

                    ], 'messages'));
                }
            }

            // Render the form
            $this->smarty->assign(
                [
                    'action' => $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        [
                            'payment_id' => 'visa',
                            'pm' => 'CreditCard',
                            'brand' => 'CreditCard'
                        ],
                        true
                    ),
                    'one_click_payment' => $oneClickPayments,
                    'aliases' => $aliases,
                    'payment_page_type' => $paymentPageType,
                    'frame_url' => $paymentPageType === 'INLINE' ? $this->coreLibrary->getCcIFrameUrlBeforePlaceOrder(
                        'cartId' . Context::getContext()->cart->id
                    ) : null
                ]
            );

            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Credit Cards', [], 'messages'))
                ->setLogo($this->getPathUri() . '/views/imgs/card-logo-unknown.svg')
                ->setAction($this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    [
                        'payment_id' => 'visa',
                        'pm' => 'CreditCard',
                        'brand' => 'CreditCard'
                    ],
                    true
                ))
                ->setForm(
                    $this->fetch('module:' . $this->name . '/views/templates/hook/cc-form.tpl')
                );

            $paymentOptions[] = $paymentOption;
        }

        // Add Generic method
        if ($this->coreLibrary->getConfiguration()->getPaymentpageType() === 'REDIRECT') {
            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Pay with Ingenico ePayments', [], 'messages'))
                ->setAdditionalInformation(
                    $this->trans('Pay safely on the next page with Ingenico using your preferred payment method',
                        [],
                        'messages'
                    )
                )
                ->setLogo($this->getPathUri() . '/views/imgs/ingenico.gif')
                ->setAction($this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    [],
                    true
                ));

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }

    /**
     * HooK: actionEmailSendBefore
     * @param $params
     * @return bool
     */
    public function hookActionEmailSendBefore($params)
    {
        if ($params['template'] === 'order_conf') {
            $orders = Order::getByReference($params['templateVars']['{order_name}']);

            /** @var Order $order */
            $order = $orders->getFirst();

            if ($order->module === $this->name) {
                // the confirmation sending locking flag
                if ($this->getSessionValue('mail_order_conf_enabled')) {
                    $this->unsetSessionValue('mail_order_conf_enabled');
                    $this->unsetSessionValue('delayed_order_conf_' . $order->id);

                    return true;
                }

                // Save params in session to send the same message after payment
                $this->setSessionValue('delayed_order_conf_' . $order->id, $params);

                // Prevent the mail sending
                return false;
            }
        }

        return true;
    }

    /**
     * Get Full Module Path Uri
     * @param bool $secure
     *
     * @return string
     */
    public function getPath($secure = false)
    {
        if ($secure) {
            return Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/';
        }

        return Tools::getShopDomain(true, true) . $this->getPathUri();
    }

    /**
     * Get controller URL
     *
     * @param $controller
     * @param array $params
     * @return string URL
     */
    public function getControllerUrl($controller, $params = [])
    {
        return $this->context->link->getModuleLink(
            $this->name,
            $controller,
            $params
        );
    }

    /**
     * Confirm and create PrestaShop order.
     *
     * @return Order|false
     */
    public function confirmOrder()
    {
        try {
            // Place order with Pending state
            $this->validateOrder(
                $this->context->cart->id,
                Configuration::get(self::PS_OS_PENDING),
                $this->context->cart->getOrderTotal(),
                $this->displayName,
                null,
                null,
                $this->context->cart->id_currency,
                false,
                $this->context->customer->secure_key
            );

            // Store OrderId value in session
            Utils::setSessionValue('ingenico_order', $this->currentOrder);

            return new Order($this->currentOrder);
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
            throw $e;
        }

        return false;
    }

    /**
     * Restores previous cart by order id.
     *
     * @param $id_order
     * @throws Exception
     * @throws PrestaShopException
     */
    public function restoreCart($id_order)
    {
        $oldCart = new Cart(Order::getCartIdStatic($id_order, $this->context->customer->id));
        $duplication = $oldCart->duplicate();
        $this->context->cookie->id_cart = $duplication['cart']->id;
        $context = $this->context;
        $context->cart = $duplication['cart'];
        CartRule::autoAddToCart($context);
        $this->context->cookie->write();
    }

    /**
     * Render HTML of payment methods by Countries ISO codes.
     * Use to generate list via ajax request in admin backend.
     *
     * @param array $countries
     * @param string|bool $openinvoice
     *
     * @return string
     */
    public function fetchPaymentMethodsByCountryTemplate(array $countries, $openinvoice)
    {
        $methods = $this->coreLibrary->getAndMergeCountriesPaymentMethods($countries);

        // Selected Open Invoice Method
        if ($openinvoice === 'afterpay') {
            // Remove Klarna if exists
            if (($key = array_search('klarna', $methods)) !== false) {
                unset($methods[$key]);
            }
        } elseif ($openinvoice === 'klarna') {
            // Remove Afterpay if exists
            if (($key = array_search('afterpay', $methods)) !== false) {
                unset($methods[$key]);
            }
        } else {
            // OpenInvoice is unavailable
            if (($key = array_search('afterpay', $methods)) !== false) {
                unset($methods[$key]);
            }

            if (($key = array_search('klarna', $methods)) !== false) {
                unset($methods[$key]);
            }
        }

        // Assign Smarty values
        $this->smarty->assign(
            [
                'module_dir' => $this->getPath(true),
                'module' => $this,
                'payment_categories' => $this->getPaymentCategories(),
                'selected_payment_methods' => $methods,
                'module_name' => $this->name,
            ]
        );

        // Render template
        return $this->fetch('module:' . $this->name . '/views/templates/hook/selected-payment-methods.tpl');
    }

    /**
     * Render HTML of payment methods.
     * Use to generate list via ajax request in admin backend.
     *
     * @param array $methods
     *
     * @return string
     */
    public function fetchPaymentMethodsTemplate(array $methods)
    {
        // Assign Smarty values
        $this->smarty->assign(
            [
                'module_dir' => $this->getPath(true),
                'module' => $this,
                'payment_categories' => $this->getPaymentCategories(),
                'selected_payment_methods' => $methods,
                'module_name' => $this->name,
            ]
        );

        // Render template
        return $this->fetch('module:' . $this->name . '/views/templates/hook/selected-payment-methods.tpl');
    }

    /**
     * Get html of modal with payment method's list
     *
     * @param array $selectedMethods
     * @return string
     */
    public function getPaymentMethodsModal($selectedMethods)
    {
        // Assign Smarty values
        $this->smarty->assign(
            [
                'payment_methods' => $selectedMethods,
                'module_name' => $this->name,
            ]
        );

        // Render template
        return $this->fetch('module:' . $this->name . '/views/templates/hook/payment-method-modal-list.tpl');
    }

    /**
     * Get Success Page Url
     *
     * @param $orderId
     * @return string
     */
    public function getSuccessPageUrl($orderId)
    {
        $order = new Order($orderId);
        $customer = new Customer($order->id_customer);
        $url = 'index.php?controller=order-confirmation&id_cart='
            . $order->id_cart . '&id_module='
            . $this->id . '&id_order='
            . $order->id . '&key='
            . $customer->secure_key;
        $base_uri = __PS_BASE_URI__;
        $link = Context::getContext()->link;
        if (strpos($url, 'http://') === false &&
            strpos($url, 'https://') === false &&
            $link) {
            if (strpos($url, $base_uri) === 0) {
                $url = Tools::substr($url, Tools::strlen($base_uri));
            }
            if (strpos($url, 'index.php?controller=') !== false &&
                strpos($url, 'index.php/') == 0) {
                $url = Tools::substr($url, Tools::strlen('index.php?controller='));
                if (Configuration::get('PS_REWRITING_SETTINGS')) {
                    $url = Tools::strReplaceFirst('&', '?', $url);
                }
            }
            $explode = explode('?', $url);
            $use_ssl = !empty($url);
            $url = $link->getPageLink($explode[0], $use_ssl);

            if (isset($explode[1])) {
                $url .= '?'.$explode[1];
            }
        }

        return $url;
    }

    /**
     * Saves admin dir
     *
     * Because Admin directory is defined only when admin is logged in
     * the dir is saved to be used later in email links generation
     * for example in admin Manual capture email
     *
     * @return bool
     */
    private function saveAdminDir()
    {
        if (defined('_PS_ADMIN_DIR_')) {
            Utils::updateConfig('admin_dir', basename(_PS_ADMIN_DIR_));

            return true;
        }
    }
}

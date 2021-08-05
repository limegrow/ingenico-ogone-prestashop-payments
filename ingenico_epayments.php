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

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

use PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ingenico\Payment\Install\InstallerFactory;
use Ingenico\Payment\Connector;
use Ingenico\Payment\Utils;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\PoFileLoader;
use IngenicoClient\IngenicoCoreLibrary;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButtonsCollection;
use PrestaShopBundle\Controller\Admin\Sell\Order\ActionsBarButton;

class Ingenico_Epayments extends PaymentModule
{
    const VERSION = '5.0.1';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ServiceContainer
     */
    private $serviceContainer;

    /**
     * Configuration HTML
     * @var string
     */
    protected $form_html = '';

    /**
     * @var Connector
     */
    public $connector;

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
        $this->version = self::VERSION;
        $this->author = 'Ingenico Group';
        $this->need_instance = 0;
        $this->bootstrap = 1;
        $this->is_configurable = 1;
        $this->module_key = '8b809d47d078b595c59c7661bd405f54';
        $this->ps_versions_compliancy = array('min' => '1.7.5.0', 'max' => _PS_VERSION_);

        $this->controllers = ['ajax', 'canceled', 'cron', 'pay', 'payment', 'payment_list', 'success', 'webhook'];

        $this->displayName = 'Ingenico ePayments';
        $this->description = 'Payment Gateway by Ingenico Group';

        parent::__construct();

        // Initialize translations
        $lang = new Language((int) $this->context->language->id);
        $this->translator = Context::getContext()->getTranslator();
        //$this->translator = $this->get('translator');
        $this->translator->addLoader('po', new PoFileLoader());
        $this->translator->setFallbackLocales([$lang->locale, 'en-us']);
        $this->translator->setLocale($lang->locale);

        $this->connector = $this->getModuleService('ingenico.payment.connector');

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
    }

    /**
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getModuleService($serviceName)
    {
        if (null === $this->serviceContainer) {
            $this->serviceContainer = new ServiceContainer($this->name, $this->getLocalPath());
        }
        return $this->serviceContainer->getService($serviceName);
    }

    /**
     * Override of native function to always retrieve Symfony container instead of legacy admin container on legacy context.
     *
     * {@inheritdoc}
     */
    public function get($serviceName)
    {
        if (null === $this->container) {
            $this->container = SymfonyContainer::getInstance();

            if (null === $this->container) {
                return $this->getModuleService($serviceName);
            }
        }

        return $this->container->get($serviceName);
    }

    /**
     * Module install.
     * @return bool
     */
    public function install()
    {
        $installer = InstallerFactory::create();
        return $this->saveAdminDir() && parent::install() && $installer->install($this);
    }

    /**
     * Module uninstall.
     * @return bool
     */
    public function uninstall()
    {
        $installer = InstallerFactory::create();

        return $installer->uninstall() && parent::uninstall();
    }

    /**
     * Hook: Header
     */
    public function hookHeader()
    {
        // Add JS and CSS
        $this->context->controller->addJquery();

        try {
            $pageType = $this->connector->coreLibrary->getConfiguration()->getPaymentpageType();
        } catch (\Exception $e) {
            $pageType = null;
        }

        Media::addJsDef([
            'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
            'ingenico_payment_page_type' => $pageType
        ]);

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

            /** @var UrlGeneratorInterface $router */
            $router = $this->get('router');

            Media::addJsDef([
                'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
                'ingenico_api_url' => $router->generate('ingenico_api'),
                'ingenico_flex_upload_url' => $this->getControllerUrl('ajax', [
                    'method' => 'flex_upload'
                ])
            ]);

            if (_PS_MODE_DEV_) {
                $this->context->controller->addJS($this->getPath(true) . 'views/js/async.js');
                $this->context->controller->addJS($this->getPath(true) . 'views/js/backoffice.js');
                $this->context->controller->addJS($this->getPath(true) . 'views/js/order-actions.js');
                $this->context->controller->addCSS($this->getPath(true) . 'views/css/backoffice.css');

                $this->context->controller->addCSS($this->getPath(true) . 'views/jsgrid/jsgrid.css');
                $this->context->controller->addCSS($this->getPath(true) . 'views/jsgrid/jsgrid-theme.css');
                $this->context->controller->addJS($this->getPath(true) . 'views/jsgrid/jsgrid.js');
            } else {
                $this->context->controller->addJS($this->getPath(true) . 'views/js/async.min.js');
                $this->context->controller->addJS($this->getPath(true) . 'views/js/backoffice.min.js');
                $this->context->controller->addJS($this->getPath(true) . 'views/js/order-actions.min.js');
                $this->context->controller->addCSS($this->getPath(true) . 'views/css/backoffice.min.css');

                $this->context->controller->addCSS($this->getPath(true) . 'views/jsgrid/jsgrid.min.css');
                $this->context->controller->addCSS($this->getPath(true) . 'views/jsgrid/jsgrid-theme.min.css');
                $this->context->controller->addJS($this->getPath(true) . 'views/jsgrid/jsgrid.min.js');
            }

            $locale = \Context::getContext()->language->iso_code;
            if (file_exists(dirname(__FILE__) . '/views/jsgrid/i18n/jsgrid-' . $locale . '.js')) {
                $this->context->controller->addJS(
                    $this->getPath(true) . 'views/jsgrid/i18n/jsgrid-' . $locale . '.js'
                );
            }
        }
    }

    /**
     * Hook: DisplayBackOfficeOrderActions
     * @depecated since PS 1.7.7, replaced to ActionGetAdminOrderButtons
     * @param $params
     */
    public function hookDisplayBackOfficeOrderActions($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            // @see hookDisplayAdminOrderSide
            return;
        }

        $orderId = $params['id_order'];
        $order = new Order($orderId);

        if ($order->module !== $this->name) {
            return;
        }

        // Get Payment Details
        $data = $this->connector->getIngenicoPaymentLog($orderId);
        if (!$data->getOrderId()) {
            // Fetch payment info
            try {
                $data = $this->connector->coreLibrary->getPaymentInfo($orderId);
                if ($data->isTransactionSuccessful()) {
                    $this->connector->logIngenicoPayment($orderId, $data);
                }
            } catch (Exception $e) {
                $this->connector->logger->debug($e->getMessage());
                return;
            }
        }

        // Get available refund amount
        $refundAmount = $this->connector->total->getAvailableRefundAmount($orderId);
        $captureAmount = $this->connector->total->getAvailableCaptureAmount($orderId);
        $cancelAmount = $this->connector->total->getAvailableCancelAmount($orderId);

        /** @var UrlGeneratorInterface $router */
        $router = $this->get('router');

        $params = [
            'template_dir' => dirname(__FILE__) . '/views/templates/',
            'ingenico_api' => $router->generate('ingenico_api'),
            'can_refund' => $this->connector->coreLibrary->canRefund($data->getOrderId(), $data->getPayId(), $refundAmount),
            'can_capture' => $this->connector->coreLibrary->canCapture($data->getOrderId(), $data->getPayId(), $captureAmount),
            'can_cancel' => $this->connector->coreLibrary->canVoid($data->getOrderId(), $data->getPayId(), $cancelAmount),
            'refund_amount' => $refundAmount,
            'capture_amount' => $captureAmount,
            'cancel_amount' => $cancelAmount,
            'order_id' => $orderId,
            'status' => $data->getStatus(),
            'pay_id' => $data->getPayId(),

            // WhiteLabels
            'support_email' => $this->connector->coreLibrary->getWhiteLabelsData()->getSupportEmail(),
            'support_phone' => $this->connector->coreLibrary->getWhiteLabelsData()->getSupportPhone(),
        ];

        $this->context->smarty->assign($params);
        echo $this->display(__FILE__, 'views/templates/admin/order-actions.tpl');
    }

    /**
     * Hook: DisplayAdminOrderSide
     * @version since PS 1.7.7
     */
    public function hookDisplayAdminOrderSide(array $params)
    {
        // Add info section to Order view in admin backend
        $orderId = $params['id_order'];
        $order = new Order($orderId);

        if ($order->module !== $this->name) {
            return;
        }

        // Get Payment Details
        $data = $this->connector->getIngenicoPaymentLog($orderId);
        if (!$data->getOrderId()) {
            // Fetch payment info
            try {
                $data = $this->connector->coreLibrary->getPaymentInfo($orderId);
                if ($data->isTransactionSuccessful()) {
                    $this->connector->logIngenicoPayment($orderId, $data);
                }
            } catch (Exception $e) {
                $this->connector->logger->debug($e->getMessage());
                return;
            }
        }

        $params = [
            'order_id' => $orderId,
            'order_amount' => $data->getOrderId(),
            'status' => $data->getStatus(),
            'pay_id' => $data->getPayId(),
            'pay_id_sub' => $data->getPayIdSub(),
            'payment_method' => $data->getPm(),
            'brand' => $data->getBrand(),
            'card_no' => $data->getCardNo(),
            'cn' => $data->getCn(),

            'refund_amount' => $this->connector->total->getAvailableRefundAmount($orderId),
            'capture_amount' => $this->connector->total->getAvailableCaptureAmount($orderId),
            'cancel_amount' => $this->connector->total->getAvailableCancelAmount($orderId),

            // WhiteLabels
            'support_email' => $this->connector->coreLibrary->getWhiteLabelsData()->getSupportEmail(),
            'support_phone' => $this->connector->coreLibrary->getWhiteLabelsData()->getSupportPhone(),
        ];

        /** @var Twig_Environment $twig */
        $twig = $this->get('twig');

        return $twig->render(
            sprintf('@Modules/%s/views/templates/admin/order-info.twig', $this->name),
            $params
        );
    }

    /**
     * Hook: DisplayAdminOrder
     */
    public function hookDisplayAdminOrder($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            // @see hookDisplayAdminOrderSide
            return;
        }

        // Add info section to Order view in admin backend
        $orderId = Tools::getValue('id_order', 0);
        $order = new Order($orderId);

        if ($order->module !== $this->name) {
            return;
        }

        // Get Payment Details
        $data = $this->connector->getIngenicoPaymentLog($orderId);
        if (!$data->getOrderId()) {
            // Fetch payment info
            try {
                $data = $this->connector->coreLibrary->getPaymentInfo($orderId);
                if ($data->isTransactionSuccessful()) {
                    $this->connector->logIngenicoPayment($orderId, $data);
                }
            } catch (Exception $e) {
                $this->connector->logger->debug($e->getMessage());
                return;
            }
        }

        $params = [
            'order_id' => $orderId,
            'order_amount' => $data->getOrderId(),
            'status' => $data->getStatus(),
            'pay_id' => $data->getPayId(),
            'pay_id_sub' => $data->getPayIdSub(),
            'payment_method' => $data->getPm(),
            'brand' => $data->getBrand(),
            'card_no' => $data->getCardNo(),
            'cn' => $data->getCn(),
        ];

        $this->context->smarty->assign($params);
        echo $this->display(__FILE__, 'views/templates/admin/order-info.tpl');
    }

    /**
     * Hook: ActionGetAdminOrderButtons
     * @version since PS 1.7.7
     */
    public function hookActionGetAdminOrderButtons(array $params)
    {
        $order = new Order($params['id_order']);

        $orderId = $order->id;

        // Get Payment Details
        $data = $this->connector->getIngenicoPaymentLog($orderId);
        if (!$data->getOrderId()) {
            // Fetch payment info
            try {
                $data = $this->connector->coreLibrary->getPaymentInfo($orderId);
                if ($data->isTransactionSuccessful()) {
                    $this->connector->logIngenicoPayment($orderId, $data);
                }
            } catch (Exception $e) {
                $this->connector->logger->debug($e->getMessage());
                return;
            }
        }

        // Get available refund amount
        $refundAmount = $this->connector->total->getAvailableRefundAmount($orderId);
        $captureAmount = $this->connector->total->getAvailableCaptureAmount($orderId);
        $cancelAmount = $this->connector->total->getAvailableCancelAmount($orderId);

        /** @var UrlGeneratorInterface $router */
        $router = $this->get('router');

        /** @var ActionsBarButtonsCollection $bar */
        $bar = $params['actions_bar_buttons_collection'];

        // Refund
        if ($this->connector->coreLibrary->canRefund($data->getOrderId(), $data->getPayId(), $refundAmount)) {
            $bar->add(
                new ActionsBarButton(
                    'btn-action btn-ing-refund',
                    [
                        'href' => $router->generate('ingenico_refund', [
                            'orderId'=> (int)$order->id
                        ]),
                    ],
                    $this->trans('order.action.refund_btn', [], 'messages')
                )
            );
        }

        // Capture
        if ($this->connector->coreLibrary->canCapture($data->getOrderId(), $data->getPayId(), $captureAmount)) {
            $bar->add(
                new ActionsBarButton(
                    'btn-action btn-ing-capture',
                    [
                        'href' => $router->generate('ingenico_capture', [
                            'orderId'=> (int)$order->id
                        ]),
                    ],
                    $this->trans('order.action.capture', [], 'messages')
                )
            );
        }

        // Cancel
        if ($this->connector->coreLibrary->canVoid($data->getOrderId(), $data->getPayId(), $cancelAmount)) {
            $bar->add(
                new ActionsBarButton(
                    'btn-action btn-ing-cancel',
                    [
                        'href' => $router->generate('ingenico_cancel', [
                            'orderId'=> (int)$order->id
                        ]),
                    ],
                    $this->trans('order.action.cancel', [], 'messages')
                )
            );
        }
    }

    /**
     * Hook: actionCronJob
     */
    public function hookActionCronJob()
    {
        $signature = $this->connector->coreLibrary->getConfiguration()->getPassphrase();

        // Trigger Cron Controller
        $cronJobUrl = $this->getControllerUrl('cron', [
            'token' => Tools::substr($signature, -5)
        ]);

        Tools::file_get_contents($cronJobUrl);
    }


    public function canUseAliases()
    {
        return $this->connector->coreLibrary->getConfiguration()->getSettingsOneclick();
        // additional check $this->canUseDirectLink();
    }

    /**
     * Hook: displayCustomerAccount
     */
    public function hookDisplayCustomerAccount() {
        if (!$this->canUseAliases()) {
            return '';
        }

        $this->context->smarty->assign('alias_page_link', $this->getControllerUrl('aliases'));
        echo $this->display(__FILE__, 'views/templates/hook/my-account-tab.tpl');
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
        $selectedPaymentMethods = $this->connector->coreLibrary->getSelectedPaymentMethods();
        $ccMethods = [];

        /** @var \IngenicoClient\PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($selectedPaymentMethods as $paymentMethod) {
            switch ($paymentMethod->getId()) {
                case \IngenicoClient\PaymentMethod\Amex::CODE:
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

                    $actionUrl = $this->context->link->getModuleLink(
                        $this->name,
                        'open_invoice',
                        [
                            'payment_id' => $paymentMethod->getId(),
                            'pm' => $paymentMethod->getPM(),
                            'brand' => $paymentMethod->getBrand()
                        ],
                        true
                    );

                    // Render the form
                    $this->smarty->assign(
                        [
                            'action' => $actionUrl,
                            'payment_id' => $paymentMethod->getId(),
                        ]
                    );

                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($this->trans($paymentMethod->getName(), [], 'messages'))
                        ->setLogo($paymentMethod->getEmbeddedLogo())
                        ->setAction($actionUrl)
                        ->setForm(
                            $this->fetch('module:' . $this->name . '/views/templates/front/payment-method.tpl')
                        );

                        $paymentOptions[] = $paymentOption;
                    break;
                case \IngenicoClient\PaymentMethod\FacilyPay3x::CODE:
                case \IngenicoClient\PaymentMethod\FacilyPay3xnf::CODE:
                case \IngenicoClient\PaymentMethod\FacilyPay4x::CODE:
                case \IngenicoClient\PaymentMethod\FacilyPay4xnf::CODE:
                    $actionUrl = $this->context->link->getModuleLink(
                        $this->name,
                        'open_invoice',
                        [
                            'payment_id' => $paymentMethod->getId(),
                            'pm' => $paymentMethod->getPM(),
                            'brand' => $paymentMethod->getBrand()
                        ],
                        true
                    );

                    // Render the form
                    $this->smarty->assign(
                        [
                            'action' => $actionUrl,
                            'payment_id' => $paymentMethod->getId(),
                        ]
                    );

                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($this->trans($paymentMethod->getName(), [], 'messages'))
                        ->setLogo($paymentMethod->getEmbeddedLogo())
                        ->setAction($actionUrl)
                        ->setForm(
                            $this->fetch('module:' . $this->name . '/views/templates/front/payment-method.tpl')
                        );

                    $paymentOptions[] = $paymentOption;
                    break;
                case \IngenicoClient\PaymentMethod\Ingenico::CODE:
                    // Add Generic method
                    $actionUrl = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        [],
                        true
                    );

                    // Render the form
                    $this->smarty->assign(
                        [
                            'action' => $actionUrl,
                            'payment_id' => $paymentMethod->getId(),
                        ]
                    );

                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($this->trans(
                            'Pay with %name%',
                            [
                                '%name%' => $this->connector->coreLibrary->getWhiteLabelsData()->getPlatform()
                            ],
                            'messages'
                        ))
                        ->setAdditionalInformation(
                            $this->trans(
                                'Pay safely on the next page with %name% using your preferred payment method',
                                [
                                    '%name%' => $this->connector->coreLibrary->getWhiteLabelsData()->getPlatform()
                                ],
                                'messages'
                            )
                        )

                        ->setAction($actionUrl)
                        ->setForm(
                            $this->fetch('module:' . $this->name . '/views/templates/front/payment-method.tpl')
                        );

                    // Set logo
                    if ($this->connector->getPlatformEnvironment() === IngenicoCoreLibrary::PLATFORM_INGENICO) {
                        $paymentOption->setLogo($this->getPathUri() . '/views/img/ingenico.gif');
                    } else {
                        $paymentOption->setLogo($this->getPathUri() . '/logo.png');
                    }

                    $paymentOptions[] = $paymentOption;
                    break;
                case \IngenicoClient\PaymentMethod\CarteBancaire::CODE:
                    $customerId = (int) Context::getContext()->customer->id;
                    $paymentPageType = $this->connector->coreLibrary->getConfiguration()->getPaymentpageType();
                    $oneClickPayments = $this->connector->coreLibrary->getConfiguration()->getSettingsOneclick();

                    // Load aliases
                    $aliases = [];
                    if ($oneClickPayments && $customerId > 0) {
                        $aliases = $this->connector->coreLibrary->getCustomerAliases($customerId);
                        foreach ($aliases as $id => $alias) {
                            if ($alias->getBrand() !== 'CB') {
                                unset($aliases[$id]);
                                continue;
                            }

                            $alias->setTranslatedName(
                                $this->trans('%brand% ends with %cardno%, expires on %month%/%year%', [
                                    '%brand%' => 'Carte Bancaire',
                                    '%cardno%' => substr($alias->getCardno(),-4,4),
                                    '%month%' => substr($alias->getEd(), 0, 2),
                                    '%year%' => substr($alias->getEd(), 2, 4),
                                ], 'messages')
                            );
                        }
                    }

                    $actionUrl = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        [
                            'payment_id' => $paymentMethod->getId(),
                            'pm' => $paymentMethod->getPM(),
                            'brand' => $paymentMethod->getBrand()
                        ],
                        true
                    );

                    // Render the form
                    $this->smarty->assign(
                        [
                            'action' => $actionUrl,
                            'payment_id' => $paymentMethod->getId(),
                            'one_click_payment' => $oneClickPayments,
                            'aliases' => $aliases,
                            'payment_page_type' => $paymentPageType,
                            'frame_url' => $paymentPageType === 'INLINE' ? $this->connector->coreLibrary->getCcIFrameUrlBeforePlaceOrder(
                                'cartId' . Context::getContext()->cart->id
                            ) : null
                        ]
                    );

                    // Render the form
                    $form = $this->fetch('module:' . $this->name . '/views/templates/hook/cb-form.tpl');

                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($this->trans(
                            $paymentMethod->getName(),
                            [],
                            'messages'
                        ))
                        ->setLogo($paymentMethod->getEmbeddedLogo())
                        ->setAction($actionUrl)
                        ->setForm($form);

                    $paymentOptions[] = $paymentOption;

                    break;

                default:
                    $actionUrl = $this->context->link->getModuleLink(
                        $this->name,
                        'payment',
                        [
                            'payment_id' => $paymentMethod->getId(),
                            'pm' => $paymentMethod->getPM(),
                            'brand' => $paymentMethod->getBrand()
                        ],
                        true
                    );

                    // Render the form
                    $this->smarty->assign(
                        [
                            'action' => $actionUrl,
                            'payment_id' => $paymentMethod->getId(),
                        ]
                    );

                    $paymentOption = new PaymentOption();
                    $paymentOption->setModuleName($this->name)
                        ->setCallToActionText($this->trans(
                            $paymentMethod->getName(),
                            [],
                            'messages'
                        ))
                        ->setLogo($paymentMethod->getEmbeddedLogo())
                        ->setAction($actionUrl)
                        ->setForm(
                            $this->fetch('module:' . $this->name . '/views/templates/front/payment-method.tpl')
                        );

                    $paymentOptions[] = $paymentOption;
            }
        }

        // Add CC method
        if (count($ccMethods) > 0) {
            $customerId = (int) Context::getContext()->customer->id;
            $paymentPageType = $this->connector->coreLibrary->getConfiguration()->getPaymentpageType();
            $oneClickPayments = $this->connector->coreLibrary->getConfiguration()->getSettingsOneclick();

            // Load aliases
            $aliases = [];
            if ($oneClickPayments && $customerId > 0) {
                $aliases = $this->connector->coreLibrary->getCustomerAliases($customerId);
                foreach ($aliases as $id => $alias) {
                    // Unset Carte Bancaire cards
                    if ($alias->getBrand() === 'CB') {
                        unset($aliases[$id]);
                        continue;
                    }

                    $alias->setTranslatedName(
                        $this->trans('%brand% ends with %cardno%, expires on %month%/%year%', [
                            '%brand%' => $alias->getBrand(),
                            '%cardno%' => substr($alias->getCardno(),-4,4),
                            '%month%' => substr($alias->getEd(), 0, 2),
                            '%year%' => substr($alias->getEd(), 2, 4),
                        ], 'messages')
                    );
                }
            }

            $actionUrl = $this->context->link->getModuleLink(
                $this->name,
                'payment',
                [
                    'payment_id' => 'visa',
                    'pm' => 'CreditCard',
                    'brand' => 'CreditCard'
                ],
                true
            );

            // Render the form
            $this->smarty->assign(
                [
                    'action' => $actionUrl,
                    'payment_id' => $paymentMethod->getId(),
                    'one_click_payment' => $oneClickPayments,
                    'aliases' => $aliases,
                    'payment_page_type' => $paymentPageType,
                    'frame_url' => $paymentPageType === 'INLINE' ? $this->connector->coreLibrary->getCcIFrameUrlBeforePlaceOrder(
                        'cartId' . Context::getContext()->cart->id
                    ) : null
                ]
            );

            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Credit Cards', [], 'messages'))
                ->setLogo($this->getPathUri() . '/views/img/card-logo-unknown.svg')
                ->setAction($actionUrl)
                ->setForm(
                    $this->fetch('module:' . $this->name . '/views/templates/hook/cc-form.tpl')
                );

            $paymentOptions[] = $paymentOption;
        }

        // Add Blank payment methods
        $flex_methods = Utils::getConfig('FLEX_METHODS');
        if (!$flex_methods) {
            $flex_methods = '[]';
        }
        $flex_methods = json_decode($flex_methods, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $flex_methods = [];
        }

        foreach ($flex_methods as $flex_method) {
            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->name)
                ->setCallToActionText($flex_method['title'])
                ->setAction($this->context->link->getModuleLink(
                    $this->name,
                    'payment',
                    [
                        'pm' => $flex_method['pm'],
                        'brand' => $flex_method['brand']
                    ],
                    true
                ));

            if ($flex_method['img']) {
                $paymentOption->setLogo(
                    $this->context->link->getBaseLink() . '/upload/ingenico/' . $flex_method['img']
                );
            }

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
                Configuration::get(Connector::PS_OS_PENDING),
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
            $this->connector->logger->debug($e->getMessage());
            throw $e;
        }

        return false;
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
        $methods = $this->connector->coreLibrary->getAndMergeCountriesPaymentMethods($countries);

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
                'connector' => $this->connector,
                'payment_categories' => $this->connector->getPaymentCategories(),
                'selected_payment_methods' => $methods,
                'module_name' => $this->name,
            ]
        );

        // Render template
        return $this->fetch('module:' . $this->name . '/views/templates/admin/selected-payment-methods.tpl');
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
                'connector' => $this->connector,
                'payment_categories' => $this->connector->getPaymentCategories(),
                'selected_payment_methods' => $methods,
                'module_name' => $this->name,
            ]
        );

        // Render template
        return $this->fetch('module:' . $this->name . '/views/templates/admin/selected-payment-methods.tpl');
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

                $data = $this->connector->coreLibrary->getConfiguration()->export();
                $contents = json_encode($data, JSON_PRETTY_PRINT);
                file_put_contents(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename, $contents);

                // Get Platform
                $platform = $this->connector->requestShoppingCartExtensionId();

                // Prepare subject
                if (!empty($ticket)) {
                    $subject = sprintf('Exported settings related to the ticket nr [%s]', $ticket);
                } else {
                    $subject = sprintf('%s: Issues configuring the site %s', $platform, \Tools::getShopDomain());
                }

                // Send E-mail
                $result = $this->connector->sendSupportEmail(
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
                        $this->connector->coreLibrary->getConfiguration()->import($data);
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
            $data = $this->connector->coreLibrary->getConfiguration()->export();
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
        $payment_countries = $this->connector->getAllCountries();

        // Countries which available to create account
        $create_account_countries = $this->connector->getAllCountries();
        unset(
            $create_account_countries['SE'],
            $create_account_countries['FI'],
            $create_account_countries['DK'],
            $create_account_countries['NO']
        );

        // Blank payment methods
        $flex_methods = Utils::getConfig('FLEX_METHODS');
        if (!$flex_methods) {
            $flex_methods = '[]';
        }
        json_decode($flex_methods);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $flex_methods = '[]';
        }

        // Assign Smarty values
        $this->smarty->assign(
            array_merge(
                $this->connector->requestSettings($this->connector->requestSettingsMode()),
                [
                    'connector' => $this->connector,
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
                    'payment_methods' => $this->connector->coreLibrary->getPaymentMethods(),
                    'payment_categories' => $this->connector->getPaymentCategories(),
                    'payment_countries' => $payment_countries,
                    'create_account_countries' => $create_account_countries,
                    'ingenico_ajax_url' => $this->getControllerUrl('ajax'),
                    'template_dir' => dirname(__FILE__) . '/views/templates/',
                    'module_name' => $this->name,
                    'account_creation_lang' => $lang,
                    'admin_email' => \Context::getContext()->employee->email,

                    // WhiteLabels
                    'logo_url' => $this->connector->coreLibrary->getWhiteLabelsData()->getLogoUrl(),
                    'ticket_placeholder' => $this->connector->coreLibrary->getWhiteLabelsData()->getSupportTicketPlaceholder(),
                    'template_guid_ecom' => $this->connector->coreLibrary->getWhiteLabelsData()->getTemplateGuidEcom(),
                    'template_guid_flex' => $this->connector->coreLibrary->getWhiteLabelsData()->getTemplateGuidFlex(),
                    'template_guid_paypal' => $this->connector->coreLibrary->getWhiteLabelsData()->getTemplateGuidPaypal(),

                    // Blank payment methods
                    'flex_methods' => $flex_methods,
                    'uploads_dir' => $this->context->link->getBaseLink() . '/upload/ingenico/'
                ]
            )
        );

        // Render templates
        foreach (['settings-header', 'form'] as $template) {
            $this->form_html .= $this->display(
                __FILE__,
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
            $error = $this->connector->coreLibrary->getConfiguration()->validate($fieldKey, $fieldValue);
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
            $this->connector->coreLibrary->getConfiguration()->setData($fieldKey, $fieldValue);
        }

        // Save configuration
        try {
            $this->connector->coreLibrary->getConfiguration()->save();
        } catch (\Exception $e) {
            // Configuration saving errors here
        }

        // Save additional settings
        if (count($errors) === 0) {
            $suffix = $this->connector->coreLibrary->getConfiguration()->getMode() ? 'live' : 'test';
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
            $this->connector->coreLibrary->getConfiguration()->copyToLive();
            Utils::updateConfig('test_to_live', 1);
        }

        // Mark as installed
        if (!Utils::getConfig('installed') && count($errors) === 0) {
            $this->form_html .= $this->displayConfirmation($this->trans('form.install.success', [], 'messages'));
            Utils::updateConfig('installed', 1);
        }

        // Save mode flag
        Utils::updateConfig('mode', \Tools::getValue('mode'));

        $flex_methods = \Tools::getValue('flex_methods');
        json_decode($flex_methods);
        if (json_last_error() === JSON_ERROR_NONE) {
            Utils::updateConfig('FLEX_METHODS', $flex_methods);
        }
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

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    private function getSessionValue($key)
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
    private function setSessionValue($key, $value)
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
    private function unsetSessionValue($key)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
}

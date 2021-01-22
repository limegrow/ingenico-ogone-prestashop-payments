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
use IngenicoClient\IngenicoCoreLibrary;

class Ingenico_EpaymentsAjaxModuleFrontController extends ModuleFrontController
{
    /** @var Ingenico_epayments */
    public $module;

    public function initContent()
    {
        parent::initContent();
        
        $method = Tools::getValue('method');
        $response = null;
        switch ($method) {
            /** settings page countries filtering */
            case 'filter_countries':
                $query = Tools::getValue('query');
                $selected_countries = Tools::getValue('selected_countries');
                $selected_countries_array = explode('|', $selected_countries);
                $matching_countries = $this->module->filterCountries($query, $selected_countries_array);
                $response = '';
                foreach ($matching_countries as $iso_code => $matching_country) {
                    $response .= '<li>
                                <label class="label-container">
                                    <input type="checkbox" name="payment_country[]" value="' . $iso_code . '">
                                    <span class="checkmark"></span>
                                    ' . $matching_country . '
                                </label>
                            </li>';
                }
                break;
            /** settings page payments filtering */
            case 'filter_payment_methods':
                $query = Tools::getValue('query');
                $matching_methods = $this->module->filterPaymentMethods($query);
                $response = '';
                foreach ($matching_methods as $key => $matching_method) {
                    $response .= '<li>
                                <label class="label-container">
                                    <input type="checkbox" name="payment_methods[]" value="' . $matching_method->getId() . '">
                                    <span class="checkmark"></span>
                                    ' . $matching_method->getName() . '
                                </label>
                            </li>';
                }
                break;
            case 'fetch_payment_methods':
                // Get HTML template of payment methods
                $payment_methods = (array) Tools::getValue('payment_methods');
                $response = $this->module->fetchPaymentMethodsTemplate($payment_methods);
                break;
            case 'payment_method_modal':
                $selected = (array) Tools::getValue('selected');

                // Get methods that unselected
                $payment_methods = [];
                $methods = $this->module->coreLibrary->getPaymentMethods();
                foreach ($methods as $method) {
                    if (!in_array($method->getId(), $selected)) {
                        $payment_methods[] = $method;
                    }
                }

                $response = $this->module->getPaymentMethodsModal($payment_methods);
                break;
            case 'payment_status':
                $orderId = Tools::getValue('ORDERID');
                $payId = Tools::getValue('PAYID', null);
                $paymentResponse = $this->module->coreLibrary->getPaymentInfo($orderId, $payId);
                if (!$paymentResponse->isSuccessful()) {
                    $message = $this->trans('Payment Status is failed. Error: %code% %details%', [
                        '%code%' => $paymentResponse->getParam('NCERROR'),
                        '%details%' => $paymentResponse->getParam('NCERRORPLUS')
                    ], 'Modules.IngenicoPayments.Shop');

                    throw new Exception($message, $paymentResponse->getParam('NCERROR'));
                }
                $response = [
                    'redirect' => $this->module->getSuccessPageUrl($paymentResponse->getParam('ORDERID'))
                ];

                break;
            case 'register_account':
                //$email = Tools::getValue('account_info');
                parse_str(Tools::getValue('account_info'), $account_info);
                $response = $this->module->submitOnboardingRequest(
                    $account_info['company_name'],
                    $account_info['account_email'],
                    $account_info['business_country']
                );
                break;
            case 'add_countries':
                $countries = (array) Tools::getValue('countries');
                $response = $this->module->fetchPaymentMethodsByCountryTemplate($countries);
                break;
            case 'fetch_methods_by_countries':
                $countries = (array) Tools::getValue('countries');
                $openinvoice = Tools::getValue('openinvoice');
                $response = $this->module->fetchPaymentMethodsByCountryTemplate($countries, $openinvoice);
                break;
            case 'charge_payment':
                $response = $this->module->finishReturnInline();
                if (isset($response['message'])) {
                    Utils::setSessionValue('ingenico_message', $response['message']);
                }

                // Add "Warning page"
                if (isset($response['is_show_warning']) && $response['is_show_warning']) {
                    // Assign data for Smarty
                    $this->context->smarty->assign([
                        'suffix' => _PS_MODE_DEV_ ? '' : '.min',
                        'path' => $this->module->getPath(true),
                        'payment_status' => $response['payment_status'],
                        'success_page' => $response['redirect'],
                        'is_show_warning' => true
                    ]);

                    // Render template
                    $html = $this->module->fetch(dirname(__FILE__) . '/../../views/templates/front/success.tpl');

                    $response['status'] = 'show_warning';
                    $response['html'] = $html;
                }
                break;
            case 'set_merchant_country':
                $country = Tools::getValue('country');

                try {
                    $this->module->setGenericCountry($country);
                    $response['status'] = 'success';
                } catch (Exception $e) {
                    $response['status'] = 'failure';
                }

                break;
            default:
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

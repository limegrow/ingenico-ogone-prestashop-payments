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
                    // Assign data for Smarty
                    $this->context->smarty->assign([
                        'iso_code' => $iso_code,
                        'country' => $matching_country,
                    ]);

                    // Render template
                    $response .= $this->module->fetch(
                        dirname(__FILE__) . '/../../views/templates/admin/ajax/payment_country.tpl'
                    );
                }
                break;
            /** settings page payments filtering */
            case 'filter_payment_methods':
                $query = Tools::getValue('query');
                $matching_methods = $this->module->filterPaymentMethods($query);
                $response = '';
                foreach ($matching_methods as $key => $matching_method) {
                    // Assign data for Smarty
                    $this->context->smarty->assign([
                        'payment_id' => $matching_method->getId(),
                        'payment_name' => $matching_method->getName(),
                    ]);

                    // Render template
                    $response .= $this->module->fetch(
                        dirname(__FILE__) . '/../../views/templates/admin/ajax/payment_methods.tpl'
                    );
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
            case 'flex_upload':
                // Upload files
                $files = $_FILES;
                $image = null;

                if (count($files) > 0) {
                    $ext_img = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg'];
                    $mime_img = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'image/svg', 'image/svg+xml'];
                    $target_dir = _PS_UPLOAD_DIR_ . '/ingenico/';
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir);
                    }

                    $errors = [];
                    foreach ($files as $file) {
                        if (file_exists($file['tmp_name'][0]) &&
                            is_uploaded_file($file['tmp_name'][0])
                        ) {
                            $target_file = $target_dir . basename($file['name'][0]);
                            $file_ext = \Tools::strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                            if (!in_array($file_ext, $ext_img)) {
                                $errors[] = $this->trans('Invalid file extension', [], 'messages');
                                unset($file['tmp_name']);
                                continue;
                            }

                            $mime = mime_content_type($file['tmp_name'][0]);
                            if (!in_array($mime, $mime_img)) {
                                $errors[] = $this->trans('Invalid file mime type', [], 'messages');
                                unset($file['tmp_name']);
                                continue;
                            }

                            // Upload file
                            if (!move_uploaded_file($file['tmp_name'][0], $target_file)) { //NOSONAR
                                throw new \Exception($this->trans('exceptions.upload_filed', [], 'messages'));
                            }

                            $image = basename($target_file);
                        }
                    }

                    if (count($errors) > 0) {
                        $image = null;
                    }
                }

                $response = [
                    'title' => $_POST['title'],
                    'pm' => $_POST['pm'],
                    'brand' => $_POST['brand'],
                    'img' => $image
                ];
                break;
            default:
                break;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

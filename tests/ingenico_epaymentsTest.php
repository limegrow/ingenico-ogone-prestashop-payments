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

use PHPUnit\Framework\TestCase;

class ingenico_epaymentsTest extends TestCase
{
    public function testReturnUrls()
    {
        $module = Module::getInstanceByName('ingenico_epayments');
        $return_urls = $module->getOgoneReturnUrls();

        foreach ($return_urls as $return_url) {
            $ch = curl_init($return_url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $this->assertEquals(200, $httpcode);
        }
    }

    public function testReturnAllSettings()
    {
        $module = Module::getInstanceByName('ingenico_epayments');
        $settings = $module->getOgoneSettings();

        $expexedSettings = ['pspid_test', 'signature_test', 'user_test',
            'password_test', 'request_timeout_test', 'pspid_live', 'signature_live',
            'user_live', 'password_live', 'request_timeout_live', 'order_freeze_days',
            'payment_reminder_days', 'fraud_notifications_email', 'direct_sale_email',
            'payment_page_type', 'template_src', 'template_name', 'template_url',
            'installments_rules', 'installments_amount', 'installments_period',
            'installments_first', 'installments_minimal', 'installments_amount_min',
            'installments_amount_max', 'installments_period_min', 'installments_period_max',
            'installments_first_min', 'installments_first_max', 'mode',
            'tokenisation', 'one_click_payment', 'skip_confirmation', 'secure',
            'direct_sale', 'order_freeze', 'payment_reminder', 'fraud_notifications',
            'direct_sale_email_option', 'enable_installment', 'selected_payment_methods'
        ];

        foreach ($expexedSettings as $expexedSetting) {
            $this->assertArrayHasKey($expexedSetting, $settings);
        }
    }

    public function testPayloadArray()
    {
        $module = Module::getInstanceByName('ingenico_epayments');
        $payloadArray = $module->getOgonePaymentPayload(1);
        $expexedPayloadArray = ['amount', 'currency', 'orderId'];

        foreach ($expexedPayloadArray as $row) {
            $this->assertArrayHasKey($row, $payloadArray);
        }
    }

    public function testPayloadAmountIsInt()
    {
        $module = Module::getInstanceByName('ingenico_epayments');
        $payloadArray = $module->getOgonePaymentPayload(1);
        $this->assertEquals(true, is_int($payloadArray['amount']));
    }

    public function testOperations()
    {
        $orderId = 1;
        $payId = 1;

        /** @var Ingenico_epayments $module */
        $module = Module::getInstanceByName('ingenico_epayments');

        $e = null;
        try {
            $module->void($orderId, $payId);
        } catch (Exception $e) {
            // Silence is golden
        }

        $this->assertInstanceOf('Exception', $e);

        $e = null;
        try {
            $module->capture($orderId, $payId);
        } catch (Exception $e) {
            // Silence is golden
        }

        $this->assertInstanceOf('Exception', $e);

        $e = null;
        try {
            $module->refund($orderId, $payId, 100);
        } catch (Exception $e) {
            // Silence is golden
        }

        $this->assertInstanceOf('Exception', $e);
    }
}

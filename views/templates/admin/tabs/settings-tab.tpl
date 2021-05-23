{*
* 2007-2021 Ingenico
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
*  @author Ingenico <contact@ingenico.com>
*  @copyright  2007-2021 Ingenico
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div id="settings" class="tab-pane">
    <h1>{l s='form.settings.title' mod='ingenico_epayments'}</h1>
    <div class="advanced-switch">
        <div class="switch-label">{l s='form.settings.mode.basic' mod='ingenico_epayments'}</div>
        <label class="switch test-live-toggle">
            <input type="checkbox" data-toggle-name="advanced_settings" name="settings_advanced"
                   {if isset($settings_advanced) && $settings_advanced}checked{/if}>
            <span class="slider round"></span>
        </label>
        <div class="switch-label right">{l s='form.settings.mode.advanced' mod='ingenico_epayments'}</div>
    </div>
    <div class="advanced_settings" {if !$settings_advanced}style="display: none"{/if}>
        <h2 class="col-lg-12">{l s='form.settings.label.tokenisation' mod='ingenico_epayments'}<span class="icon-span modal-link" data-modal-id="tokenisation-modal"></span>
        </h2>
        <div class="form-group col-lg-12">
            <label class="switch test-live-toggle">
                <input type="checkbox" name="settings_tokenisation" data-toggle-name="one-click-payment"
                       {if $settings_tokenisation}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">{l s='toggle.enabled' mod='ingenico_epayments'}</div>
        </div>
        <div class="disclaimer">
            <span class="icon-span"></span>
            <p>{l s='form.settings.label.disclaimer' mod='ingenico_epayments'}
                <br>
                <a href="https://www.f-secure.com/en/consulting/our-thinking/pci-compliance-which-saq-is-right-for-me" target="_blank">{l s='form.settings.label.readmore' mod='ingenico_epayments'}</a>
            </p>
        </div>
        <div class="one-click-payment col-lg-12" {if !$settings_tokenisation}style="display: none"{/if}>
            <h3 class="col-lg-12">{l s='form.settings.label.stored_cards' mod='ingenico_epayments'}<span class="icon-span modal-link"
                                                       data-modal-id="one-click-payment-modal"></span></h3>
            <div class="form-group col-lg-12">
                &nbsp;
            </div>
            <div class="form-group col-lg-12">
                <label class="switch test-live-toggle">
                    <input type="checkbox" name="settings_oneclick" {if $settings_oneclick}checked="checked"{/if}>
                    <span class="slider round"></span>

                </label>
                <div class="toggle-label">{l s='toggle.enabled' mod='ingenico_epayments'}</div>
                <span class="icon-span modal-link inline-store-cards" data-modal-id="inline-store-cards-modal" style="{if $paymentpage_type === 'REDIRECT'}display: none;{/if}"></span>
            </div>
            <div class="form-group col-lg-12">
                <label class="switch test-live-toggle">
                    <input type="checkbox" name="settings_skipsecuritycheck"
                           {if $settings_skipsecuritycheck}checked="checked"{/if}>
                    <span class="slider round"></span>
                </label>
                <div class="toggle-label">{l s='form.settings.label.skipsecurity' mod='ingenico_epayments'}</div>
            </div>
            <div class="form-group col-lg-12">
                <p>{l s='form.settings.label.skipsecurity.note' mod='ingenico_epayments'}</p>
            </div>
        </div>
        <h3 class="col-lg-12">
            {l s='form.settings.label.capture' mod='ingenico_epayments'}
            <span class="icon-span modal-link" data-modal-id="delayed-payment-capture-modal"></span>
        </h3>
        <div class="form-group col-lg-12">
            <label class="switch test-live-toggle">
                <input data-toggle-name="direct-sale-email-option" data-toggle-reverse="1" type="checkbox" name="settings_directsales"
                       {if $settings_directsales}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">{l s='form.settings.label.directsales' mod='ingenico_epayments'}</div>
        </div>
        <div class="form-group col-lg-12 direct-sale-email-option" {if $settings_directsales}style="display: none"{/if}>
            <label class="switch test-live-toggle">
                <input type="checkbox" name="direct_sale_email_option" data-toggle-name="direct_sale_email"
                       {if $direct_sale_email_option}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">{l s='form.settings.label.sendemail' mod='ingenico_epayments'}</div>
        </div>
        <div class="form-group direct_sale_email direct-sale-email-option col-lg-12"
             {if !$settings_directsales && $direct_sale_email_option}style="display: block" {else}style="display: none"{/if}>
            <input class="form-control" type="email" size="5" name="direct_sale_email"
                   placeholder="{l s='form.settings.email' mod='ingenico_epayments'}"
                   value="{$direct_sale_email|escape}">
        </div>

        <h3 class="col-lg-12">
            {l s='Notifications' mod='ingenico_epayments'}
        </h3>

        {* notification_order_paid
        notification_order_paid_email *}
        <div class="form-group col-lg-12">
            <label class="switch test-live-toggle">
                <input type="checkbox" name="notification_order_paid" data-toggle-name="notification-order-paid-email-option"
                       {if $notification_order_paid}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">
                {l s='Send an e-mail when order has been paid or reverted from cancelled state.' mod='ingenico_epayments'}
            </div>
        </div>
        <div class="form-group notification-order-paid-email-option col-lg-12"
             {if $notification_order_paid}style="display: block" {else}style="display: none"{/if}>
            <input class="form-control" type="email" size="5" name="notification_order_paid_email"
                   placeholder="{l s='form.settings.email' mod='ingenico_epayments'}"
                   value="{$notification_order_paid_email|escape}">
        </div>

        <div style="clear: both">&nbsp;</div>

        <div class="form-group col-lg-12">
            <label class="switch test-live-toggle">
                <input type="checkbox" name="notification_refund_failed" data-toggle-name="notification-refund-failed-email-option"
                       {if $notification_refund_failed}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">
                {l s='Send an e-mail when refund has been failed.' mod='ingenico_epayments'}
            </div>
        </div>
        <div class="form-group notification-refund-failed-email-option col-lg-12"
             {if $notification_refund_failed}style="display: block" {else}style="display: none"{/if}>
            <input class="form-control" type="email" size="5" name="notification_refund_failed_email"
                   placeholder="{l s='form.settings.email' mod='ingenico_epayments'}"
                   value="{$notification_refund_failed_email|escape}">
        </div>


    </div>
    <h2 class="col-lg-12">{l s='form.settings.label.orders' mod='ingenico_epayments'}</h2>
    {* #IE-268 Freeze order and Fraud notification temporarily removed
    <div class="advanced_settings" {if !$settings_advanced}style="display: none"{/if}>
        <h3 class="col-lg-12">Order freeze<span class="icon-span modal-link" data-modal-id="order-freeze-modal"></span>
        </h3>
        <div class="form-group col-lg-12">
            <label class="switch test-live-toggle">
                <input type="checkbox" name="settings_orderfreeze" data-toggle-name="order-freeze-days"
                       {if $settings_orderfreeze}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">Keep an order if it is not paid immediately</div>
        </div>
        <div class="order-freeze-days col-lg-12" {if !$settings_orderfreeze}style="display: none;"{/if}>
            <p>
                How long would you like to keep your order in a pending state if payment is not immediately confirmed?
            </p>
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="form-control" type="number" size="5" name="settings_orderfreeze_days"
                           value="{$settings_orderfreeze_days|escape}">
                    <span class="suffix">days</span>
                </div>
            </div>
        </div>
    </div>
    *}
    <h3 class="col-lg-12">
        {l s='form.settings.label.payment_reminder' mod='ingenico_epayments'}
        <span class="icon-span modal-link" data-modal-id="payment-reminder-modal"></span>
    </h3>
    <p>
        {l s='form.settings.label.payment_reminder_description' mod='ingenico_epayments'}
    </p>
    <div class="form-group col-lg-12">
        <label class="switch test-live-toggle">
            <input type="checkbox" name="settings_reminderemail" data-toggle-name="payment-reminder-days"
                   {if $settings_reminderemail}checked="checked"{/if}>
            <span class="slider round"></span>
        </label>
        <div class="toggle-label">{l s='form.settings.label.payment_reminder_send' mod='ingenico_epayments'}</div>
    </div>
    <div class="payment-reminder-days col-lg-12" {if !$settings_reminderemail}style="display: none"{/if}>
        <div class="form-group">
            <div class="col-lg-12">
                <input class="form-control" type="number" size="5" name="settings_reminderemail_days"
                       value="{$settings_reminderemail_days|escape}">
                <span class="suffix">{l s='form.settings.label.delay_in_days' mod='ingenico_epayments'}</span>
            </div>
        </div>
    </div>
    {* #IE-268 Fraud notification temporarily removed
    <div class="advanced_settings" {if !$settings_advanced}style="display: none"{/if}>
        <h3 class="col-lg-12">Fraud notifications?<span class="icon-span modal-link"
                                                        data-modal-id="fraud-notifications-modal"></span></h3>
        <div class="form-group col-lg-12">
            <label class="switch test-live-toggle">
                <input type="checkbox" name="fraud_notifications" data-toggle-name="fraud_notifications_email"
                       {if $fraud_notifications}checked="checked"{/if}>
                <span class="slider round"></span>
            </label>
            <div class="toggle-label">Send an e-mail for any fraud suspect</div>
        </div>
        <div class="form-group fraud_notifications_email col-lg-12"
             {if !$fraud_notifications}style="display: none"{/if}>
            <input class="form-control" type="email" size="5" name="fraud_notifications_email"
                   value="{$fraud_notifications_email|escape}">
        </div>
    </div>
    *}
</div>
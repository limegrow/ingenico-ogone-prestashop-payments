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
<div class="form-horizontal">
    <div class="panel ingenico-settings {if isset($installation) && $installation}installed {else}install {/if}{if isset($connection_mode) && $connection_mode}live{else}test{/if}" id="configuration_fieldset_settings">
        {include file="$template_dir/hook/migration.tpl"}
        {include file="$template_dir/hook/create-account.tpl"}
        <form action="{$action|escape}" id="configuration_form" method="post" enctype="multipart/form-data"  class="form-wrapper settings-form">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#connection" data-toggle="tab">{l s='tab.connection' mod='ingenico_epayments'}</a>
                </li>
                <li>
                    <a href="#settings" data-toggle="tab">{l s='tab.settings' mod='ingenico_epayments'}</a>
                </li>
                <li>
                    <a href="#payment_methods" data-toggle="tab">{l s='tab.payment_methods' mod='ingenico_epayments'}</a>
                </li>
                <li>
                    <a href="#payment_page" data-toggle="tab">{l s='tab.payment_page' mod='ingenico_epayments'}</a>
                </li>
                {* #IE-268 temporarily removed Instalments & Subscriptions tab
                <li>
                    <a href="#installments" data-toggle="tab">{l s='tab.installments' mod='ingenico_epayments'}</a>
                </li>
                <li>
                    <a href="#subscriptions" data-toggle="tab">{l s='tab.subscriptions' mod='ingenico_epayments'}</a>
                </li>
                *}
                <li>
                    <a href="#support" data-toggle="tab">{l s='tab.support' mod='ingenico_epayments'}</a>
                </li>
            </ul>
            <div class="tab-content panel">
                {include file="$template_dir/admin/tabs/connection-tab.tpl"}
                {include file="$template_dir/admin/tabs/settings-tab.tpl"}
                {include file="$template_dir/admin/tabs/payment-methods-tab.tpl"}
                {include file="$template_dir/admin/tabs/payment-page-tab.tpl"}
                {include file="$template_dir/admin/tabs/instalments-tab.tpl"}
                {include file="$template_dir/admin/tabs/support-tab.tpl"}
                <input type="hidden" name="connection_mode" value="{if isset($connection_mode) && $connection_mode}on{else}off{/if}">
            </div>
            <div class="panel-footer">
                <button type="submit" class="save-settings" name="submit{$module_name|escape}">{l s='button.save' mod='ingenico_epayments'}</button>
            </div>
        </form>
        {include file="$template_dir/admin/modals/credentials-live-validation-modal.tpl"}
        {include file="$template_dir/admin/modals/tokenisation-modal.tpl"}
        {include file="$template_dir/admin/modals/inline-store-cards-modal.tpl"}
        {include file="$template_dir/admin/modals/payment-reminder-modal.tpl"}
        {include file="$template_dir/admin/modals/fraud-notifications-modal.tpl"}
        {include file="$template_dir/admin/modals/signature-value-modal.tpl"}
        {include file="$template_dir/admin/modals/direct-link-modal.tpl"}
        {include file="$template_dir/admin/modals/direct-link-modal-2.tpl"}
        {include file="$template_dir/admin/modals/delayed-payment-capture-modal.tpl"}
        {include file="$template_dir/admin/modals/pspid-modal.tpl"}
        {include file="$template_dir/admin/modals/pspid-modal-test.tpl"}
        {include file="$template_dir/admin/modals/webhook-modal.tpl"}
        {include file="$template_dir/admin/modals/one-click-payment-modal.tpl"}
        {include file="$template_dir/admin/modals/order-freeze-modal.tpl"}
        {include file="$template_dir/admin/modals/payment-page-modal.tpl"}
        {include file="$template_dir/admin/modals/optional-customisation-modal.tpl"}
        {include file="$template_dir/admin/modals/template-manager-redirect-modal.tpl"}
        {include file="$template_dir/admin/modals/template-manager-inline-modal.tpl"}
        {include file="$template_dir/admin/modals/countries-list.tpl"}
        {include file="$template_dir/admin/modals/payment-methods-list.tpl"}
        {include file="$template_dir/admin/modals/payment-methods-modal.tpl"}
        {include file="$template_dir/admin/modals/direct-link-user-modal.tpl"}
        {include file="$template_dir/admin/modals/instalments-modal.tpl"}
        {include file="$template_dir/admin/modals/upload-confirmation.tpl"}
        {if $connection_mode}
            {include file="$template_dir/admin/modals/paypal-modal.tpl"}
        {else}
            {include file="$template_dir/admin/modals/paypal-modal-test.tpl"}
        {/if}
        {include file="$template_dir/admin/modals/openinvoice-choose-country.tpl"}
        {include file="$template_dir/admin/modals/openinvoice-select-payment.tpl"}
    </div>
</div>


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
{extends file='page.tpl'}
{block name="page_content"}
    <div class="ingenico-confirmation">
    {if $type == 'REDIRECT'}
        <div class="ingenico-loader">
            <img src="{$module_dir|escape}/views/img/loader.svg" alt="{l s='checkout.please_wait' mod='ingenico_epayments'}">
        </div>
        <form id="ingenico-hosted-checkout-form" method="post" action="{$url|escape}" accept-charset="utf-8" style="display: none">
            {foreach $fields as $key => $value}
                <input type="hidden" name="{$key|escape}" value="{$value|escape}">
            {/foreach}
        </form>
        <script>
            // Submit form
            window.addEventListener("DOMContentLoaded", function () {
                document.getElementById("ingenico-hosted-checkout-form").submit();
            });
        </script>
    {elseif $type == 'INLINE'}
        <ul>
            {foreach $categories as $category => $category_name}
                {assign var="array_methods" value=[]}
                {foreach $methods as $key => $payment_method}
                    {if $payment_method->getCategory() === $category}
                        {$array_methods[]=$payment_method}
                    {/if}
                {/foreach}

                {if count($array_methods) > 0}
                    {if $category === 'card'}
                        <h2>{$category_name|escape}</h2>
                        <li>
                            <div class="payment-title cc-logo">
                                {foreach $array_methods as $key => $payment_method}
                                    <img src="{$payment_method->getEmbeddedLogo()|escape}" alt="{$payment_method->getName()|escape}">
                                {/foreach}
                            </div>

                            <iframe class="payment-content" data-src="{$credit_card_url|escape}" lazyload="on"></iframe>
                        </li>
                    {else}
                        <!-- <h2>{$category_name|escape}</h2> -->
                        {foreach $array_methods as $key => $payment_method}
                            <li>
                                {if (!$payment_method->isRedirectOnly())}
                                    <div class="payment-title payment-logo">
                                        <img src="{$payment_method->getEmbeddedLogo()|escape}" width="50">
                                        <h3>{$payment_method->getName()|escape}</h3>
                                    </div>
                                    <iframe class="payment-content" data-src="{$payment_method->getIFrameUrl()|escape}" lazyload="on"></iframe>
                                {else}
                                    {if $payment_method->getAdditionalDataRequired()}
                                        <div class="payment-title payment-logo">
                                            <img src="{$payment_method->getEmbeddedLogo() nofilter}" width="50" alt="{$payment_method->getName()|escape}">
                                            <h3>{$payment_method->getName()|escape}</h3>
                                        </div>
                                        <div class="payment-content" style="display: block;">
                                            <h4 class="container">{l s='modal.openinvoice.additional_data_required' mod='ingenico_epayments'}</h4>
                                            <form action="{$open_invoice_url|escape}" method="post" class="col-md-11">
                                                <input type="hidden" name="payment_id" value="{$payment_method->getId()|escape}" />
                                                <input type="hidden" name="pm" value="{$payment_method->getPM()|escape}" />
                                                <input type="hidden" name="brand" value="{$payment_method->getBrand()|escape}" />

                                                {foreach $payment_method->getMissingFields() as $field}
                                                <div class="form-group row">
                                                    <label for="{$field->getFieldName()|escape}" class="col-sm-3 col-form-label">{$field->getLabel()|escape}</label>
                                                    <div class="col-sm-9">
                                                        {if ($field->getFieldType() === 'radio')}
                                                            {foreach from=$field->getValues() key=key item=value name=foo}
                                                                <div class="custom-control1 custom-radio1">
                                                                    <input type="radio"
                                                                           id="{$field->getFieldName()|escape}_{$key|escape}"
                                                                           name="{$field->getFieldName()|escape}"
                                                                           value="{$key|escape}"
                                                                           class="custom-control-input1" {if $smarty.foreach.foo.first} checked {/if}>
                                                                    <label class="custom-control-label1" for="{$field->getFieldName()|escape}_{$key|escape}">{$value|escape}</label>
                                                                </div>
                                                            {/foreach}
                                                        {else}
                                                        <input class="form-control"

                                                               id="{$field->getFieldName()|escape}"
                                                               name="{$field->getFieldName()|escape}"
                                                                {if ($field->getFieldType() === 'date')}
                                                                    type="text"
                                                                    {if (empty($field->getValue()))}
                                                                        value="{date('d-m-Y')}"
                                                                    {else}
                                                                        value="{date('d-m-Y', $field->getValue())}"
                                                                    {/if}
                                                                {else}
                                                                    type="{$field->getFieldType()|escape}"
                                                                    value="{$field->getValue()|escape}"
                                                                {/if}

                                                                {if ($field->getLength())}
                                                                    pattern="{literal}.{0,{/literal}{$field->getLength()|escape}}"
                                                                    maxlength="{$field->getLength()|escape}"
                                                                {/if}

                                                                {if ($field->getRequired())}
                                                                    required
                                                                {/if}

                                                                {if ($field->getRequired())}
                                                                    required
                                                                {/if}
                                                        />
                                                        {/if}
                                                        {if ($field->getFieldType() === 'date')}
                                                            <script type="application/javascript" defer>
                                                                var xhr = window.setInterval(function () {
                                                                    if (typeof jQuery !== 'undefined') {
                                                                        window.clearInterval(xhr);

                                                                        $("#{$field->getFieldName()|escape}").datepicker({
                                                                            changeMonth: true,
                                                                            changeYear: true,
                                                                            showButtonPanel: true,
                                                                            dateFormat: 'dd-mm-yy',
                                                                            yearRange: "-90:+0",
                                                                        });
                                                                    }
                                                                }, 500)

                                                            </script>
                                                        {/if}
                                                        <div class="invalid-feedback1">
                                                            {$field->getValidationMessage()|escape}
                                                        </div>
                                                    </div>
                                                </div>
                                                {/foreach}

                                                <div class="row justify-content-center">
                                                    <button type="submit" class="btn btn-primary">{l s='modal.openinvoice.proceed' mod='ingenico_epayments'}</button>
                                                </div>
                                                <div class="row">
                                                    &nbsp;
                                                </div>
                                            </form>
                                        </div>
                                    {else}
                                        <a href="{$payment_method->getIFrameUrl()}">
                                            <div class="payment-title payment-logo">
                                                <img src="{$payment_method->getEmbeddedLogo() nofilter}" width="50">
                                                <h3>{$payment_method->getName()|escape}</h3>
                                            </div>
                                        </a>
                                    {/if}
                                {/if}
                            </li>
                        {/foreach}
                    {/if}
                {/if}
            {/foreach}
        </ul>
    {/if}
    </div>
{/block}

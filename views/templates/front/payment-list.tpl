{*
* 2007-2019 Ingenico
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
*  @copyright  2007-2019 Ingenico
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{extends file='page.tpl'}
{block name="page_content"}
    <div class="ingenico-confirmation">
    {if $type == 'REDIRECT'}
        <div class="ingenico-loader">
            <img src="{$module_dir}/views/imgs/loader.svg" alt="{l s='checkout.please_wait' mod='ingenico_epayments'}">
        </div>
        <form id="ingenico-hosted-checkout-form" method="post" action="{$url}" accept-charset="utf-8" style="display: none">
            {foreach $fields as $key => $value}
                <input type="hidden" name="{$key}" value="{$value}">
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
                        <h2>{$category_name}</h2>
                        <li>
                            <div class="payment-title cc-logo">
                                {foreach $array_methods as $key => $payment_method}
                                    <img src="{$payment_method->getEmbeddedLogo()}" alt="{$payment_method->getName()}">
                                {/foreach}
                            </div>

                            <iframe class="payment-content" data-src="{$credit_card_url}" lazyload="on"></iframe>
                        </li>
                    {else}
                        <h2>{$category_name}</h2>
                        {foreach $array_methods as $key => $payment_method}
                            <li>
                                {if (!$payment_method->isRedirectOnly())}
                                    <div class="payment-title payment-logo">
                                        <img src="{$payment_method->getEmbeddedLogo()}" width="50">
                                        <h3>{$payment_method->getName()}</h3>
                                    </div>
                                    <iframe class="payment-content" data-src="{$payment_method->getIFrameUrl()}" lazyload="on"></iframe>
                                {else}
                                    {if $payment_method->getAdditionalDataRequired()}
                                        <div class="payment-title payment-logo">
                                            <img src="{$payment_method->getEmbeddedLogo()}" width="50" alt="{$payment_method->getName()}">
                                            <h3>{$payment_method->getName()}</h3>
                                        </div>
                                        <div class="payment-content" style="display: block;">
                                            <h4 class="container">{l s='modal.openinvoice.additional_data_required' mod='ingenico_epayments'}</h4>
                                            <form action="{$open_invoice_url}" method="post" class="col-md-11">
                                                <input type="hidden" name="payment_id" value="{$payment_method->getId()}" />
                                                <input type="hidden" name="pm" value="{$payment_method->getPM()}" />
                                                <input type="hidden" name="brand" value="{$payment_method->getBrand()}" />

                                                {foreach $payment_method->getMissingFields() as $field}
                                                <div class="form-group row">
                                                    <label for="{$field->getFieldName()}" class="col-sm-3 col-form-label">{$field->getLabel()}</label>
                                                    <div class="col-sm-9">
                                                        {if ($field->getFieldType() === 'radio')}
                                                            {foreach from=$field->getValues() key=key item=value name=foo}
                                                                <div class="custom-control1 custom-radio1">
                                                                    <input type="radio"
                                                                           id="{$field->getFieldName()}_{$key}"
                                                                           name="{$field->getFieldName()}"
                                                                           value="{$key}"
                                                                           class="custom-control-input1" {if $smarty.foreach.foo.first} checked {/if}>
                                                                    <label class="custom-control-label1" for="{$field->getFieldName()}_{$key}">{$value}</label>
                                                                </div>
                                                            {/foreach}
                                                        {else}
                                                        <input class="form-control"
                                                               type="{$field->getFieldType()}"
                                                               id="{$field->getFieldName()}"
                                                               name="{$field->getFieldName()}"
                                                                {if ($field->getFieldType() === 'date')}
                                                                    data-inv-value="{$field->getValue()}"
                                                                    value="{date('Y-m-d', strtotime('-20 years'))}"
                                                                {else}
                                                                    value="{$field->getValue()}"
                                                                {/if}

                                                                {if ($field->getLength())}
                                                                    pattern="{literal}.{0,{/literal}{$field->getLength()}}"
                                                                    maxlength="{$field->getLength()}"
                                                                {/if}

                                                                {if ($field->getRequired())}
                                                                    required
                                                                {/if}

                                                                {if ($field->getRequired())}
                                                                    required
                                                                {/if}
                                                        />
                                                        {/if}
                                                        <div class="invalid-feedback1">
                                                            {$field->getValidationMessage()}
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
                                                <img src="{$payment_method->getEmbeddedLogo()}" width="50">
                                                <h3>{$payment_method->getName()}</h3>
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

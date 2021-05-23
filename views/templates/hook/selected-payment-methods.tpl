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
<h2 class="col-lg-12">{l s='form.payment_methods.title' mod='ingenico_epayments'}<span class="icon-span modal-link" data-modal-id="payment-methods-modal"></span></h2>
<select id="pm_list" name="selected_payment_methods[]" multiple style="display: none">
    {foreach $selected_payment_methods as $selected_payment_method}
        <option value="{$selected_payment_method|escape}" selected>{$selected_payment_method|escape}</option>
    {/foreach}
</select>
{foreach $payment_categories as $category => $category_name}
    {$payment_methods=$module->getPaymentMethodsByCategory($category)}
    {foreach $selected_payment_methods as $selected_payment_method}
        {if isset($payment_methods[$selected_payment_method])}
            <h3 class="col-lg-12">{$category_name|escape}</h3>
            {break}
        {/if}
    {/foreach}
    <ul>
    {foreach $selected_payment_methods as $selected_payment_method}
        {if isset($payment_methods[$selected_payment_method])}
            {$payment_method=$payment_methods[$selected_payment_method]}
            <li class="{$payment_method->getId()|escape}">
                <img src="{$payment_method->getEmbeddedLogo() nofilter}" width="50">
                <label>
                    {if $payment_method->getId() === 'klarna'}
                        Klarna (deprecated)
                    {else}
                        {$payment_method->getName()|escape}
                    {/if}
                </label>
                <div class="payment-method-settings">
                    {* IE-177
                    <a>
                        <i class="material-icons">mode_edit</i><span>Setup</span>
                    </a> *}
                    <a onclick="Ingenico.removePaymentMethod('{$payment_method->getId()|escape}')">
                        <i class="material-icons">delete</i><span>{l s='form.payment_methods.button.delete' mod='ingenico_epayments'}</span>
                    </a>
                </div>
                <div class="countries">
                    {$countries=$payment_method->getCountries()}
                    {assign var="array_countries" value=[]}
                    {foreach $countries as $country => $popularities}
                        {$array_countries[]=$module->getCountryByCode($country)}
                    {/foreach}
                    {assign var='string' value=', '|implode:$array_countries}
                    {$string|truncate:60:"...":true}
                </div>
            </li>
            {if $payment_method->getId() ==='pay_pal'}
                <li id="paypal-warn">
                    <span class="icon-span modal-link" data-modal-id="paypal-modal" style=""></span>
                </li>
            {/if}
        {/if}
    {/foreach}
    </ul>
{/foreach}
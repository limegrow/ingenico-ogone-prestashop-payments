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
<div id="payment_methods" class="tab-pane">
    <h1>{l s='form.payment_methods.title' mod='ingenico_epayments'}</h1>
    <h2 class="col-lg-12">{l s='form.payment_methods.label.geography' mod='ingenico_epayments'}</h2>
    <p class="selected_countries">
        {l s='form.payment_methods.label.choose_countries' mod='ingenico_epayments'}
    </p>
    <p class="col-lg-12">
        <button type="button" class="ingenico-btn modal-link" data-modal-id="countries-list">{l s='form.payment_methods.button.fetch' mod='ingenico_epayments'}</button>
    </p>
    <div id="selected_payment_methods">
        {include file="$template_dir/hook/selected-payment-methods.tpl"}
    </div>
    <p class="col-lg-12">
        <button type="button" class="ingenico-btn modal-link" data-modal-id="payment-methods-list">{l s='form.payment_methods.button.add' mod='ingenico_epayments'}</button>
    </p>
    <input type="hidden" name="generic_country" value="{$generic_country}">
</div>
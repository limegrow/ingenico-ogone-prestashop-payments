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
{if (isset($payment_methods) && $payment_methods)}
    <div class="modal-body col-lg-12">
        <div class="col-lg-12">
            <input class="form-control payment-methods-list" type="text" name="payment_method" placeholder="{l s='form.payment_methods.label.type_search' mod='ingenico_epayments'}">
        </div>
        <div class="col-lg-12">
            <ul class="methods-list">
                {foreach $payment_methods as $key => $method}
                    <li>
                        <label class="label-container">
                            <input type="checkbox" name="payment_methods[]" value="{$method->getId()}">
                            <span class="checkmark"></span>
                            {if $method->getId() === 'klarna'}
                                Klarna (deprecated)
                            {else}
                                {$method->getName()}
                            {/if}
                        </label>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="apply" name="submit_save_payments" data-dismiss="modal" aria-label="Close">
            {l s='button.apply' mod='ingenico_epayments'}
        </button>
    </div>
{else}
    <div class="modal-body col-lg-12">
        <h3 class="text-center">{l s='form.payment_methods.label.no_available' mod='ingenico_epayments'}</h3>
    </div>
    <div class="modal-footer">
        <button type="submit" class="close" data-dismiss="modal" aria-label="Close">
            {l s='button.close' mod='ingenico_epayments'}
        </button>
    </div>
{/if}
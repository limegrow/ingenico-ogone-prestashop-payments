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
<div id="countries-list" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">
                    {l s='modal.countries_list.select' mod='ingenico_epayments'}
                </h3>
            </div>
            <div class="modal-body col-lg-12">
                <div class="col-lg-12">
                    <input class="form-control country-list" type="text" name="country_name" placeholder="{l s='modal.countries_list.placeholder' mod='ingenico_epayments'}">
                </div>
                <div class="col-lg-12">
                    <ul>
                        {foreach $payment_countries as $key => $country}
                            <li>
                                <label class="label-container">
                                    <input type="checkbox" name="payment_country[]" value="{$key|escape}">
                                    <span class="checkmark"></span>
                                    {$country|escape}
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="apply" name="submit_save_countries" data-dismiss="modal" aria-label="Apply">
                    {l s='button.apply' mod='ingenico_epayments'}
                </button>
            </div>
        </div>
    </div>
</div>

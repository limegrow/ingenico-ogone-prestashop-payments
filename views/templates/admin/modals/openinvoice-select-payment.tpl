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
<div id="openinvoice-select-payment-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">
                    {l s='modal.openinvoice.payments.title' mod='ingenico_epayments'}
                </h3>
            </div>
            <div class="modal-body">
                <ul>
                    <li id="openinvoice_selected1" style="display: none;">
                        {l s='modal.openinvoice.payments.label1' mod='ingenico_epayments'}
                    </li>
                    <li id="openinvoice_selected2">
                        {l s='modal.openinvoice.payments.label1' mod='ingenico_epayments'}
                    </li>
                    <li>{l s='modal.openinvoice.payments.label2' mod='ingenico_epayments'}</li>
                    <li>{l s='modal.openinvoice.payments.label3' mod='ingenico_epayments'}</li>
                </ul>
                <div class="methods-list" style="display: inline-block;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <p class="radio">
                                <input type="radio" id="openinvoice_afterpay" name="openinvoice_method" value="afterpay" checked="checked">
                                <label for="openinvoice_afterpay">
                                    {l s='modal.openinvoice.payments.afterpay' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio">
                                <input type="radio" id="openinvoice_klarna" name="openinvoice_method" value="klarna">
                                <label for="openinvoice_klarna">
                                    {l s='modal.openinvoice.payments.klarna' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio">
                                <input type="radio" id="openinvoice_another" name="openinvoice_method" value="">
                                <label for="openinvoice_another">
                                    {l s='modal.openinvoice.another' mod='ingenico_epayments'}
                                </label>
                            </p>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="apply" data-dismiss="modal" aria-label="Apply">
                    {l s='button.apply' mod='ingenico_epayments'}
                </button>
            </div>
        </div>
    </div>
</div>

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
<div id="paypal-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">
                    {l s='modal.paypal.howto' mod='ingenico_epayments'}
                </h3>
            </div>
            <div class="modal-body">
                <ul>
                    <li>{l s='modal.paypal.label1' mod='ingenico_epayments'}</li>
                    <br>
                    <li>{l s='modal.paypal.label2' mod='ingenico_epayments'}:<br>
                        {l s='modal.paypal.label3' mod='ingenico_epayments'}
                    </li>
                </ul>
                <img src="{$module_dir|escape}views/img/paypal.png">
                <ul>
                    <li>{l s='modal.paypal.label4' mod='ingenico_epayments'}</a></li>
                </ul>
                <img src="{$module_dir|escape}views/img/paypal2.png">
                <ul>
                    <li>{l s='modal.paypal.label5' mod='ingenico_epayments'}</a></li>
                </ul>
                <div class="info-box red-info-box">
                    {l s='modal.paypal.label6' mod='ingenico_epayments' tags=['<br>']}
                </div>
                <div class="info-box green-info-box">
                    {l s='modal.paypal.label7' mod='ingenico_epayments'}
                    <a target="_blank" href="{$template_guid_paypal nofilter}">
                        {l s='modal.paypal.label8' mod='ingenico_epayments'}
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    {l s='button.close' mod='ingenico_epayments'}
                </button>
            </div>
        </div>
    </div>
</div>

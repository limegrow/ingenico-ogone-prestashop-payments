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
<div id="direct-link-user-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">
                    {l s='modal.directlink.label14' mod='ingenico_epayments'}
                </h3>
            </div>
            <div class="modal-body">
                <ul>
                    <li>{l s='modal.directlink.label15' mod='ingenico_epayments'}</li>
                    <br>
                    <li>{l s='modal.directlink.label16' mod='ingenico_epayments'}</li>
                    <li>{l s='modal.directlink.label17' mod='ingenico_epayments'}</li>
                    <li>{l s='modal.directlink.label18' mod='ingenico_epayments'}</li>
                    <li>{l s='modal.directlink.label19' mod='ingenico_epayments'}</li>
                    <br>
                    <li>{l s='modal.directlink.label20' mod='ingenico_epayments'}</li>
                </ul>
                <img src="{$path|escape}/views/img/directlink-user.png">
            </div>
            <div class="modal-footer">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    {l s='button.close' mod='ingenico_epayments'}
                </button>
            </div>
        </div>
    </div>
</div>

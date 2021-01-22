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
<div id="upload-confirmation-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">
                    {l s='form.support.upload_confirmation.title' mod='ingenico_epayments'}
                </h3>
            </div>
            <div class="modal-body">
                <ul>
                    <li>{l s='form.support.upload_confirmation' mod='ingenico_epayments'}</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button id="submitModalImportSettings" type="button" class="close" data-dismiss="modal" aria-label="Import">
                    {l s='form.support.import' mod='ingenico_epayments'}
                </button>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-right: 10px;">
                    {l s='button.close' mod='ingenico_epayments'}
                </button>
            </div>
        </div>
    </div>
</div>

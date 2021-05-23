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
<div id="support" class="tab-pane">
    <h1>{l s='form.support.title' mod='ingenico_epayments'}</h1>

    <h2 class="col-lg-12">{l s='form.support.assistance' mod='ingenico_epayments'}</h2>

    <p>
        {l s='form.support.hint' mod='ingenico_epayments' tags=['<strong>']}
    </p>

    <div class="form-group col-lg-12">
        <label class="col-lg-12" for="support_ticket">
            {l s='form.support.label1' mod='ingenico_epayments'}
        </label>
        <input class="form-control" type="text" id="support_ticket" name="support_ticket" placeholder="{$ticket_placeholder|escape:'htmlall':'UTF-8'}">
    </div>

    <div class="form-group col-lg-12">
        <label class="col-lg-12" for="support_email">
            {l s='form.support.label2' mod='ingenico_epayments'}
        </label>
        <input class="form-control" type="text" id="support_email" name="support_email" placeholder="{l s='form.support.label4' mod='ingenico_epayments'}" value="{$admin_email|escape}">
    </div>

    <div class="form-group col-lg-12">
        <label class="col-lg-12" for="support_description">
            {l s='form.support.label3' mod='ingenico_epayments'}
        </label>
        <textarea class="form-control" id="support_description" name="support_description" rows="7"></textarea>
    </div>

    <div style="clear: both">&nbsp;</div>

    <div class="disclaimer">
        <span class="icon-span"></span>
        <p>
            {l s='form.support.disclaimer1' mod='ingenico_epayments'}
        </p>
    </div>

    <div style="clear: both">&nbsp;</div>
    <hr class="grey-bar" />

    <div style="clear: both">&nbsp;</div>
    <button type="submit" class="save-settings" id="submitSupportRequest" name="submitSupportRequest">
        {l s='form.support.submit' mod='ingenico_epayments'}
    </button>

    <div style="clear: both">&nbsp;</div>

    <h2 class="col-lg-12">{l s='form.support.export_import' mod='ingenico_epayments'}</h2>

    <div class="import-export">
        <p>
            <strong>
                {l s='form.support.export_settings' mod='ingenico_epayments'}
            </strong>
            <br>

            <span class="cloud">&nbsp;</span>
            <button type="submit" id="submitExportSettings" name="submitExportSettings" style="display: none"></button>
            <a id="export-settings" href="#">
                {l s='form.support.download_settings' mod='ingenico_epayments'}
            </a>
            <br><br>

            <strong>
                {l s='form.support.import_settings' mod='ingenico_epayments'}
            </strong>
            <br>

            {l s='form.support.import_hint' mod='ingenico_epayments'}
        </p>

        <label class="upload-label" for="support-import">
            {l s='form.support.browse_file' mod='ingenico_epayments'}
        </label>
        <input id="support-import" name="support-import" type="file" style="display: none">
    </div>

    <div style="clear: both">&nbsp;</div>

    <div class="disclaimer">
        <span class="icon-span"></span>
        <p>
            {l s='form.support.disclaimer2' mod='ingenico_epayments'}
        </p>
    </div>

    <div style="clear: both">&nbsp;</div>

    <button type="button" class="save-settings" id="modalImportSettings">
        {l s='form.support.import' mod='ingenico_epayments'}
    </button>

    <button type="submit" class="save-settings" id="submitImportSettings" name="submitImportSettings" style="display: none;">
        {l s='form.support.import' mod='ingenico_epayments'}
    </button>
</div>

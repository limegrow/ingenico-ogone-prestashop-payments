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
<div id="payment_page" class="tab-pane">
    <h1>{l s='form.payment_page.title' mod='ingenico_epayments'}</h1>
    <h2 class="col-lg-12">{l s='form.payment_page.label.choose_access' mod='ingenico_epayments'}<span class="icon-span modal-link" data-modal-id="payment-page-modal"></span></h2>
    <div class="form-group">
        <div class="col-lg-12">
            <p class="radio">
                <input type="radio" data-toggle-name="paymentpage_type" name="paymentpage_type" id="paymentpage_type_inline" value="INLINE" {if $paymentpage_type === 'INLINE'}checked="checked"{/if}>
                <label for="paymentpage_type_inline">
                    {l s='form.payment_page.label.inline' mod='ingenico_epayments'}
                </label>
            </p>
            <p class="radio">
                <input type="radio" data-toggle-name="paymentpage_type" name="paymentpage_type" id="paymentpage_type_redirect" value="REDIRECT" {if $paymentpage_type === 'REDIRECT'}checked="checked"{/if}>
                <label for="paymentpage_type_redirect">
                    {l s='form.payment_page.label.redirect' mod='ingenico_epayments'}
                </label>
            </p>
        </div>
    </div>
    <div class="disclaimer radio-toggle paymentpage_typeINLINE paymentpage_type" {if $paymentpage_type === 'REDIRECT'}style="display: none"{/if}>
        <span class="icon-span"></span>
        <p>{l s='form.payment_page.label.disclaimer' mod='ingenico_epayments'}</p>
    </div>
    <h2 class="col-lg-12">{l s='form.payment_page.label.template' mod='ingenico_epayments'}<span class="icon-span modal-link" data-modal-id="optional-customisation-modal"></span></h2>
    <h3 class="col-lg-12">{l s='form.payment_page.label.step1' mod='ingenico_epayments'}</h3>
    <div class="step1 redirect" {if $paymentpage_type === 'INLINE'}style="display: none"{/if}>
        <a href="{$template_guid_ecom}" target="_blank">
            {l s='form.payment_page.label.readmore' mod='ingenico_epayments'}
        </a>
    </div>
    <div class="step1 inline" {if $paymentpage_type === 'REDIRECT'}style="display: none"{/if}>
        <a href="{$template_guid_flex}" target="_blank">
            {l s='form.payment_page.label.readmore' mod='ingenico_epayments'}
        </a>
    </div>
    <h3 class="col-lg-12">{l s='form.payment_page.label.step2' mod='ingenico_epayments'}</h3>
    <p><strong>{l s='form.payment_page.label.create_own' mod='ingenico_epayments'}</strong></p>
    <h3 class="col-lg-12">{l s='form.payment_page.label.step3' mod='ingenico_epayments'}</h3>

    <!-- <div class="radio-toggle paymentpage_typeREDIRECT paymentpage_type" {if $paymentpage_type === 'INLINE'}style="display: none"{/if}>
        <p><strong>{l s='form.payment_page.label.point_your' mod='ingenico_epayments'}</strong></p>
        <div class="form-group">
            <div class="col-lg-12">
                <p class="radio">
                    <input type="radio" name="paymentpage_template" data-toggle-name="paymentpage_template" id="template_manager" value="INGENICO" {if $paymentpage_template === 'INGENICO'}checked="checked"{/if}>
                    <label for="template_manager">
                        {l s='form.payment_page.label.ingenico' mod='ingenico_epayments'}
                    </label>
                </p>
                <p class="radio">
                    <input type="radio" name="paymentpage_template" data-toggle-name="paymentpage_template" id="template_upload" value="STORE" {if $paymentpage_template === 'STORE'}checked="checked"{/if}>
                    <label for="template_upload">
                        {l s='form.payment_page.label.upload' mod='ingenico_epayments'}
                    </label>
                </p>
                <p class="radio">
                    <input type="radio" name="paymentpage_template" data-toggle-name="paymentpage_template" id="template_external" value="EXTERNAL" {if $paymentpage_template === 'EXTERNAL'}checked="checked"{/if}>
                    <label for="template_external">
                        {l s='form.payment_page.label.external' mod='ingenico_epayments'}
                    </label>
                </p>
            </div>
        </div>
    </div> -->

    {if $connection_mode}
        {assign var="template_manager_url" value="https://secure.ogone.com/Ncol/Prod/BackOffice/template/filemanager?CSRFSP=%2fncol%2ftest%2fbackoffice%2ftemplate%2fdefaulttemplate&CSRFKEY=A793B1DE11B27BD4701093635DAB6345A36EB999&CSRFTS=20181204071717&branding=OGONE&MenuId=43"}
    {else}
        {assign var="template_manager_url" value="https://secure.ogone.com/Ncol/Test/BackOffice/template/filemanager?CSRFSP=%2fncol%2ftest%2fbackoffice%2ftemplate%2fdefaulttemplate&CSRFKEY=A793B1DE11B27BD4701093635DAB6345A36EB999&CSRFTS=20181204071717&branding=OGONE&MenuId=43"}
    {/if}

    <div class="radio-toggle paymentpage_templateINGENICO paymentpage_template" {if $paymentpage_template != 'INGENICO'}style="display: none"{/if}>
        <p>
            {l s='form.payment_page.label.upload_template' mod='ingenico_epayments'}
            <a target="_blank" href="{$template_manager_url}">
                {l s='form.payment_page.label.template_manager' mod='ingenico_epayments'}
            </a>

            <span class="ppt inline icon-span modal-link" data-modal-id="template-manager-inline-modal" {if $paymentpage_type !== 'INLINE'}style="display: none"{/if}></span>
            <span class="ppt redirect icon-span modal-link" data-modal-id="template-manager-redirect-modal" {if $paymentpage_type !== 'REDIRECT'}style="display: none"{/if}></span>
        </p>
        <div class="col-lg-12">
            <input class="form-control" placeholder="{l s='form.payment_page.label.template_name' mod='ingenico_epayments'}" type="text" name="paymentpage_template_name" value="{$paymentpage_template_name}" {if $paymentpage_type === 'INLINE'}style="display: none"{/if}>
        </div>
    </div>

    <div class="radio-toggle paymentpage_templateSTORE paymentpage_template" {if $paymentpage_template != 'STORE'}style="display: none"{/if}>
        <div class="col-lg-12">
            <label class="upload-label" for="upload-template">
                {if isset($paymentpage_template_localfilename) && $paymentpage_template_localfilename}{$paymentpage_template_localfilename}{else}{l s='form.payment_page.label.browse' mod='ingenico_epayments'}{/if}
            </label>
            <input name="paymentpage_template_localfilename" type="file" id="upload-template" class="file-upload-field" value="">
            <button type="submit" class="upload-template" name="submitingenico_epayments">{l s='form.payment_page.button.upload' mod='ingenico_epayments'}</button>
        </div>
    </div>

    <div class="radio-toggle paymentpage_templateEXTERNAL paymentpage_template" {if $paymentpage_template != 'EXTERNAL'}style="display: none"{/if}>
        <div class="col-lg-12">
            <input class="form-control" placeholder="{l s='form.payment_page.label.file_url' mod='ingenico_epayments'}" type="text" name="paymentpage_template_externalurl" value="{$paymentpage_template_externalurl}">
        </div>
    </div>

    <!-- <div class="disclaimer radio-toggle paymentpage_templateEXTERNAL paymentpage_templateSTORE paymentpage_template">
        <span class="icon-span"></span>
        <p>{l s='form.payment_page.label.pci' mod='ingenico_epayments'}</p>
    </div> -->
</div>
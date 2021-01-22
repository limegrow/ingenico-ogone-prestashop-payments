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
<div class="account-creation-progress">
    <div class="progress-half"></div>
</div>
<form id="create-account-frm" action="{$action}" method="post" class="form-wrapper installation-view installation-form">
    <div class="col-lg-12 installation-options">
        <h1 class="col-lg-12">{l s='form.create_account.title' mod='ingenico_epayments'}</h1>
        <div id="create-account" class="installation-button" onclick="Ingenico.createAccount()">{l s='form.create_account.button.create_account' mod='ingenico_epayments'}</div>
        <div id="account-exist" class="installation-button" onclick="Ingenico.connectExistingAccount()">{l s='form.create_account.button.account_exist' mod='ingenico_epayments'}</div>
    </div>
    <div class="create-account-form">
        <div class="tab-content panel">
            <h1>{l s='form.create_account.label.create_account' mod='ingenico_epayments'}</h1>
            <div class="form-group col-lg-6">
                <label>
                    {l s='form.create_account.label.company_name' mod='ingenico_epayments'} *
                </label>
                <input class="form-control" type="text" size="5" name="company_name" required>
            </div>
            <div class="form-group col-lg-6 country-select">
                <label>
                    {l s='form.create_account.label.business_country' mod='ingenico_epayments'} *
                </label>
                <select name="business_country" required>
                    <option value="">{l s='form.create_account.label.business_country' mod='ingenico_epayments'}</option>
                    {foreach $create_account_countries as $iso_code => $country}
                        <option value="{$iso_code}">{$country}</option>

                    {/foreach}
                </select>
            </div>
            <div class="form-group col-lg-6">
                <label>
                    {l s='form.create_account.label.email' mod='ingenico_epayments'} *
                </label>
                <input class="form-control" type="email" size="5" name="account_email" required>
            </div>
            <div class="form-group">
                <label class="checkbox-container">
                    <input id="agreement" type="checkbox" required>
                    <span class="checkmark"></span>
                </label>
                <p class="account-gdpr">
                    {l s='form.create_account.label.checkmark' mod='ingenico_epayments'}
                </p>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="ingenico-button" onclick="Ingenico.backToInstallation(event)">{l s='button.back' mod='ingenico_epayments'}</button>
            <button id="registration-btn" type="submit" class="ingenico-button pull-right" name="submit_account" onclick="Ingenico.registerAccount(event)">{l s='button.submit' mod='ingenico_epayments'}</button>
        </div>
    </div>
</form>
<div class="registration-complete">
    <img class="account-creation-done" src="{$module_dir}/views/imgs/ic_done.svg">
    <p>{l s='form.create_account.label.done.label1' mod='ingenico_epayments'}</p>
    <p>{l s='form.create_account.label.done.label2' mod='ingenico_epayments'}</p>
    <p>{l s='form.create_account.label.done.label3' mod='ingenico_epayments'}</p>

    <button type="submit" class="ingenico-button connect-existing" onclick="Ingenico.connectExistingAccount()">{l s='form.create_account.button.connect-existing' mod='ingenico_epayments'}</button>
</div>
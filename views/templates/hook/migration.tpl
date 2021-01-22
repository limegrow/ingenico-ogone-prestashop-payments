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
<script>
    window.migration_ajax_url = '{$migration_ajax_url}';
</script>
<form id="migration-frm" action="{$action}" method="post" class="form-wrapper" style="display: none">
    <div class="col-lg-12 installation-options">
        <h1 class="col-lg-12">{l s='form.create_account.title' mod='ingenico_epayments'}</h1>
        <div id="create-account" class="installation-button" onclick="Ingenico.createAccount()">{l s='form.create_account.button.create_account' mod='ingenico_epayments'}</div>
        <div id="account-exist" class="installation-button" onclick="Ingenico.connectExistingAccount()">{l s='form.create_account.button.account_exist' mod='ingenico_epayments'}</div>
    </div>
    <div class="migration-form" style="width: 500px;">
        <div class="tab-content panel">
            <div class="form-group">
                <textarea class="form-control" id="migration-logs" style="height: 300px;" readonly></textarea>
                <div style="clear: both">&nbsp;</div>

                <div class="progress">
                    <div id="aliases_progress" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                    </div>
                </div>
                <div style="clear: both">&nbsp;</div>

                <button name="doMigrate" type="button" class="btn btn-primary" onclick="Ingenico.doMigrate()">Migrate from old plugin</button>
            </div>

        </div>
    </div>
</form>
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
<script>
    ingenico_ajax_url = '{$ingenico_ajax_url}';
    installments_amount_min = "{$instalments_flex_instalments_min}";
    installments_amount_max = "{$instalments_flex_instalments_max}";
    installments_period_min = "{$instalments_flex_period_min}";
    installments_period_max = "{$instalments_flex_period_max}";
    installments_first_min = "{$instalments_flex_firstpayment_min}";
    installments_first_max = "{$instalments_flex_firstpayment_max}";

    window.addEventListener("DOMContentLoaded", function () {
        // Startup page
        if (window.location.hash === '') {
            {if $installed}
                // Show configuration
                Ingenico.connectExistingAccount();
            {else}
                // Show Create or connect your account
                Ingenico.backToInstallation();
            {/if}
        }
    });
</script>
<div class="panel ingenico-header  {if $installed}installed {else}install {/if}{if isset($connection_mode) && $connection_mode}live{else}test{/if}">
    <div class="row">
        <div class="col-md-2">
            <a target="_blank" href="{$logo_url}">
                <img src="{$path|escape:'htmlall':'UTF-8'}views/imgs/logo.png"/>
            </a>
        </div>
        <div class="page-bar toolbarBox">
            <div class="btn-toolbar">
                <ul id="toolbar-nav" class="nav nav-pills pull-right collapse navbar-collapse">
                    <li class="header-links" {if !isset($installation) || !$installation}style="display: none" {/if}>
                        {if $is_migration_available}
                        <a class="create-account run-migration" onclick="Ingenico.showMigrationUI()" href="#">Migrate</a>
                        {/if}
                        <a class="create-account creation" onclick="Ingenico.createAccount()" title="{l s='form.header.create_account' mod='ingenico_epayments'}">
                            {l s='form.header.create_account' mod='ingenico_epayments'}
                        </a>
                        <a class="create-account configuration" onclick="Ingenico.connectExistingAccount()"
                           title="{l s='form.header.configuration' mod='ingenico_epayments'}">
                            {l s='form.header.configuration' mod='ingenico_epayments'}
                        </a>
                    </li>
                    <li class="header-switch">
                        <div class="test-live-toggle-test">{l s='form.header.test' mod='ingenico_epayments'}</div>
                        <label class="switch test-live-toggle">
                            <input type="checkbox" {if isset($connection_mode) && $connection_mode}checked{/if}>
                            <span class="slider round"></span>
                        </label>
                        <div class="test-live-toggle-live">{l s='form.header.live' mod='ingenico_epayments'}</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

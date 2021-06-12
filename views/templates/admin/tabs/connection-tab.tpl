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
<div id="connection" class="tab-pane active">
    <h1>{l s='form.connection.title' mod='ingenico_epayments'}</h1>

    <div class="settings-group test-api">
        <div class="form-group test_settings" data-tab-id="account">
            <div class="account-header col-lg-12">
                <h2>{l s='form.connection.label.test_account' mod='ingenico_epayments'}</h2>
                <div class="collapse-btn" data-toggle="collapse" data-target="#connection-test" >
                    <span class="show-collapse">{l s='collapse.show' mod='ingenico_epayments'}<i class="arrow right"></i></span>
                    <span class="hide-collapse">{l s='collapse.hide' mod='ingenico_epayments'}<i class="arrow up"></i></span>
                </div>
            </div>
        </div>

        <div id="connection-test" class="collapse">
            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.pspid' mod='ingenico_epayments'}
                </label>
                <input class="form-control " type="text" size="5" name="connection_test_pspid" value="{$connection_test_pspid|escape}" {if !$connection_mode}required{/if}>
                <a href="#" class="modal-link" data-modal-id="pspid-modal-test">{l s='form.connection.label.where' mod='ingenico_epayments'}</a>
            </div>
            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.signature' mod='ingenico_epayments'}
                </label>
                <input class="form-control" type="password" size="5" name="connection_test_signature" value="{$connection_test_signature|escape}" {if !$connection_mode}required{/if}>
                <span toggle="connection_test_signature" class="form-btn mask"
                      data-show="{l s='form.connection.button.show' mod='ingenico_epayments'}"
                      data-hide="{l s='form.connection.button.hide' mod='ingenico_epayments'}">
                    {l s='form.connection.button.show' mod='ingenico_epayments'}
                </span>
                <span class="form-btn generate" onclick="Ingenico.generateHash('connection_test_signature', 40)">{l s='form.connection.button.generate' mod='ingenico_epayments'}</span>
                <div class="copy-response" data-copy="test-signature">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyValue('connection_test_signature', 'test-signature')">{l s='form.connection.button.copy_value' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="signature-value-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>
            <div class="webhook">
                <h3 class="col-lg-12">{l s='form.connection.label.webhook_settings' mod='ingenico_epayments'}</h3>
                {if $connection_test_webhook == null}
                    <input name="connection_test_webhook" type="hidden" value="{$webhook_url|escape}">
                    <p class="col-lg-12">
                        <a class="webhook-url" onclick="Ogone.copyLink('{$webhook_url nofilter}', 'test-webhook')">{$webhook_url|escape}</a>
                    </p>
                {else}
                  <input name="connection_test_webhook" type="hidden" value="{$connection_test_webhook|escape}">
                  <p class="col-lg-12">
                        <a class="webhook-url" onclick="Ogone.copyLink('{$connection_test_webhook nofilter}', 'test-webhook')">{$connection_test_webhook|escape}</a>
                    </p>
                {/if}
                <div class="copy-response" data-copy="test-webhook">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyLink('{$webhook_url nofilter}', 'test-webhook')">{l s='form.connection.button.copy_link' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="webhook-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>

            <h3 class="col-lg-12">{l s='form.connection.label.directlink.directlink' mod='ingenico_epayments'}<span class="icon-span modal-link" data-modal-id="direct-link-modal"></span></h3>
            <div class="disclaimer">
                <span class="icon-span"></span>
                <p>{l s='form.connection.label.directlink.label4' mod='ingenico_epayments'}
                    <br>
                    <a href="https://www.mwrinfosecurity.com/our-thinking/pci-compliance-which-saq-is-right-for-me" target="_blank">{l s='form.settings.label.readmore' mod='ingenico_epayments'}</a>
                </p>
            </div>
            <div id="direct-link" class="tooltiptext" style="display: none">
                <button type="button" class="close" onclick="Ingenico.toggleTooltip('direct-link')">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4>{l s='form.connection.label.directlink.label1' mod='ingenico_epayments'}</h4>
                <p>{l s='form.connection.label.directlink.label2' mod='ingenico_epayments'}<br>
                    {l s='form.connection.label.directlink.label3' mod='ingenico_epayments'}</p>
            </div>

            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.user' mod='ingenico_epayments'}
                </label>
                <input class="form-control " type="text" size="5" name="connection_test_dl_user" value="{$connection_test_dl_user|escape}" {if !$connection_mode}required{/if}>
                <a href="#" class="modal-link" data-modal-id="direct-link-user-modal">{l s='form.connection.label.where' mod='ingenico_epayments'}</a>
                <div class="copy-response" data-copy="test-directlink-user">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyValue('connection_test_dl_user', 'test-directlink-user')">{l s='form.connection.button.copy_value' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="direct-link-user-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.password' mod='ingenico_epayments'}
                </label>
                <input class="form-control" type="password" size="5" name="connection_test_dl_password" value="{$connection_test_dl_password|escape}" {if !$connection_mode}required{/if}>
                <span toggle="connection_test_dl_password" class="form-btn mask"
                      data-show="{l s='form.connection.button.show' mod='ingenico_epayments'}"
                      data-hide="{l s='form.connection.button.hide' mod='ingenico_epayments'}">
                    {l s='form.connection.button.show' mod='ingenico_epayments'}
                </span>
                <span class="form-btn generate" onclick="Ingenico.generateHash('connection_test_dl_password', 40)">{l s='form.connection.button.generate' mod='ingenico_epayments'}</span>
                <div class="copy-response" data-copy="test-directlink-password">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyValue('connection_test_dl_password', 'test-directlink-password')">{l s='form.connection.button.copy_value' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="direct-link-user-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-group live-api">
        <div class="form-group live_settings" data-tab-id="account">
            <div class="account-header col-lg-12">
                <h2>{l s='form.connection.label.live_account' mod='ingenico_epayments'}</h2>
                <div class="collapse-btn" data-toggle="collapse" data-target="#connection-live" >
                    <span class="show-collapse">{l s='collapse.show' mod='ingenico_epayments'}<i class="arrow right"></i></span>
                    <span class="hide-collapse">{l s='collapse.hide' mod='ingenico_epayments'}<i class="arrow up"></i></span>
                </div>
            </div>
        </div>

        <div id="connection-live" class="collapse">
            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.pspid' mod='ingenico_epayments'}
                </label>
                <input class="form-control " type="text" size="5" name="connection_live_pspid" value="{$connection_live_pspid|escape}" {if $connection_mode}required{/if}>
                <a href="#" class="modal-link" data-modal-id="pspid-modal">{l s='form.connection.label.where' mod='ingenico_epayments'}</a>
            </div>
            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.signature' mod='ingenico_epayments'}
                </label>
                <input class="form-control" type="password" size="5" name="connection_live_signature" value="{$connection_live_signature|escape}" {if $connection_mode}required{/if}>
                <span toggle="connection_live_signature" class="form-btn mask"
                      data-show="{l s='form.connection.button.show' mod='ingenico_epayments'}"
                      data-hide="{l s='form.connection.button.hide' mod='ingenico_epayments'}">
                    {l s='form.connection.button.show' mod='ingenico_epayments'}
                </span>
                <span class="form-btn generate" onclick="Ingenico.generateHash('connection_live_signature', 40)">{l s='form.connection.button.generate' mod='ingenico_epayments'}</span>
                <div class="copy-response" data-copy="live-signature">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyValue('connection_live_signature', 'live-signature')">{l s='form.connection.button.copy_value' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="signature-value-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>
            <div class="webhook">
                <h3 class="col-lg-12">{l s='form.connection.label.webhook_settings' mod='ingenico_epayments'}</h3>
                {if $connection_live_webhook == null}
                    <input name="connection_live_webhook" type="hidden" value="{$webhook_url|escape}">
                    <p class="col-lg-12">
                        <a class="webhook-url" onclick="Ogone.copyLink('{$webhook_url nofilter}', 'live-webhook')">{$webhook_url|escape}</a>
                    </p>
                {else}
                  <input name="connection_live_webhook" type="hidden" value="{$connection_live_webhook|escape}">
                  <p class="col-lg-12">
                        <a class="webhook-url" onclick="Ogone.copyLink('{$connection_live_webhook nofilter}', 'live-webhook')">{$connection_live_webhook|escape}</a>
                    </p>
                {/if}
                <div class="copy-response" data-copy="live-webhook">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyLink('{$webhook_url nofilter}', 'live-webhook')">{l s='form.connection.button.copy_link' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="webhook-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>

            <h3 class="col-lg-12">{l s='form.connection.label.directlink.directlink' mod='ingenico_epayments'}<span class="icon-span modal-link" data-modal-id="direct-link-modal"></span></h3>
            <div class="disclaimer">
                <span class="icon-span"></span>
                <p>{l s='form.connection.label.directlink.label4' mod='ingenico_epayments'}
                    <br>
                    <a href="https://www.mwrinfosecurity.com/our-thinking/pci-compliance-which-saq-is-right-for-me" target="_blank">{l s='form.settings.label.readmore' mod='ingenico_epayments'}</a>
                </p>
            </div>
            <div id="direct-link" class="tooltiptext" style="display: none">
                <button type="button" class="close" onclick="Ingenico.toggleTooltip('direct-link')">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4>{l s='form.connection.label.directlink.label1' mod='ingenico_epayments'}</h4>
                <p>{l s='form.connection.label.directlink.label2' mod='ingenico_epayments'}<br>
                    {l s='form.connection.label.directlink.label3' mod='ingenico_epayments'}</p>
            </div>

            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.user' mod='ingenico_epayments'}
                </label>
                <input class="form-control " type="text" size="5" name="connection_live_dl_user" value="{$connection_live_dl_user|escape}" {if $connection_mode}required{/if}>
                <a href="#" class="modal-link" data-modal-id="direct-link-user-modal">{l s='form.connection.label.where' mod='ingenico_epayments'}</a>
                <div class="copy-response" data-copy="live-directlink-user">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyValue('connection_live_dl_user', 'live-directlink-user')">{l s='form.connection.button.copy_value' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="direct-link-user-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-12">
                    {l s='form.connection.label.password' mod='ingenico_epayments'}
                </label>
                <input class="form-control" type="password" size="5" name="connection_live_dl_password" value="{$connection_live_dl_password|escape}" {if $connection_mode}required{/if}>
                <span toggle="connection_live_dl_password" class="form-btn mask"
                      data-show="{l s='form.connection.button.show' mod='ingenico_epayments'}"
                      data-hide="{l s='form.connection.button.hide' mod='ingenico_epayments'}">
                    {l s='form.connection.button.show' mod='ingenico_epayments'}
                </span>
                <span class="form-btn generate" onclick="Ingenico.generateHash('connection_live_dl_password', 40)">{l s='form.connection.button.generate' mod='ingenico_epayments'}</span>
                <div class="copy-response" data-copy="live-directlink-password">{l s='form.connection.label.copied' mod='ingenico_epayments'}</div>
                <div class="col-lg-12">
                    <a class="copy-link" onclick="Ingenico.copyValue('connection_live_dl_password', 'live-directlink-password')">{l s='form.connection.button.copy_value' mod='ingenico_epayments'}</a>
                    <a class="modal-link modal-link-how-to" data-modal-id="direct-link-user-modal">{l s='form.connection.label.howto' mod='ingenico_epayments'}</a>
                </div>
            </div>
        </div>
    </div>
</div>

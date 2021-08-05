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
<form class="ingenico-cb-form" action="{$action|escape}" method="POST">
    <div>
        <input type="hidden" name="payment_id" value="{$payment_id|escape}">

        {if $payment_page_type === 'INLINE'}
            <div class="one-click-wrap">
                <div class="cc-helper-text"></div>
                <div class="iframe-tos-hint" style="display: none">
                    {l s='You should accept terms and conditions' mod='ingenico_epayments'}
                </div>
                <div class="iframe-wrap">
                    <iframe id="ingenico_iframe_cb" lazyload="on" src="{$frame_url|escape}"
                            style="width: 100%; min-height: 505px; border: none;"
                            class="ingenico_frame">
                    </iframe>
                </div>
            </div>
            <div class="clear: both;">&nbsp;</div>
        {/if}

        {if $one_click_payment == true}
            <label for="new">
                <input type="radio" id="new_alias" name="alias" value="new" checked>
                {l s='checkout.use_new_payment_method' mod='ingenico_epayments'}
            </label>

            <br />

            {foreach $aliases as $index => $alias}
                <label for="alias_{$alias->getId()|escape}">
                    <input type="radio" id="alias_{$alias->getId()|escape}" name="alias" value="{$alias->getId()|escape}">
                    <img src="{$alias->getEmbeddedLogo() nofilter}" width="50" alt="{$alias->getTranslatedName()|escape}">
                    {$alias->getTranslatedName()|escape}
                </label>
                <br />
            {/foreach}
        {else}
            <input type="hidden" name="alias" value="new">
        {/if}
    </div>
</form>

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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery{$suffix}.js"></script>
<script src="{$path}/views/js/front{$suffix}.js"></script>
<link rel="stylesheet" href="{$path}/views/css/front{$suffix}.css" type="text/css" media="all">

<div class="ingenico-return">
    <script>
        function ingenico_redirect(url) {
            let ingenico_redirect = url;
            let isInIframe = (window.location != window.parent.location) ? true : false;
            if (isInIframe) {
                window.top.location.href = ingenico_redirect;
            } else {
                window.location.href = ingenico_redirect;
            }
        }
    </script>

    {if $payment_status == 'authorized' || $payment_status == 'captured'}
        {if $is_show_warning}
            {* Show warning page *}
            <div class="ingenico-payment-warning">
                <strong>{l s='checkout.test_mode_warning' mod='ingenico_epayments'}</strong>
                <p>{l s='checkout.manual_capture_required' mod='ingenico_epayments'}</p>
                <p>{l s='checkout.click_capture' mod='ingenico_epayments'}</p>
            </div>
            <div class="ingenico-info">
                <img class="account-creation-done" src="{$path}/views/imgs/ic_done.svg">
                <p>{$payment_status}</p>

                <button class="ingenico-button" onClick="ingenico_redirect('{$success_page nofilter}');">{l s='checkout.back_to_shop' mod='ingenico_epayments'}</button>
            </div>
        {else}
            {* Redirect so success page immediately *}
            <div class="ingenico-info">
                <div class="ingenico-loader">
                    <img src="{$path}/views/imgs/loader.svg" alt="{l s='checkout.please_wait' mod='ingenico_epayments'}">
                </div>
                <script>
                    ingenico_redirect('{$success_page nofilter}');
                </script>
            </div>
        {/if}
    {elseif $payment_status == 'pending'}
        {* Redirect so success page immediately *}
        <div class="ingenico-info">
            <div class="ingenico-loader">
                <img src="{$path}/views/imgs/loader.svg" alt="{l s='checkout.please_wait' mod='ingenico_epayments'}">
            </div>
            <script>
                ingenico_redirect('{$success_page nofilter}');
            </script>
        </div>
    {elseif $payment_status == 'cancelled'}
        <div class="message">
            <p>
                {l s='checkout.payment_cancelled' mod='ingenico_epayments'}
            </p>
        </div>
    {else}
        <div class="message">
            <p>
                {l s='checkout.something_wrong' mod='ingenico_epayments'}
            </p>
        </div>
    {/if}
</div>

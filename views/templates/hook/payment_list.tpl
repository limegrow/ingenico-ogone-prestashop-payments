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
<form action="{$action|escape}" method="POST" id="ingenico-payment-form">
    <ul>
        {foreach $payment_methods as $key => $method}
            <img src="{$method->getEmbeddedLogo()|escape}" height="30">
        {/foreach}
    </ul>

    <div>
        {if $one_click_payment == true}
            {foreach $aliases as $index => $alias}
                <label for="alias_{$alias->getId()|escape}">
                    <input type="radio" id="alias_{$alias->getId()|escape}" name="alias" value="{$alias->getId()|escape}" {if $index == 0} checked {/if}>
                    <img src="{$alias->getEmbeddedLogo() nofilter}" width="50">
                    {$alias->getName()|escape}
                </label>
                <br />
            {/foreach}

            {if count($aliases) > 0}
                <label for="new">
                    <input type="radio" id="new_alias" name="alias" value="new" {if count($aliases) == 0} checked {/if}>
                    {l s='checkout.use_new_payment_method' mod='ingenico_epayments'}
                </label>
            {else}
                <input type="hidden" name="alias" value="new">
            {/if}
        {/if}
    </div>
</form>
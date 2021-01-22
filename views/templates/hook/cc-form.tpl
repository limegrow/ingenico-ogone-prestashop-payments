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
<form class="ingenico-cc-form" action="{$action|escape}" method="POST">
    <div>
        {if $one_click_payment == true}
            <label for="new">
                <input type="radio" id="new_alias" name="alias" value="new" checked>
                {l s='checkout.use_new_payment_method' mod='ingenico_epayments'}
            </label>

            {if $payment_page_type === 'INLINE'}
                <script>
                    window.onload = function() {
                        jQuery(document).ready(function($) {
                            $(document).on('click', 'input[name="alias"]', function() {
                                if ($(this).val() === 'new') {
                                    $('#ingenico_form').show();
                                } else {
                                    $('#ingenico_form').hide();
                                }
                            });
                        });
                    };
                </script>

                <div class="cc-helper-text"></div>
                <iframe id="ingenico_iframe" lazyload="on" src="{$frame_url|escape}" style="width: 100%; min-height: 505px; border: none;"></iframe>
            {/if}

            <br />

            {foreach $aliases as $index => $alias}
                <label for="alias_{$alias->getId()}">
                    <input type="radio" id="alias_{$alias->getId()}" name="alias" value="{$alias->getId()}">
                    <img src="{$alias->getEmbeddedLogo()}" width="50" alt="{$alias->getTranslatedName()}">
                    {$alias->getTranslatedName()}
                </label>
                <br />
            {/foreach}
        {else}
            <input type="hidden" name="alias" value="new">

            {if $payment_page_type === 'INLINE'}
                <div class="cc-helper-text"></div>
                <iframe id="ingenico_iframe" lazyload="on" src="{$frame_url|escape}" style="width: 100%; min-height: 505px; border: none;"></iframe>
            {/if}
        {/if}
    </div>

</form>


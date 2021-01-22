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
<script src="{$module_dir}/views/js/front{$suffix}.js"></script>
<link rel="stylesheet" href="{$module_dir}/views/css/front{$suffix}.css" type="text/css" media="all">

<div class="ingenico-return">
    <div class="ingenico-loader">
        <img src="{$module_dir}/views/imgs/loader.svg" alt="{l s='checkout.please_wait' mod='ingenico_epayments'}">
    </div>

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

        jQuery(document).ready(function ($) {
            $.ajax({
                type: 'POST',
                url: '{$ajax_url nofilter}',
                data: {
                    method: 'charge_payment',
                    token: '{$token}',
                    order_id: '{$order_id}',
                    alias_id: '{$alias_id}',
                    card_brand: '{$card_brand}',
                },
                dataType: 'json'
            }).always(function(response) {
                //
            }).done(function(response) {
                switch (response.status) {
                    case 'success':
                    case 'cancelled':
                    case 'error':
                        ingenico_redirect(response.redirect);
                        break;
                    case '3ds_required':
                        if (top.$('#checkout-payment-step div.content').length === 1) {
                            top.$('#checkout-payment-step div.content').html($(response.html));
                        } else {
                            top.$('body').html($(response.html));
                        }
                        //$('.ingenico-loader').hide();
                        //$(response.html).insertAfter($('.ingenico-return'));
                        break;
                    case 'show_warning':
                        $('.ingenico-return').replaceWith(response.html);
                        break;
                }
            });
        });
    </script>
</div>

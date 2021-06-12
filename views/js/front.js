/**
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
 * to contact@ingenico.com we can send you a copy immediately.
 *
 *  @author    Ingenico <contact@ingenico.com>
 *  @copyright 2007-2021 Ingenico
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
"use strict"
/* global ingenico_payment_page_type */

if ( typeof ingenico_payment_page_type === 'undefined' ) {
    var ingenico_payment_page_type = null;
}

const Ingenico = {

    init: function () {
        $('.ingenico-confirmation .payment-title').click(function() {
            let block = $(this).closest('li').find('.payment-content').first();
            if (block.is(':visible')) {
                block.slideUp();
            } else {
                block.slideDown(400, function () {
                    block.css('display', 'block');
                });
            }

            // Load iframe
            if (block.is('iframe') && !block.data('loaded')) {
                let src = block.data('src');
                block.prop('src', src);
                block[0].onload = function() {
                    block.data('loaded', 'true');
                };
            }
        });
    },

    setEnvironment: function () {
        const data = Ingenico.getBrowserData();
        for (let elem in data) {
            Ingenico.setCookie(elem, data[elem], 2);
        }
    },

    getBrowserData: function () {
        return {
            'browserColorDepth': window.screen.colorDepth,
            'browserJavaEnabled': navigator.javaEnabled(),
            'browserLanguage': navigator.language,
            'browserScreenHeight': window.screen.height,
            'browserScreenWidth': window.screen.width,
            'browserTimeZone': (new Date()).getTimezoneOffset()
        };
    },

    setCookie: function(name, value, days) {
        let d = new Date;
        d.setTime(d.getTime() + 24*60*60*1000*days);
        document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
    },

    inlineFailure: function (e, msg, aliasId, cardBrand, iframeUrl) {
        let ingenicoCcIFrame = $('.ingenico-cc-form iframe#ingenico_iframe');
        let ingenicoCcHelper = $('.ingenico-cc-form div.cc-helper-text');

        ingenicoCcIFrame.hide();
        ingenicoCcHelper.html(msg);

        $('#ingenico-cc-iframe-retry').on('click', function(event) {
            // Prevent PrestaShop collapsing step 4 in the checkout
            event.stopPropagation();

            ingenicoCcHelper.html('');

            // Reload the iFrame
            ingenicoCcIFrame.show();
            document.getElementById('ingenico_iframe').src = iframeUrl;
        });
    },

    /**
     * Save Payment Method.
     *
     * @param payment_method
     * @returns {*|jQuery}
     */
    setPaymentMethod: function (payment_method) {
        return $.ajax(ingenico_ajax_url, {
            'method': 'POST',
            'data': {
                method: 'set_payment_method',
                payment_method: payment_method
            },
            'dataType': 'json'
        });
    }
};

jQuery(document).ready(function ($) {
    // Hide "payment confirmation" on the checkout page when cc form chosen
    $(document).on( 'click', '.payment-options [name="payment-option"]', function( e ) {
        var id = $( e.target ).prop('id'),
            form = $( '#pay-with-' + id + '-form' ),
            button = $( '#payment-confirmation button' );

        // Saved selected payment method
        Ingenico.setPaymentMethod( form.find( '[name="payment_id"]' ).first().val() );

        if ( ingenico_payment_page_type === 'INLINE' ) {
            // Tick the "new card" option if exists
            form.find( '[name="alias"]' ).find(':radio[value=new]').click();

            button.show();
            if ( form.find( '.ingenico_frame' ).length > 0 ) {
                button.hide();
            }
        }
    } );

    // Toggle CCForm on alias selection
    $(document).on( 'click', '.payment-options input[name="alias"]', function( e ) {
        if ( ingenico_payment_page_type === 'INLINE' ) {
            var wrap = $( e.target ).closest( '.js-payment-option-form' ).find( '.one-click-wrap' ),
                button = $( '#payment-confirmation button' );

            if ( $(this).val() === 'new' ) {
                wrap.show();
                button.hide();
            } else {
                wrap.hide();
                button.show();
            }
        }
    });

    if ( ingenico_payment_page_type === 'INLINE' ) {
        var tos = $( '#conditions-to-approve input[type="checkbox"]' );
        if ( tos.length > 0 ) {
            setInterval(function () {
                if ( tos.is( ':checked' ) ) {
                    //$( '.iframe-wrap' ).show();
                    $( '.iframe-wrap' ).removeClass( 'locked-frame' );
                } else {
                    //$( '.iframe-wrap' ).hide();
                    $( '.iframe-wrap' ).addClass( 'locked-frame' );
                }
            }, 500);
        }
    }
});

$(function() {
    Ingenico.init();
    Ingenico.setEnvironment();
});

jQuery(document).ready(function ($) {
    $(document).on('ingenico:inline:failure', 'body', function(e, msg, aliasId, cardBrand, url) {
        Ingenico.inlineFailure(e, msg, aliasId, cardBrand, url);
    });
});
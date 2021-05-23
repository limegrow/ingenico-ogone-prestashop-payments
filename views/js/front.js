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
};

window.IngenicoCCInline = {
    iframe: null,
    wrap: null,
    oc_wrap: null,
    tos: null,

    init: function() {
        var self = this;

        this.iframe = $('iframe#ingenico_iframe');
        this.wrap = $('.iframe-wrap');
        this.oc_wrap = $('.one-click-wrap');
        this.tos = $('#conditions-to-approve input[type="checkbox"]');

        // Hide iframe if ToS has been unchecked
        if (this.tos.length > 0) {
            this.watchTos();
        }

        // Hide "payment confirmation" on the checkout page when cc form chosen
        $(document).on('click', '.payment-options [name="payment-option"]', function() {
            // If Inline CC mode is active
            if ($('#pay-with-' + $(this).prop('id') + '-form').find(self.iframe).length > 0) {
                setTimeout(function () {
                    self.hideConfirmationButton();
                }, 1500);
            } else {
                self.showConfirmationButton();
            }
        });

        // Toggle CCForm on alias selection
        $(document).on('click', '.payment-options input[name="alias"]', function() {
            // Check if the cc iframe is exists
            if (self.iframe.length === 0) {
                return;
            }

            if ($(this).val() === 'new') {
                self.showOneClickIframe();
                self.hideConfirmationButton();
            } else {
                self.hideOneClickIframe();
                self.showConfirmationButton();
            }
        });
    },

    /**
     * Watch "Terms of service" and shows or hides iframe depends on the checkbox
     */
    watchTos: function () {
        var self = this;

        if (self.tos.length > 0) {
            setInterval(function () {
                if (self.tos.is(':checked')) {
                    self.showIframe();
                } else {
                    self.hideIframe();
                }
            }, 500);
        }
    },

    /**
     * Hide CC Iframe
     */
    hideIframe: function () {
        this.wrap.hide();
    },

    /**
     * Show CC Iframe
     */
    showIframe: function () {
        this.wrap.show();
    },

    /**
     * Hide "One Click" iframe
     */
    hideOneClickIframe: function () {
        this.oc_wrap.hide();
    },

    /**
     * Show "One Click" iframe
     */
    showOneClickIframe: function () {
        this.oc_wrap.show();
    },

    /**
     * Hide Confirmation Button
     */
    hideConfirmationButton: function () {
        $('#payment-confirmation button').hide();
    },

    /**
     * Show Confirmation Button
     */
    showConfirmationButton: function () {
        $('#payment-confirmation button').show();
    }
};

$(function() {
    Ingenico.init();
    Ingenico.setEnvironment();
});

jQuery(document).ready(function ($) {
    $(document).on('ingenico:inline:failure', 'body', function(e, msg, aliasId, cardBrand, url) {
        Ingenico.inlineFailure(e, msg, aliasId, cardBrand, url);
    });
});
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

        $('.ingenico-header .switch.test-live-toggle').click(function(e) {
            if ($('.ingenico-header .switch.test-live-toggle input:checked').length > 0) {
                // Check if live configured.
                const requiredFields = [
                    'connection_live_pspid',
                    'connection_live_signature',
                    'connection_live_dl_user',
                    'connection_live_dl_password',
                ];

                var isValid = true;
                requiredFields.forEach(function(item, index, arr) {
                    let el = $('[name="' + item + '"]');
                    if (el.length === 0 || el.val().length === 0) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    Ingenico.openModal('credentials-live-validation-modal');
                    e.preventDefault();
                    return false;
                } else {
                    // Set Live is "On"
                    $('[name=connection_mode]').val('on');
                    $('.ingenico-header, .ingenico-settings').toggleClass('test').toggleClass('live');

                    $('[name="connection_test_pspid"],[name="connection_test_signature"],[name="connection_test_dl_user"],[name="connection_test_dl_password"]').removeProp('required');
                    $('[name="connection_live_pspid"],[name="connection_live_signature"],[name="connection_live_dl_user"],[name="connection_live_dl_password"]').prop('required', true);
                }
            } else {
                // Set Live is "Off"
                $('[name=connection_mode]').val('off');
                $('[name="connection_live_pspid"],[name="connection_live_signature"],[name="connection_live_dl_user"],[name="connection_live_dl_password"]').removeProp('required');
                $('[name="connection_test_pspid"],[name="connection_test_signature"],[name="connection_test_dl_user"],[name="connection_test_dl_password"]').prop('required', true);
            }
        });

        $(document).on('click', '.ingenico-settings .modal-link', function (e) {
            e.preventDefault();
            const modal_id = $(e.target).data('modal-id');
            if (modal_id === 'payment-methods-list') {
                Ingenico.getPaymentMethodModal();
            }

            Ingenico.openModal(modal_id);
        });

        $(document).on( 'click', '.account-header', function (e) {
            let el = $(this);
            if ( el.prop('disabled') ) {
                e.preventDefault();
                return false;
            }

            el.prop( 'disabled', true );
            el.find( '.collapse-btn' ).first().click();

            setTimeout(function () {
                el.prop( 'disabled', false );
            }, 500);
        } );

        $(document).on( 'click', '.account-header', function (e) {
            // Toggle Show and Hide labels
            $(this).find( 'span' ).toggle();
        } );

        $('.modal-dialog .close, .modal-backdrop, .ingenico-modal').click(function(e) {
            if (Ingenico.hasSomeParentTheClass(e.target, 'modal-dialog')) {
                if(!Ingenico.hasSomeParentTheClass(e.target, 'close')) {
                    return;
                }
            }

            e.preventDefault();
            Ingenico.closeModal();
        });

        $('.switch > input').click(function (e) {
            if ($(this).hasClass('disabled')) {
                e.preventDefault();
                return false;
            }
        });

        $('.switch > input').change(function () {
            let toggle_target = $(this).data('toggle-name');
            let reverse = $(this).data('toggle-reverse');
            let checked = $(this).is(':checked');
            if (checked) {
                if (parseInt(reverse) === 1) {
                    $('.' + toggle_target).slideUp();
                } else {
                    $('.' + toggle_target).slideDown();
                }
            } else {
                if (parseInt(reverse) === 1) {
                    $('.' + toggle_target).slideDown();
                } else {
                    $('.' + toggle_target).slideUp();
                }
            }
        });

        $('.radio > input').change(function () {
            let val = $(this).val();
            let toggle_target = $(this).data('toggle-name');
            $('.' + toggle_target).slideUp();
            $('.' + toggle_target + val).slideDown();
            //IE-89
            // Inline method always have "Ingenico" template
            if(toggle_target + val === 'paymentpage_typeINLINE') {
                $('#template_manager').click();
                $('[name=paymentpage_template_name]').hide();
            } else {
                $('[name=paymentpage_template_name]').show();
            }
        });

        $('[name=paymentpage_type]').change(function () {
            if ($(this).val() === 'REDIRECT') {
                $('.step1.inline').hide();
                $('.step1.redirect').show();
                $('.ppt.inline').hide();
                $('.ppt.redirect').show();
            } else if ($(this).val() === 'INLINE') {
                $('.step1.redirect').hide();
                $('.step1.inline').show();
                $('.ppt.redirect').hide();
                $('.ppt.inline').show();
            }
        });

        $('.toggle-field').click(function(e) {
            e.preventDefault();
            Ingenico.closeModal();
        });

        $('.country-list').keyup(function() {
            var query = $(this).val();
            Ingenico.filterCountries(query);
        });

        $('.payment-methods-list').keyup(function() {
            var query = $(this).val();
            Ingenico.filterPaymentMethods(query);
        });

        // Support: Export Settings Button
        $(document).on('click', '#export-settings', function (e) {
            e.preventDefault();
            $('#submitExportSettings').click();
        });

        $(document).on('change', 'input[type=file]#support-import', function (e) {
            const filename = $('input[type=file]#support-import').val().split('\\').pop();
            $('#support .upload-label').html(filename);
        });

        // Support: Show Upload Confirmation Modal
        $(document).on('click', '#modalImportSettings', function (e) {
            e.preventDefault();
            Ingenico.openModal('upload-confirmation-modal');
        });

        // Support: Import Settings Button
        $(document).on('click', '#submitModalImportSettings', function (e) {
            e.preventDefault();
            $('#submitImportSettings').click();
        });

        // Button: Add more payment methods / Apply
        $(document).on( 'click', '[name=submit_save_payments]', function (e) {
            e.preventDefault();

            Ingenico.getModalPaymentMethods(function (payment_methods) {
                if (payment_methods.includes('afterpay') || payment_methods.includes('klarna') ) {
                    // Show "Choose Country" modal
                    Ingenico.openModal('openinvoice-choose-country-modal');
                }

                // Save payment methods
                Ingenico.addPaymentMethod();
            });
        });

        // Payment methods list: Only one of the two Open Invoice PMs can be selected
        $(document).on( 'click', '#payment-methods-list input[name="payment_methods[]"]', function (e) {
            Ingenico.getSelectedPaymentMethods(function (payment_methods) {
                Ingenico.disableAfterpayOrKlarnaMethods(payment_methods);
            });
        });

        // OpenInvoice: Choose Country
        $(document).on( 'click', '#openinvoice-choose-country-modal .apply', function () {
            let selected = $('[name=openinvoice_country]:checked').val();
            if (selected === '') {
                // Unset all OpenInvoice methods
                Ingenico.removePaymentMethod('afterpay');
                Ingenico.removePaymentMethod('klarna');
            }

            $('[name=generic_country]').val(selected);

            $.ajax( ingenico_ajax_url, {
                'method': 'POST',
                'data': {
                    method: 'set_merchant_country',
                    country: selected
                },
                'dataType': 'json'
            } ).success(function(data) {
                Ingenico.closeModal('openinvoice-choose-country-modal');
            } );
        } );

        // OpenInvoice: Select method
        $(document).on( 'click', '#openinvoice-select-payment-modal .apply', function () {
            window.OPENINVOICE_METHOD = $('[name=openinvoice_method]:checked').val();
            Ingenico.closeModal('openinvoice-select-payment-modal', function () {
                Ingenico.addCountries();
            });
        } );

        $(document).on( 'keyup', '.payment-methods-list', function (e) {
            const query = $(this).val();
            Ingenico.filterPaymentMethods(query);
        });

        if (typeof installments_amount_min !== 'undefined') {
            $('#installments_amount_range').slider({
                range: true,
                min: 1,
                max: 24,
                values: [installments_amount_min, installments_amount_max],
                slide: function (event, ui) {
                    let min_value = ui.values[0];
                    let max_value = ui.values[1];
                    $('[name=instalments_flex_instalments_min]').val(min_value);
                    $('[name=instalments_flex_instalments_max]').val(max_value);

                    $('#installments_amount_range .ui-slider-handle b').remove();
                    let handler = $('#installments_amount_range .ui-slider-handle');
                    handler.eq(0).append("<b class='amount left-handler'><span id='min'>" + min_value + "</span></b>");
                    handler.eq(1).append("<b class='amount right-handler'><span id='max'>" + max_value + "</span></b>");
                }
            });
            let amount_handler = $('#installments_amount_range .ui-slider-handle');
            amount_handler.eq(0).append("<b class='amount left-handler'><span id='min'>" + installments_amount_min + "</span></b>");
            amount_handler.eq(1).append("<b class='amount right-handler'><span id='max'>" + installments_amount_max + "</span></b>");

            $('#installments_period_range').slider({
                range: true,
                min: 1,
                max: 90,
                values: [installments_period_min, installments_period_max],
                slide: function (event, ui) {
                    let min_value = ui.values[0];
                    let max_value = ui.values[1];
                    $('[name=instalments_flex_period_min]').val(min_value);
                    $('[name=instalments_flex_period_max]').val(max_value);
                    $('#installments_period_range .ui-slider-handle b').remove();
                    let handler = $('#installments_period_range .ui-slider-handle');
                    handler.eq(0).append("<b class='amount left-handler'><span id='min'>" + min_value + "</span></b>");
                    handler.eq(1).append("<b class='amount right-handler'><span id='max'>" + max_value + "</span></b>");
                }
            });
            let period_handler = $('#installments_period_range .ui-slider-handle');
            period_handler.eq(0).append("<b class='amount left-handler'><span id='min'>" + installments_period_min + "</span></b>");
            period_handler.eq(1).append("<b class='amount right-handler'><span id='max'>" + installments_period_max + "</span></b>");

            $('#installments_first_range').slider({
                range: true,
                min: 1,
                max: 99,
                values: [installments_first_min, installments_first_max],
                slide: function (event, ui) {
                    let min_value = ui.values[0];
                    let max_value = ui.values[1];
                    $('[name=instalments_flex_firstpayment_min]').val(min_value);
                    $('[name=instalments_flex_firstpayment_max]').val(max_value);
                    $('#installments_first_range .ui-slider-handle b').remove();
                    let handler = $('#installments_first_range .ui-slider-handle');
                    handler.eq(0).append("<b class='amount left-handler'><span id='min'>" + min_value + "%</span></b>");
                    handler.eq(1).append("<b class='amount right-handler'><span id='max'>" + max_value + "%</span></b>");
                }
            });
            let first_handler = $('#installments_first_range .ui-slider-handle');
            first_handler.eq(0).append("<b class='amount left-handler'><span id='min'>" + installments_first_min + "%</span></b>");
            first_handler.eq(1).append("<b class='amount right-handler'><span id='max'>" + installments_first_max + "%</span></b>");
        }

        $('.form-btn.mask').click(function() {
            var input = $('[name="' + $(this).attr('toggle') +'"]');
            if (input.attr('type') == 'password') {
                $(this).text($(this).data('hide'));
                input.attr('type', 'text');
            } else {
                $(this).text($(this).data('show'));
                input.attr('type', 'password');
            }
        });

        let hash = window.location.hash;
        if (typeof(hash) != "undefined" && hash !== null && hash !== '') {
            let panel = $('#content.bootstrap .ingenico-settings .form-wrapper .panel-footer');
            if (hash === '#support') {
                // Hide Save button in bottom
                panel.hide();
            } else {
                panel.show();
            }

            $('.nav-tabs li').removeClass('active');
            $('.tab-content .tab-pane').removeClass('active');
            $('.nav-tabs li a[href="' + hash + '"]').parent().addClass('active');
            $('.tab-content ' + hash).addClass('active');
            $('#configuration_form').attr('action', window.location.href);
            Ingenico.connectExistingAccount();
        }

        $('.nav-tabs li a').click(function () {
            let hash = this.href.split("#")[1];
            window.location.hash = hash;
            $('#configuration_form').attr('action', window.location.href);

            let panel = $('#content.bootstrap .ingenico-settings .form-wrapper .panel-footer');
            if (hash === 'support') {
                // Hide Save button in bottom
                panel.hide();
            } else {
                panel.show();
            }
        });

        $(document).keydown(function(e) {
            if (e.keyCode == 9) {  //tab pressed
                if($('.modal-backdrop').is(":visible")) {
                    e.preventDefault(); // stops its action
                }
            }
            if (e.keyCode == 27) {  //esc pressed
                Ingenico.closeModal();
            }
        });

        //Disable account registration submit button
        //if terms agreement checkbox unchecked
        var the_terms = $('#agreement');
        if (the_terms.is(":checked")) {
            $('#registration-btn').removeAttr('disabled').removeClass('disabled');
        } else {
            $('#registration-btn').attr('disabled', 'disabled').addClass('disabled');
        }
        the_terms.click(function() {
            if ($(this).is(":checked")) {
                $('#registration-btn').removeAttr('disabled').removeClass('disabled');
            } else {
                $('#registration-btn').attr('disabled', 'disabled').addClass('disabled');
            }
        });

        $('[name=submit_save_countries]').click(function(e) {
            e.preventDefault();
            Ingenico.addCountries();
        });

        $(document).on( 'click', '.paymentpage_template input[type=file]', function (e) {
            let el = $(this);

            var filename = el.split('\\').pop();
            $('.paymentpage_template .upload-label').html(filename);
        } );
    },

    pattern : /[a-zA-Z0-9_\-\+\.]/,

    getRandomByte : function()
    {
        if(window.crypto && window.crypto.getRandomValues)
        {
            var result = new Uint8Array(1);
            window.crypto.getRandomValues(result);
            return result[0];
        }
        else if(window.msCrypto && window.msCrypto.getRandomValues)
        {
            var result = new Uint8Array(1);
            window.msCrypto.getRandomValues(result);
            return result[0];
        }
        else
        {
            return Math.floor(Math.random() * 256);
        }
    },

    generateHash : function(field, length)
    {
        let generated_hash = Array.apply(null, {'length': length})
            .map(function()
            {
                var result;
                while(true)
                {
                    result = String.fromCharCode(this.getRandomByte());
                    if(this.pattern.test(result))
                    {
                        return result;
                    }
                }
            }, this)
            .join('');

        document.getElementsByName(field)[0].value = generated_hash;
    },

    copyValue: function (field, copy_response_container) {
        let el = document.createElement('textarea');
        let value = document.getElementsByName(field)[0].value;
        el.value = value;
        el.setAttribute('readonly', '');
        el.style = {position: 'absolute', left: '-9999px'};
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        $(".copy-response[data-copy='" + copy_response_container +"']").slideDown();
        setTimeout(function(){
            $(".copy-response[data-copy='" + copy_response_container +"']").slideUp();
        }, 3000);
    },

    copyLink: function (link, copy_response_container) {
        var el = document.createElement('textarea');
        el.value = link;
        el.setAttribute('readonly', '');
        el.style = {position: 'absolute', left: '-9999px'};
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        $(".copy-response[data-copy='" + copy_response_container +"']").slideDown();
        setTimeout(function(){
            $(".copy-response[data-copy='" + copy_response_container +"']").slideUp();
        }, 6000);
    },

    toggleTooltip: function (tooltip) {
        let x = document.getElementById(tooltip);
        if (x.style.display === 'none') {
            x.style.display = 'block';
        } else {
            x.style.display = 'none';
        }
    },

    /**
     * Open Bootstrap Modal
     *
     * @param modal_id
     * @param options
     * @param onShow
     * @return {jQuery|HTMLElement}
     */
    openModal: function (modal_id, options, onShow) {
        if (typeof options === 'undefined') {
            options = {};
        }

        if (typeof onShow === 'undefined') {
            onShow = function () {};
        }

        let elm = $('#' + modal_id),
            offset = $(window).scrollTop() - 250;

        // Show modal
        elm.find('.modal-dialog').addClass('modal-dialog-scrollable');
        elm.modal(options);
        elm.on('shown.bs.modal', function () {
            //$('body').css('overflow', 'auto');
            //elm.addClass('in');
            //elm.css('top', offset + 'px');

            // @todo
            //$.scrollTo(elm);

            $("html, body").animate({ scrollTop: 0 }, "slow");

            onShow();
        });

        elm.on('hide.bs.modal', function () {
            //elm.removeClass('in').hide();
            //$('.modal-backdrop').hide();
        });

        return elm;
    },

    /**
     * Close Bootstrap Modal
     *
     * @param modal_id
     * @param onClose
     * @return {jQuery|HTMLElement}
     */
    closeModal: function (modal_id, onClose) {
        let elm = $('.ingenico-modal');

        if (typeof modal_id !== 'undefined' || !modal_id) {
            elm = $('#' + modal_id);
        }

        if (typeof onClose === 'undefined') {
            onClose = function () {};
        }

        elm.modal('hide');
        elm.on('hide.bs.modal', function () {
            //onClose();
        });

        setTimeout(function () {
            onClose();
        }, 300);

        return elm;

        //$(modal + '.ingenico-modal').removeClass('in').hide();
        //$('.modal-backdrop').hide();
        //$(modal + '.ingenico-modal').modal('hide')
        //elm.on('hide.bs.modal', function () {
        //    elm.removeClass('in').hide();
        //    $('.modal-backdrop').hide();
        //});
    },

    filterCountries: function (query) {
        var selected_countries = '';
        $('#countries-list [type="checkbox"]:checked').each(function() {
            selected_countries += this.value + '|';
        }).promise().done(function() {
            $.ajax(ingenico_ajax_url, {
                'method': 'POST',
                'data': {
                    query : query,
                    method: 'filter_countries',
                    selected_countries: selected_countries
                },
                'dataType': 'json'
            }).success(function(data) {
                $('#countries-list ul').html(data);
            });
        });
    },

    filterPaymentMethods: function (query) {
        $.ajax(ingenico_ajax_url, {
            'method': 'POST',
            'data': {
                query : query,
                method: 'filter_payment_methods'
            },
            'dataType': 'json'
        }).success(function(data) {
            $('#payment-methods-list ul').html(data);

            Ingenico.getSelectedPaymentMethods(function (payment_methods) {
                Ingenico.disableAfterpayOrKlarnaMethods(payment_methods);
            });
        });
    },

    /**
     * Get Selected Payment Methods
     * @param callback
     */
    getSelectedPaymentMethods: function(callback) {
        // @see #selected_payment_methods
        let payment_methods = $("#pm_list").val();

        // Append new methods
        Ingenico.getModalPaymentMethods(function (selected) {
            let result = payment_methods.concat(selected);
            callback(result);
        });
    },

    /**
     * Get Selected Payment Methods in Modal
     * @param callback
     */
    getModalPaymentMethods: function (callback) {
        let payment_methods = [];

        $('#payment-methods-list input[name="payment_methods[]"]:checked').each(function () {
            let method = $(this).val();
            payment_methods.push(method);
        }).promise().done(function() {
            callback(payment_methods);
        });
    },

    /**
     * Disable Afterpay or Klarna in Payment List Modal
     * @param payment_methods
     */
    disableAfterpayOrKlarnaMethods: function(payment_methods) {
        // Disallow usage of Afterpay and Klarna in one time
        let afterpay = $('#payment-methods-list input[value="afterpay"]'),
            klarna = $('#payment-methods-list input[value="klarna"]'),
            all = $('#payment-methods-list input[name="payment_methods[]"]');

        if (payment_methods.includes('afterpay')) {
            afterpay.prop('disabled', false).removeClass('disabled');
            klarna.prop('disabled', true).addClass('disabled');
        } else if (payment_methods.includes('klarna')) {
            klarna.prop('disabled', false).removeClass('disabled');
            afterpay.prop('disabled', true).addClass('disabled');
        } else {
            all.prop('disabled', false).removeClass('disabled');
        }
    },

    removePaymentMethod: function (payment_method) {
        $("#pm_list option[value='" + payment_method + "']").remove();
        $('li.' + payment_method).fadeOut(1000, function() {
            $(this).remove();
        });
    },

    addPaymentMethod: function () {
        // Currently selected methods
        Ingenico.getSelectedPaymentMethods(function (payment_methods) {
            $.ajax(ingenico_ajax_url, {
                'method': 'POST',
                'data': {
                    payment_methods: payment_methods,
                    method: 'fetch_payment_methods'
                },
                'dataType': 'json'
            }).success(function(data) {
                $('#selected_payment_methods').html(data);
                Ingenico.closeModal('payment-methods-list');
            });
        });
    },

    getPaymentMethodModal: function () {
        // Currently selected methods
        let payment_methods = $("#pm_list").val();

        $.ajax(ingenico_ajax_url, {
            'data': {
                method: 'payment_method_modal',
                selected: payment_methods
            },
            'dataType': 'json'
        }).success(function(data) {
            $('#payment-methods-list .methods-list').html(data);

            Ingenico.getSelectedPaymentMethods(function (payment_methods) {
                Ingenico.disableAfterpayOrKlarnaMethods(payment_methods);
            });

        });
    },

    hasSomeParentTheClass: function(element, classname) {
        if(element.tagName === 'HTML') {
            return false;
        }
        if (element.className.split(' ').indexOf(classname) >= 0) {
            return true;
        }

        return element.parentNode && this.hasSomeParentTheClass(element.parentNode, classname);
    },

    showMigrationUI: function() {
        $('.installation-options').hide();
        $('#migration-frm').show();
        $('#create-account-frm').hide();
        $('#configuration_form').hide();
        $('.ingenico-settings, .ingenico-header').addClass('install');
        //$('.installation-view').show();
        //$('.settings-form').hide();
        $('.account-creation-progress .progress-half').addClass('active');

        // Header links
        $('.header-links').show();
        $('.create-account.creation').hide();
        $('.create-account.configuration').show();
        $('.create-account-form .panel-footer').show();
    },
    
    createAccount: function () {
        $('.installation-options').hide();
        $('.create-account-form').show();
        $('.ingenico-settings, .ingenico-header').addClass('install');
        $('.installation-view').show();
        $('.settings-form').hide();
        $('.account-creation-progress .progress-half').addClass('active');

        // Header links
        $('.header-links').show();
        $('.create-account.creation').hide();
        $('.create-account.configuration').show();
        $('.create-account-form .panel-footer').show();

        $('#migration-frm').hide();
    },
    
    connectExistingAccount: function () {
        $('.installation-options').hide();
        $('.settings-form').css('display', 'grid');
        $('.ingenico-header .btn-toolbar').show();
        $('.ingenico-settings, .ingenico-header').removeClass('install');
        $('.installation-view').hide();
        $('.registration-complete').hide();
        $('.account-creation-progress, .ingenico-header').removeClass('done');
        $('.header-links').show();

        // Header links
        $('.create-account.creation').show();
        $('.create-account.configuration').hide();
    },

    backToInstallation: function (e) {
        if (e) {
            e.preventDefault();
        }

        $('.installation-view').show();
        $('.installation-options').show();
        $('.create-account-form').hide();
        $('.account-creation-progress .progress-half').removeClass('active');

        // Header links
        $('.header-links').show();
        $('.create-account.creation').hide();
        $('.create-account.configuration').show();
    },

    registerAccount: function (e) {
        if ($('.installation-form [name=company_name]').val().length > 0 &&
            this.validateEmail($('.installation-form [name=account_email]').val()) &&
            $('.installation-form #agreement').is(":checked")) {
            e.preventDefault();
            $.ajax(ingenico_ajax_url, {
                'data': {
                    method: 'register_account',
                    account_info: $('.installation-form').serialize()
                },
                'dataType': 'json'
            }).success(function(data) {
                $('.installation-form').hide();
                $('.registration-complete').css('display', 'inline-block');
                $('.account-creation-progress .progress-half').removeClass('active');
                $('.account-creation-progress, .ingenico-header').addClass('done');
            });
        } else {
            return false;
        }
    },

    validateEmail: function (email) {
        var pattern = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

        return $.trim(email).match(pattern) ? true : false;
    },

    addCountries: function () {
        var countries = [];
        var openinvoice_countries = [];
        $('#countries-list [type=checkbox]').each(function () {
            if (this.checked) {
                const country_code = $(this).val(),
                    label = $(this).closest('.label-container').text();

                // Get country name of OpenInvoice
                if (['SE', 'FI', 'DK', 'NO','DE', 'NL'].indexOf(country_code) > -1) {
                    openinvoice_countries.push(label);
                }

                countries.push(country_code);

            }
        }).promise().done( function() {
            if ((countries.indexOf('DE') > -1 || countries.indexOf('NL') > -1) && typeof window.OPENINVOICE_METHOD === 'undefined') {
                let label = $('#openinvoice_selected2').html();
                $('#openinvoice_selected2').html(label.replace('%country%', openinvoice_countries.join(', ')));

                Ingenico.closeModal(false, function () {
                    Ingenico.openModal('openinvoice-select-payment-modal');
                });

            } else {
                Ingenico.fetchPaymentMethods(countries, window.OPENINVOICE_METHOD);
            }
        } );
    },

    fetchPaymentMethods: function (countries, openinvoice_method) {
        return $.ajax(ingenico_ajax_url, {
            'method': 'POST',
            'data': {
                countries: countries,
                method: 'fetch_methods_by_countries',
                openinvoice: openinvoice_method
            },
            'dataType': 'json'
        }).success(function(data) {
            $('#selected_payment_methods').html(data);
            Ingenico.closeModal('countries-list');
        });
    },

    /**
     * Add message to migration log
     * @param message
     */
    addMigrationLog: function(message) {
        let el = $('#migration-logs');
        el.append(message + "\r\n");
        el.scrollTop(el[0].scrollHeight - el.height());
    },

    /**
     * Call AJAX Request
     * @param action
     * @param callback
     * @return {*}
     */
    doMigrationAction: function(action, callback) {
        return $.ajax(window.migration_ajax_url, {
            data: {
                action: action
            },
            dataType: 'json'
        }).done(function(data) {
            data.data.forEach(function(item, index, arr) {
                Ingenico.addMigrationLog(item);
            });

            if (!data.success) {
                callback(data.data, data);
            } else {
                callback(null, data);
            }

        }).fail(function (jqXHR, textStatus, errorThrown) {
            Ingenico.addMigrationLog(JSON.stringify(errorThrown));
            callback(errorThrown, {});
        });
    },

    /**
     * Start Migration
     */
    doMigrate: function () {
        async.series({
            step1: function(callback) {
                Ingenico.doMigrationAction('step1', callback);
            },
            step2: function(callback) {
                Ingenico.doMigrationAction('step2', callback);
            },
            step3: function(callback) {
                Ingenico.doMigrationAction('step3', callback);
            },
            step4: function(callback) {
                Ingenico.doMigrationAction('step4', callback);
            },
            step5: function (callback) {
                Ingenico.addMigrationLog('Import Aliases...');
                Ingenico.doMigrationAction('aliases_info', function (err, data) {
                    var aliases = data.aliases,
                        aliases_count = aliases.length,
                        aliases_imported = 0;

                    // Process Aliases
                    async.eachSeries(aliases, function(alias_id, callback1) {
                        Ingenico.addMigrationLog('Import alias #' + alias_id);

                        // Import Alias
                        $.ajax(window.migration_ajax_url, {
                            data: {
                                action: 'import_alias',
                                alias_id: alias_id
                            },
                            dataType: 'json'
                        }).done(function(data1) {
                            Ingenico.addMigrationLog('Alias #' + alias_id + ' was imported');
                        }).always(function () {
                            aliases_imported++;
                            console.log(aliases_imported);
                            console.log(aliases_count);

                            const value1 = Math.floor(aliases_imported / aliases_count * 100);
                            $('#aliases_progress').css('width', value1 + '%').attr('aria-valuenow', value1);

                            callback1();
                        });
                    }, function(err) {
                        if (err) {
                            console.log(err);
                        }

                        callback();
                    });
                });
            },
            step6: function (callback) {
                Ingenico.doMigrationAction('finish', callback);
            }
        }, function(err, results) {
            // results is now equal to: {one: 1, two: 2}
            console.log(results);
            Ingenico.addMigrationLog('Finished');
        });
    }
};

$(function() {
    Ingenico.init();
});

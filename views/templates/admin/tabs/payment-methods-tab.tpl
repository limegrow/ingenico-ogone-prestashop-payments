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
<div id="payment_methods" class="tab-pane">
    <h1>{l s='form.payment_methods.title' mod='ingenico_epayments'}</h1>
    <h2 class="col-lg-12">{l s='form.payment_methods.label.geography' mod='ingenico_epayments'}</h2>
    <p class="selected_countries">
        {l s='form.payment_methods.label.choose_countries' mod='ingenico_epayments'}
    </p>
    <p class="col-lg-12">
        <button type="button" class="ingenico-btn modal-link" data-modal-id="countries-list">{l s='form.payment_methods.button.fetch' mod='ingenico_epayments'}</button>
    </p>
    <div id="selected_payment_methods">
        {include file="$template_dir/admin/selected-payment-methods.tpl"}
    </div>
    <p class="col-lg-12">
        <button type="button" class="ingenico-btn modal-link" data-modal-id="payment-methods-list">{l s='form.payment_methods.button.add' mod='ingenico_epayments'}</button>
    </p>
    <input type="hidden" name="generic_country" value="{$generic_country|escape}">
    <h2 class="col-lg-12">
        {l s='Blank payment methods' mod='ingenico_epayments'}
    </h2>
    <p>
        {l s='Add payment method' mod='ingenico_epayments'}
        <br/>
        {l s='Client will be redirected to payment page with given payment method preselected' mod='ingenico_epayments'}
        <br/>
        {l s='Please, make sure that payment method you are adding is activated and configured in your Ingenico backoffice' mod='ingenico_epayments'}
    </p>
    <textarea name="flex_methods" id="flex_methods" style="display: none;">{$flex_methods nofilter}</textarea>
    <div id="jsGrid"></div>
    <script>
        var jsGridData = JSON.parse($('#flex_methods').val())

        $("#jsGrid").jsGrid({
            width: "100%",
            height: "300px",
            inserting: true,
            editing: true,
            sorting: true,
            paging: false,
            data: jsGridData,

            fields: [
                { name: "title", type: "text", title: "Title", width: 150, validate: "required" },
                { name: "pm", type: "text", title: "PM", width: 150, validate: "required" },
                { name: "brand", type: "text", title: "Brand", width: 150, validate: "required" },
                {
                    name: "img",
                    title: "Logo",
                    sorting: false,
                    itemTemplate: function(val, item) {
                        if (!val) {
                            return '';
                        } else {
                            val = '{$uploads_dir nofilter}' + val;
                            return $("<img>").attr('src', val).css({ height: 50, width: 50 });
                        }
                    },
                    insertTemplate: function() {
                        this.insertControl = $('<input>').prop('type', 'file');
                        return this.insertControl;
                    },
                    insertValue: function() {
                        return this.insertControl[0].files[0];
                    },
                    align: 'center',
                    width: 120
                },
                { type: 'control' }
            ],
            controller: {
                insertItem: function(insertingItem) {
                    console.log(insertingItem);
                    var formData = new FormData();
                    formData.append('title', insertingItem.title);
                    formData.append('pm', insertingItem.pm);
                    formData.append('brand', insertingItem.brand);

                    if (insertingItem.img instanceof File) {
                        formData.append('img[]', insertingItem.img, insertingItem.img.name);
                    } else {
                        insertingItem.img = null;
                    }

                    return $.ajax({
                        method: 'post',
                        type: 'POST',
                        url: ingenico_flex_upload_url,
                        data: formData,
                        contentType: false,
                        processData: false
                    });
                },
            },
            onRefreshed: function (grid) {
                console.log('onRefreshed');
                var data = $("#jsGrid").jsGrid("option", "data");
                $('#flex_methods').val(JSON.stringify(data));
            },
            onItemUpdated: function () {
                console.log('onItemUpdated');
                var data = $("#jsGrid").jsGrid("option", "data");
                $('#flex_methods').val(JSON.stringify(data));
            }
        });
    </script>
</div>
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
<div id="openinvoice-choose-country-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">
                    {l s='modal.openinvoice.title' mod='ingenico_epayments'}
                </h3>
            </div>
            <div class="modal-body">
                <ul>
                    <li>{l s='modal.openinvoice.label1' mod='ingenico_epayments'}</li>
                </ul>
                <div class="country-list" style="display: inline-block">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <p class="radio">
                                <input type="radio" id="openinvoice_country_nl" name="openinvoice_country" value="NL" checked="checked">
                                <label for="openinvoice_country_nl">
                                    {l s='modal.openinvoice.country_nl' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio">
                                <input type="radio" id="openinvoice_country_de" name="openinvoice_country" value="DE">
                                <label for="openinvoice_country_de">
                                    {l s='modal.openinvoice.country_de' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio" style="display: none;">
                                <input type="radio" id="openinvoice_country_se" name="openinvoice_country" value="SE">
                                <label for="openinvoice_country_se">
                                    {l s='modal.openinvoice.country_se' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio" style="display: none;">
                                <input type="radio" id="openinvoice_country_fi" name="openinvoice_country" value="FI">
                                <label for="openinvoice_country_fi">
                                    {l s='modal.openinvoice.country_fi' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio" style="display: none;">
                                <input type="radio" id="openinvoice_country_dk" name="openinvoice_country" value="DK">
                                <label for="openinvoice_country_dk">
                                    {l s='modal.openinvoice.country_dk' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio" style="display: none;">
                                <input type="radio" id="openinvoice_country_no" name="openinvoice_country" value="NO">
                                <label for="openinvoice_country_no">
                                    {l s='modal.openinvoice.country_no' mod='ingenico_epayments'}
                                </label>
                            </p>
                            <p class="radio">
                                <input type="radio" id="openinvoice_country_another" name="openinvoice_country" value="">
                                <label for="openinvoice_country_another">
                                    {l s='modal.openinvoice.another' mod='ingenico_epayments'}
                                </label>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="apply" data-dismiss="modal" aria-label="Apply">
                    {l s='button.apply' mod='ingenico_epayments'}
                </button>
            </div>
        </div>
    </div>
</div>

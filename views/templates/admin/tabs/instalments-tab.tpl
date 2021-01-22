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
<div id="installments" class="tab-pane">
    <h1>Instalments</h1>
    <div class="form-group col-lg-12">
        <label class="switch test-live-toggle">
            <input type="checkbox" name="instalments_enabled" data-toggle-name="instalments-settings" value="1" {if $instalments_enabled}checked="checked"{/if}>
            <span class="slider round"></span>
        </label>
        <div class="toggle-label">Allow clients to pay in multiple instalments</div>
    </div>
    <div class="instalments-settings" {if !$instalments_enabled}style="display: none"{/if}>
        <div class="form-group col-lg-12">
            <h3>Rules</h3>
            <p class="radio">
                <input type="radio" data-toggle-name="installments_rules" name="instalments_type" id="fixed_rules" value="FIXED" {if $instalments_type === 'FIXED'}checked="checked"{/if}>
                <label for="fixed_rules">
                    Fixed rules for all clients
                </label>
            </p>
            <p class="radio">
                <input type="radio" data-toggle-name="installments_rules" name="instalments_type" id="flexible_rules" value="FLEX" {if $instalments_type === 'FLEX'}checked="checked"{/if}>
                <label for="flexible_rules">
                    Flexible rules
                </label>
            </p>
        </div>
        <div class="installment-row col-lg-12">
            <a class="modal-link modal-link-how-to" data-modal-id="instalments-modal">What is the difference?</a>
        </div>
        <div class="form-group col-lg-12">
            <h3>Split orders into</h3>
            <div class="installment-row">
                <div class="installments_rules installments_rulesFIXED" {if $instalments_type === 'FLEX'}style="display: none"{/if}>
                    <input class="form-control" type="number" size="5" name="instalments_fixed_instalments" value="{$instalments_fixed_instalments}">
                    <span class="suffix">instalments</span>
                </div>
                <div class="installments_rules installments_rulesFLEX" {if $instalments_type === 'FIXED'}style="display: none"{/if}>
                    <input type="hidden" name="instalments_flex_instalments_min" value="{$instalments_flex_instalments_min}">
                    <input type="hidden" name="instalments_flex_instalments_max" value="{$instalments_flex_instalments_max}">
                    <div class="range-bar" id="installments_amount_range"></div>
                    <span class="suffix">instalments</span>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-12">
            <h3>Period between each instalment</h3>
            <div class="installment-row">
                <div class="installments_rules installments_rulesFIXED" {if $instalments_type === 'FLEX'}style="display: none"{/if}>
                    <input class="form-control" type="number" size="5" name="instalments_fixed_period" value="{$instalments_fixed_period}">
                    <span class="suffix">days</span>
                </div>
                <div class="installments_rules installments_rulesFLEX" {if $instalments_type === 'FIXED'}style="display: none"{/if}>
                    <input type="hidden" name="instalments_flex_period_min" value="{$instalments_flex_period_min}">
                    <input type="hidden" name="instalments_flex_period_max" value="{$instalments_flex_period_max}">
                    <div class="range-bar" id="installments_period_range"></div>
                    <span class="suffix">days</span>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-12">
            <h3>First payment</h3>
            <div class="installment-row">
                <div class="installments_rules installments_rulesFIXED" {if $instalments_type === 'FLEX'}style="display: none"{/if}>
                    <input class="form-control" type="number" size="5" name="instalments_fixed_firstpayment" value="{$instalments_fixed_firstpayment}">
                    <span class="suffix">%</span>
                </div>
                <div class="installments_rules installments_rulesFLEX" {if $instalments_type === 'FIXED'}style="display: none"{/if}>
                    <input type="hidden" name="instalments_flex_firstpayment_min" value="{$instalments_flex_firstpayment_min}">
                    <input type="hidden" name="instalments_flex_firstpayment_max" value="{$instalments_flex_firstpayment_max}">
                    <div class="range-bar" id="installments_first_range"></div>
                    <span class="suffix">of the order total</span>
                </div>
            </div>
        </div>
        <div class="form-group col-lg-12">
            <h3>Minimal payment</h3>
            <div class="installment-row">
                <input class="form-control" type="number" size="5" name="instalments_fixed_minpayment" value="{$instalments_fixed_minpayment}">
                <span class="suffix">€</span>
            </div>
        </div>
    </div>
</div>
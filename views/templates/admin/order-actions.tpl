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
<input type="hidden" id="ingenico_order_id" name="ingenico_order_id" value="{$order_id|escape:'htmlall':'UTF-8'}"/>
<input type="hidden" id="ingenico_pay_id" name="ingenico_pay_id" value="{$pay_id|escape:'htmlall':'UTF-8'}"/>

{if $can_capture}
    <a class="btn btn-default" href="#" data-toggle="modal" data-target="#captureModal">
        <i class="icon-print"></i>
        {l s='order.action.capture' mod='ingenico_epayments'}
    </a>

    <!-- Capture Modal -->
    <div class="modal fade" id="captureModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{l s='order.action.capture' mod='ingenico_epayments'}</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="capture_amount" class="col-sm-2 control-label">{l s='order.action.capture_amount' mod='ingenico_epayments'}</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" id="capture_amount" name="capture_amount" value="{($capture_amount)|floatval}" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-close" data-dismiss="modal">{l s='button.close' mod='ingenico_epayments'}</button>
                    <button id="process-capture" type="button" class="btn btn-primary">{l s='order.action.capture' mod='ingenico_epayments'}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Capture Confirmation -->
    <div id="capture-confirmation-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h3 class="modal-title">
                        {l s='order.action.confirmation' mod='ingenico_epayments'}
                    </h3>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>{l s='order.action.capture_confirmation' mod='ingenico_epayments'}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-close" data-dismiss="modal" aria-label="Close">
                        {l s='button.close' mod='ingenico_epayments'}
                    </button>

                    <button id="process-capture-action" class="btn btn-primary">
                        {l s='order.action.capture' mod='ingenico_epayments'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}

{if $can_cancel}
    <a class="btn btn-default" href="#" data-toggle="modal" data-target="#cancel-confirmation-modal">
        <i class="icon-print"></i>
        {l s='order.action.cancel' mod='ingenico_epayments'}
    </a>

    <!-- Cancel Confirmation -->
    <div id="cancel-confirmation-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h3 class="modal-title">
                        {l s='order.action.confirmation' mod='ingenico_epayments'}
                    </h3>
                </div>
                <div class="modal-body">
                    {l s='order.action.cancellation_confirmation' mod='ingenico_epayments'}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-close" data-dismiss="modal" aria-label="Close">
                        {l s='button.close' mod='ingenico_epayments'}
                    </button>

                    <button id="process-cancel-action" class="btn btn-primary">
                        {l s='order.action.cancel' mod='ingenico_epayments'}
                    </button>
                </div>
            </div>
        </div>
    </div>
{/if}

{if $can_refund}
    <a class="btn btn-default" href="#" data-toggle="modal" data-target="#refundModal">
        <i class="icon-print"></i>
        {l s='order.action.refund_btn' mod='ingenico_epayments'}
    </a>

    <!-- Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">{l s='order.action.refund' mod='ingenico_epayments'}</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label for="refund_amount" class="col-sm-2 control-label">{l s='order.action.refund_amount' mod='ingenico_epayments'}</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control" id="refund_amount" name="refund_amount" value="{($refund_amount)|floatval}" />
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-close" data-dismiss="modal">{l s='button.close' mod='ingenico_epayments'}</button>
                    <button id="process-perform-refund" type="button" class="btn btn-primary">{l s='order.action.refund' mod='ingenico_epayments'}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Confirmation -->
    <div id="refund-confirmation-modal" class="ingenico-modal modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h3 class="modal-title">
                        {l s='order.action.confirmation' mod='ingenico_epayments'}
                    </h3>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>{l s='order.action.refund_confirmation' mod='ingenico_epayments'}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-close" data-dismiss="modal" aria-label="Close">
                        {l s='button.close' mod='ingenico_epayments'}
                    </button>

                    <button id="process-refund-action" class="btn btn-primary">
                        {l s='order.action.refund' mod='ingenico_epayments'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {include file="$template_dir/admin/modals/refund-failed-modal.tpl"}
{/if}

<script>
var ingenico_api_url = '{$ingenico_api}';
</script>

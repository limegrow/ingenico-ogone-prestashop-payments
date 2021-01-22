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
<div class="col-lg-7">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-credit-card"></i> {l s='ingenico.payment.info' mod='ingenico_epayments'}
        </div>
        <div id="ingenico_actions">
            <table class="table table-responsive">
                {if !empty($pay_id)}
                <tr>
                    <td>{l s='order.payid' mod='ingenico_epayments'} </td>
                    <td>{$pay_id|escape:'htmlall':'UTF-8'} </td>
                </tr>
                {/if}
                {if !empty($pay_id_sub)}
                    <tr>
                        <td>{l s='order.payidsub' mod='ingenico_epayments'} </td>
                        <td>{$pay_id_sub|escape:'htmlall':'UTF-8'} </td>
                    </tr>
                {/if}
                {if !empty($payment_method)}
                <tr>
                    <td>{l s='order.payment_method' mod='ingenico_epayments'} </td>
                    <td>{$payment_method|escape:'htmlall':'UTF-8'} </td>
                </tr>
                {/if}
                {if !empty($status)}
                <tr>
                    <td>{l s='order.status' mod='ingenico_epayments'} </td>
                    <td>{$status|escape:'htmlall':'UTF-8'} </td>
                </tr>
                {/if}
                {if !empty($brand)}
                <tr>
                    <td>{l s='order.brand' mod='ingenico_epayments'} </td>
                    <td>{$brand|escape:'htmlall':'UTF-8'} </td>
                </tr>
                {/if}
                {if !empty($card_no)}
                <tr>
                    <td>{l s='order.card_no' mod='ingenico_epayments'} </td>
                    <td>{$card_no|escape:'htmlall':'UTF-8'} </td>
                </tr>
                {/if}
                {if !empty($cn)}
                    <tr>
                        <td>{l s='order.customer_name' mod='ingenico_epayments'} </td>
                        <td>{$cn|escape:'htmlall':'UTF-8'} </td>
                    </tr>
                {/if}
            </table>
        </div>
    </div>
</div>

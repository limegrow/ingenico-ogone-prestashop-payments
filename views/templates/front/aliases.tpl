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
{extends file='customer/page.tpl'}

{block name='page_content_top' prepend}
  <h1 class="page-heading bottom-indent">{l s='My payment methods' mod='ingenico_epayments'}</h1>
{/block}

{block name='page_content'}
{capture name=path}
{if isset($navigationPipe)}
  <a title="{l s='My account' mod='ingenico_epayments'}" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">{l s='My account' mod='ingenico_epayments'}</a>
  <span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>
  {l s='My payment methods' mod='ingenico_epayments'}
{/if}
{/capture}

{if $aliases}
  <p class="cards-title"><i class="icon-credit-card"></i> {l s='My active payment methods' mod='ingenico_epayments'}</p>
  <p class="info-title"></p>
  <div class="ingenico-aliases row clearfix card">
  {foreach $aliases as $alias}
    <div class="col-lg-4 col-md-6">
        <div class="inner-card">
          <img class="ingenico-alias-card-logo {$alias->getBrand()|escape}" src="{$alias->getEmbeddedLogo() nofilter}" alt="{$alias->getTranslatedName()|escape}">
          <p class="ingenico-alias-cardno">
          <span class="card-info-title">
              {l s='Card number' mod='ingenico_epayments'}
          </span>
            {$alias.cardno|escape:'html':'UTF-8'}
          </p>
          <p class="ingenico-alias-cn">
          <span class="card-info-title">
            {l s='Card owner' mod='ingenico_epayments'}
          </span>
            {$alias.cn|escape:'html':'UTF-8'}
          </p>
          {if $alias.ed}
            <span class="ingenico-alias-ed">
            <span class="card-info-title">
              {l s='Expiration date' mod='ingenico_epayments'}
            </span>
            {$alias.ed|escape:'html':'UTF-8'}
            </span>
          {/if}

          <div class="ingenico-alias-delete">
            <a href="{$alias.delete_link|escape:'html':'UTF-8'}" onclick="return confirm('{l s='Are you sure?' mod='ingenico_epayments'}');">
              <i class="material-icons">delete</i>{l s='Delete' mod='ingenico_epayments'}
            </a>
          </div>
        </div>
    </div>
  {/foreach}
  </div>
{/if}
 {*
  <iframe src="{$inline_frame_url|escape:'quotes':'UTF-8'}" style="min-width: 400px; min-height: 500px;"></iframe>
*}
{/block}

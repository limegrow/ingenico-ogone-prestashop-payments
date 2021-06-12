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
{if $alias_page_link}
<a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" id="ogone-alias-link" href="{$alias_page_link|escape:'htmlall':'UTF-8'}">
  <span class="link-item"><i class="material-icons">&#xE870;</i>{l s='My payment methods' mod='ogone'}</span>
</a>
{/if}

{*
* 2007-2017 PrestaShop
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
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{block name='history'}
	{capture name=path}
		<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			{l s='My account'}
		</a>
		<span class="navigation-pipe">{$navigationPipe}</span>
		<span class="navigation_page">{l s='Bookings'}</span>
	{/capture}
	{block name='errors'}
		{include file="$tpl_dir./errors.tpl"}
	{/block}
	{block name='history_heading'}
		<h1 class="page-heading bottom-indent">{l s='Bookings'}</h1>
		<a href="https://wa.me/243813616956?text=Bonjour, je vous contacte pour la chambre merci!" target="_blank"
			style="background-color:#25D366; color:white; border:none; padding:10px 16px; border-radius:5px; display:inline-flex; align-items:center; font-weight:bold; text-decoration:none;">
			<img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp"
				style="height:20px; vertical-align:middle; margin-right:8px;">
			<span>{l s='Contact via whatsapp' mod='bankwire'}</span>
		</a>
		<hr>
	{/block}

	<p class="info-title">{l s='Here are the orders you\'ve placed since your account was created.'}</p>

	{if $slowValidation}
		<p class="alert alert-warning">
			{l s='If you have just placed an order, it may take a few minutes for it to be validated. Please refresh this page if your order is missing.'}
		</p>
	{/if}
	<div class="block-center" id="block-history">
		{if $orders && count($orders)}
			{block name='bookings_list'}
				<table id="order-list" class="table table-bordered footab">
					<thead>
						<tr>
							<th class="first_item" data-sort-ignore="true">{l s='Order reference'}</th>
							<th class="item">{l s='Date'}</th>
							<th data-hide="phone" class="item">{l s='Total price'}</th>
							{if isset($adv_active)}
								<th data-hide="phone" class="item">{l s='Due Price'}</th>
							{/if}
							<th data-sort-ignore="true" data-hide="phone,tablet" class="item">{l s='Payment'}</th>
							<th class="item">{l s='Status'}</th>

							<th data-sort-ignore="true" data-hide="phone,tablet" class="item">{l s='Invoice'}</th>
							<th data-sort-ignore="true" data-hide="phone,tablet" class="last_item">&nbsp;</th>
							{block name='displayHistoryTableHeading'}
								{hook h='displayHistoryTableHeading'}
							{/block}
						</tr>
					</thead>
					<tbody>
						{foreach from=$orders item=order name=myLoop}
							<tr
								class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
								{block name='booking_reference'}
									<td class="history_link bold">
										{* By webkul- no downloadable products *}
										{* {if isset($order.invoice) && $order.invoice && isset($order.virtual) && $order.virtual}
										<img class="icon" src="{$img_dir}icon/download_product.gif"	alt="{l s='Products to download'}" title="{l s='Products to download'}" />
									{/if} *}
										<a class="color-myaccount"
											href="{$link->getPageLink('order-detail', true, NULL, "id_order={$order.id_order|intval}"
						)|escape:'html':'UTF-8'}">
											{Order::getUniqReferenceOf($order.id_order)}
										</a>
									</td>
								{/block}
								{block name='booking_date'}
									<td data-value="{$order.date_add|regex_replace:"/[\-\:\ ]/":""}" class="history_date bold">
										{dateFormat date=$order.date_add full=0}
									</td>
								{/block}
								{block name='booking_total_price'}
									<td class="history_price" data-value="{$order.total_paid}">
										<span class="price">
											{displayPrice price=$order.total_paid currency=$order.id_currency no_utf8=false convert=false}
										</span>
									</td>
								{/block}
								{block name='booking_due_price'}
									{if isset($adv_active)}
										<td class="history_price" data-value="{$order.due_amount}">
											<span class="price">
												{displayPrice price=$order.due_amount currency=$order.id_currency no_utf8=false convert=false}
											</span>
										</td>
									{/if}
								{/block}
								{block name='booking_method'}
									<td class="history_method">{$order.payment|escape:'html':'UTF-8'}</td>
								{/block}
								{block name='booking_state'}
									<td {if isset($order.order_state)} data-value="{$order.id_order_state}" {/if} class="history_state">
										{if isset($order.order_state)}
											<span
												class="label{if isset($order.order_state_color) && Tools::getBrightness($order.order_state_color) > 128} dark{/if}"
												{if isset($order.order_state_color) && $order.order_state_color}
													style="background-color:{$order.order_state_color|escape:'html':'UTF-8'}; border-color:{$order.order_state_color|escape:'html':'UTF-8'};"
												{/if}>
												{if $order.current_state|in_array:$overbooking_order_states}
													{l s='Order Not Confirmed'}
												{else}
													{$order.order_state|escape:'html':'UTF-8'}
												{/if}
											</span>
										{/if}
									</td>
								{/block}
								{block name='booking_invoice'}
									<td class="history_invoice">
										{if (isset($order.invoice) && $order.invoice && isset($order.invoice_number) && $order.invoice_number) && isset($invoiceAllowed) && $invoiceAllowed == true}
											<a class="link-button"
												href="{$link->getPageLink('pdf-invoice', true, NULL, "id_order={$order.id_order}"
						)|escape:'html':'UTF-8'}" title="{l s='Invoice'}" target="_blank">
												<i class="icon-file-text large"></i>{l s='PDF'}
											</a>
										{else}
											-
										{/if}
									</td>
								{/block}
								{block name='booking_detail'}
									<td class="history_detail">
										<a class="btn btn-default button button-small"
											href="{$link->getPageLink('order-detail', true, NULL, "id_order={$order.id_order|intval}"
						)|escape:'html':'UTF-8'}">
											<span>
												{l s='Details'}<i class="icon-chevron-right right"></i>
											</span>
										</a>
										<!-- {if isset($opc) && $opc}
											<a class="link-button" href="{$link->getPageLink('order-opc', true, NULL, "submitReorder&id_order={$order.id_order|intval}")|escape:'html':'UTF-8'}" title="{l s='Reorder'}">
										{else}
											<a class="link-button" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order.id_order|intval}")|escape:'html':'UTF-8'}" title="{l s='Reorder'}">
										{/if}
											{if isset($reorderingAllowed) && $reorderingAllowed}
												<i class="icon-refresh"></i>{l s='Reorder'}
											{/if}
										</a> -->
										<!-- by webkul not to show reorder tab -->
									</td>
								{/block}
								{block name='displayHistoryTableRow'}
									{hook h='displayHistoryTableRow' id_order=$order.id_order}
								{/block}
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/block}
			<div id="block-order-detail" class="unvisible">&nbsp;</div>
		{else}
			<p class="alert alert-warning">{l s='You have not placed any orders.'}</p>
		{/if}
	</div>
	{block name='history_footer_links'}
		<ul class="footer_links clearfix">
			<li>
				<a class="btn btn-default button button-small"
					href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
					<span>
						<i class="icon-chevron-left"></i> {l s='Back to My account'}
					</span>
				</a>
			</li>
			<li>
				<a class="btn btn-default button button-small" href="{$base_dir}">
					<span><i class="icon-chevron-left"></i> {l s='Home'}</span>
				</a>
			</li>
		</ul>
	{/block}

{/block}
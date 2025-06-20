{*
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*}

{block name='header_hotel_block'}
	<div class="header-desc-container">
		<div class="header-desc-wrapper">
			<div class="header-desc-primary">
				<div class="container">
					<div class="row">
						<div class="col-md-offset-1 col-md-10 col-lg-offset-2 col-lg-8">
							<p class="header-desc-welcome">{l s='Bienvenu à' mod='hotelreservationsystem'}</p>
							<hr class="heasder-desc-hr-first" />
							<div class="header-desc-inner-wrapper">
								{block name='header_hotel_chain_name'}
									<h1 class="header-hotel-name">{$WK_HTL_CHAIN_NAME|escape:'htmlall':'UTF-8'}</h1>
								{/block}
								{block name='header_hotel_description'}
									<p class="header-hotel-desc">{$WK_HTL_TAG_LINE|escape:'htmlall':'UTF-8'}</p>
								{/block}
								<hr class="heasder-desc-hr-second" />
							</div>
						</div>
					</div>
					{block name='displayAfterHeaderHotelDesc'}
						{hook h="displayAfterHeaderHotelDesc"}
					{/block}
				</div>
			</div>
		</div>
	</div>
{/block}
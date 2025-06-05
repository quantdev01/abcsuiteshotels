{*
* 2021 Linea Gráfica
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
*  @author Linea Grafica
*}

<div class="col-md-2 left-column">
        <div class="headerConfig">
                <span class="imgconfig"><img src="{$img_path|escape:'htmlall':'UTF-8'}logo_pluglin.png"></span>
                <div class="clearfix"></div>
        </div>
        <div class="menu">
            {foreach from=$menu_pluglin key="option_pluglin" item="option_data"}
                <div class="menu_option {if $option_data.selected}selected{/if}"><a href="{$option_data.url}">{$option_data.name}</a></div>
            {/foreach}
        </div>
        <div class="clearfix"></div>
</div>
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
{include file="./header.tpl"}

<div class="panel pluglin_panel pluglin_steps welcome_pluglin">
    <div class="progress_bar progress_bar_1"></div>
    <div class="form-wrapper">
        <form>
            <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
            <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
            <input type="hidden" name="token" value="{Tools::getValue('token')}">

            {include file="./_partials/alert.tpl"}

            <p class="pluglin_title">{l s='We are going to configure your Pluglin module for Prestashop.' mod='pluglin'}</p>
            <p>{l s='Enter the access token of your Pluglin account.' mod='pluglin'}</p>
            <p class="pluglin_subtitle">{l s='Access token' mod='pluglin'}</p>
            <div class="inputToken">
                <input name="token_blarlo" value="{$token_blarlo}">
            </div>
            <p>{l s='If you don\'t have a Pluglin account yet, follow these steps first:' mod='pluglin'}</p>
            <ol>
                <li>{l s='You do' mod='pluglin'} <a href="{$url_panel}" target="_blank">{l s='click here' mod='pluglin'}</a> {l s='to go to Pluglin and create an account.' mod='pluglin'}</li>
                <li>{l s="Once you have logged into your Pluglin account, go to “Settings”." mod='pluglin'}</li>
                <li>{l s="Copy the access token to your account and paste it here." mod='pluglin'}</li>
            </ol>
            <div class="pluglin_submit">
                <button type="submit" name="sendToken" disabled>{l s="Continue" mod='pluglin'}</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $(".bootstrap.panel").hide();
    });
</script>

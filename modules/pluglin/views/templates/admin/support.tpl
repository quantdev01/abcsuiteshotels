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

<div class="pluglin_panel row">
    {include file="./_partials/menu_left.tpl"}
    <div class="col-md-10 center-column">
        <div class="panel control_panel support_pluglin">
            <div class="panel-heading">
                <p class="pluglin_title">
                    {l s='Contact with Pluglin' mod='pluglin'}
                </p>
            </div>
            <div class="clearfix">
                {if count($messages_pluglin)==0}

                    {include file="./_partials/alert.tpl"}

                    <p>{l s='Send us a message if you have any problem using Pluglin with your Prestashop store and we will respond as soon as possible.' mod='pluglin'}</p>
                    <form method="post" action="{$menu_pluglin.support.url}">
                        <p class="pluglin_subtitle">{l s='Message' mod='pluglin'}</p>
                        <textarea name='message'></textarea>
                        <div class="btn-container">
                            <button type="submit" class="btn btn-default btn-block clearfix">{l s='Send Message' mod='pluglin'}</button>
                        </div>
                    </form>
                {else}
                    {foreach from=$messages_pluglin item="message"}
                        <div class="alert alert-{$message.type}">
                            <strong>{$message.content}</strong>
                        </div>
                    {/foreach}
                {/if}
            </div>
            <div class="panel-heading">
                <p class="pluglin_title">
                    {l s='Version' mod='pluglin'}
                </p>
            </div>
            <div>
                <p>{l s='Pluglin module for Prestashop v' mod='pluglin'} {$version_module}.</p>
            </div>
        </div>
    </div>
</div>
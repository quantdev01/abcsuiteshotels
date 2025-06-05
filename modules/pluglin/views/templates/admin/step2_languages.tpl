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

<div class="panel pluglin_panel pluglin_steps">
    <div class="progress_bar progress_bar_2"></div>
    <div class="form-wrapper">
        <p class="pluglin_title">{l s='Indicate the languages' mod='pluglin'}</p>
        <p>{l s='Configure the languages to which you want to translate the contents of your store' mod='pluglin'}</p>

        {include file="./_partials/alert.tpl"}

        <p class="pluglin_subtitle">{l s='Base language' mod='pluglin'}</p>
        <div class="pluglin_default_lang">
            <img src="/img/l/{$id_default_language}.jpg">
            <span>{$default_language}</span>
        </div>
        <form id="languages_form">
            <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
            <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
            <input type="hidden" name="token" value="{Tools::getValue('token')}">

        <div id="language_restriction_div">
            <table class="table">
                <tr>
                    <td>
                        <p class="pluglin_subtitle">{l s='Unselected languages' mod='pluglin'}</p>
                        <select id="language_select_1" class="input-large" multiple>
                            {foreach from=$languages_options.unselected item='language'}
                                <option value="{$language.id_lang|intval}">&nbsp;{$language.name|escape}</option>
                            {/foreach}
                        </select>
                        <a id="language_select_add" class="btn btn-default btn-block clearfix" >{l s='Add' mod='pluglin'} <i class="icon-arrow-right"></i></a>
                    </td>
                    <td>
                        <p class="pluglin_subtitle">{l s='Selected languages' mod='pluglin'}</p>
                        <select name="language_select[]" id="language_select_2" class="input-large" multiple>
                            {foreach from=$languages_options.selected item='language'}
                                <option value="{$language.id_lang|intval}">&nbsp;{$language.name|escape}</option>
                            {/foreach}
                        </select>
                        <a id="language_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='pluglin'} </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="pluglin_submit">
            <button type="submit" name="sendLanguages">{l s='Continue' mod='pluglin'}</button>
        </div>
        </form>
    </div>
</div>

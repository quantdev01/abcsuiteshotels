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
        <div class="panel control_panel configuration_pluglin">
            <div class="panel-heading">
                <p class="pluglin_title">
                    {l s='Your Pluglin account' mod='pluglin'}
                </p>
            </div>
            {if $connected}
                <div class="status_pluglin connected">
                    {l s='connected' mod='pluglin'}
                </div>
            {else}
                <div class="status_pluglin disconnected">
                    {l s='disconnected' mod='pluglin'}
                </div>
            {/if}
            <div class="form-wrapper clearfix">
                <p>
                    {l s='Enter the access token of your Pluglin account.' mod='pluglin'}
                    {l s='If you don\'t have one, you can create it right now by following' mod='pluglin'}
                    <a href="{$url_panel}" target="_blank">{l s='this link' mod='pluglin'}</a>
                    {l s='and get your access token to include it here' mod='pluglin'}:
                </p>
                <p class="pluglin_subtitle">{l s='Access token' mod='pluglin'}</p>
                <form>
                    <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
                    <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
                    <input type="hidden" name="token" value="{Tools::getValue('token')}">
                    <div class="inputToken">
                        <input name="token_blarlo"  value="{$token_blarlo}">
                    </div>
                    {if $connected}
                        <div class="form-wrapper clearfix">
                            <p class="pluglin_subtitle">{l s='Your plan' mod='pluglin'}</p>
                            <p>
                                {l s='Your subscription is' mod='pluglin'}
                                <strong>{$price}</strong>
                                {l s='€/month' mod='pluglin'}
                                {if $date_renew!=''}
                                {l s='and it is renewed on' mod='pluglin'}
                                <strong>{$date_renew}</strong>
                                {/if}.
                            </p>
                            <p>
                                {l s='You are using' mod='pluglin'}
                                <strong>
                                    {$used_languages}
                                    {l s='languages' mod='pluglin'}
                                </strong>
                                {l s='de' mod='pluglin'}
                                {if $num_languages}
                                    <strong>{$num_languages} {l s='languages' mod='pluglin'}</strong>.
                                {else}
                                    <strong>{l s='unlimited languages' mod='pluglin'}</strong>.
                                {/if}
                                <br/>
                                {l s='You have used' mod='pluglin'}
                                <strong>{$used_words}</strong>
                                {l s='words of' mod='pluglin'}
                                <strong>{$available_words}</strong>
                                {l s='available' mod='pluglin'}.
                            </p>
                            <p><a href="https://app.pluglin.com/settings" target="_blank">{l s='Manage your subscription in Pluglin' mod='pluglin'}</a></p>
                        </div>
                    {/if}
                    <div class="btn-container">
                        <button type="submit" class="btn btn-default btn-block clearfix" name="sendToken">{l s="Continue" mod='pluglin'}</button>
                    </div>
                </form>
            </div>
            <div class="form-wrapper">
                <div class="panel-heading">
                    <p class="pluglin_title">
                        {l s='Languages' mod='pluglin'}
                    </p>
                </div>
                <p class="pluglin_subtitle">{l s="Idioma base" mod="pluglin"}</p>
                <div class="pluglin_default_lang">
                    <img src="/img/l/{$id_default_language}.jpg">
                    <span>{$default_language}</span>
                </div>
                <form id="configure_languages">
                    <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
                    <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
                    <input type="hidden" name="token" value="{Tools::getValue('token')}">
                    <input type="hidden" name="configuration" value="1">
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
                    <div class="btn-container">
                        <button type="submit" class="btn btn-default btn-block clearfix" name="configureLanguages">{l s='Continue' mod='pluglin'}</button>
                    </div>
                </form>
            </div>
            <div class="form-wrapper">
                <div class="panel-heading">
                    <p class="pluglin_title">
                        {l s='Autopilot' mod='pluglin'}
                    </p>
                </div>
                <form id="configure_syncs">
                    <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
                    <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
                    <input type="hidden" name="token" value="{Tools::getValue('token')}">
                    <input type="hidden" name="configuration" value="1">

                    <label class="automatic_sync">
                        <input type="checkbox" name="automatic_sync" value="1" {if $automatic_sync}checked{/if}>
                        <span><img src="{$img_path|escape:'htmlall':'UTF-8'}check.png" class="checkbox-checked"></span>
                        <span>{l s='Synchronize content and translations automatically:' mod='pluglin'}</span>
                    </label>

                    <div class="configure_languages_howmany">
                        <span>{l s='Every' mod='pluglin'}</span>
                        <select class="frequency_sync" name="frequency_sync">
                            <option {if $frequency_sync == 6}selected{/if}>6</option>
                            <option {if $frequency_sync == 12}selected{/if}>12</option>
                            <option {if $frequency_sync == 24}selected{/if}>24</option>
                            <option {if $frequency_sync == 48}selected{/if}>48</option>
                        </select>
                        <span>{l s='hours' mod='pluglin'}</span>
                    </div>
                    <div class="btn-container">
                        <button type="submit" class="btn btn-default btn-block clearfix" name="configureSync">{l s='Continue' mod='pluglin'}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
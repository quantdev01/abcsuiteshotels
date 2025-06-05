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
    <div class="progress_bar progress_bar_4"></div>
    <div class="form-wrapper">
        <p class="pluglin_title">{l s='Your subscription in Pluglin' mod='pluglin'}</p>
        <p>
            {l s='According to the volume of words of your store ' mod='pluglin'}
            (<strong>{$words} {l s='palabras' mod='pluglin'}</strong>)
            {l s='and the number of languages to translate, the total words to manage are:' mod='pluglin'}</p>
        <table class="pluglin_languages_selecteds">
            {foreach from=$languages item="lang"}
                {if $lang['id_lang']!=$lang_default}
                    {if isset($langs_selected[$lang['id_lang']])}
                    <tr class="pluglin_language">
                        <td class="check_language">
{*                            {if isset($langs_selected[$lang['id_lang']])}
                                <img src="{$img_path|escape:'htmlall':'UTF-8'}check.png">
                            {/if}
*}                        </td>
                        <td class="name_language">
                            <div>
                                <img src="/img/l/{$lang['id_lang']}.jpg">
                                <span>{$lang['name']}</span>
                            </div>
                        </td>
                        <td class="words_language">
                            {$words} {l s='words' mod='pluglin'}
                        </td>
                    </tr>
                    {/if}
                {else}
                    <tr class="pluglin_language">
                        <td class="check_language">
                        </td>
                        <td class="name_language">
                            <div>
                                <img src="/img/l/{$lang['id_lang']}.jpg">
                                <span>{$lang['name']} ({l s='base' mod='pluglin'})</span>
                            </div>
                        </td>
                        <td class="words_language">
                            {$words} {l s='words' mod='pluglin'}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            {if $connected}
                <tr class="pluglin_language">
                    <td class="check_language">
                    </td>
                    <td class="name_language">
                        <div>
                            <img src="{$img_path|escape:'htmlall':'UTF-8'}pluglin_ico.png">
                            <span>{l s='Pluglin' mod='pluglin'}</span>
                        </div>
                    </td>
                    <td class="words_language">
                        {$used_words} {l s='words already in your Pluglin account' mod='pluglin'}
                    </td>
                </tr>
            {/if}

            <tr class="pluglin_language_totals">
                <td class="check_language"></td>
                <td class="name_language"><strong>{l s='Total' mod='pluglin'}</strong></td>
                <td class="words_language"><strong>{$total_words} {l s='words' mod='pluglin'}</strong></td>
            </tr>
        </table>
    <div>
    {if $connected}
        <div class="form-wrapper clearfix">
            <p class="pluglin_subtitle">
                {l s='Your plan' mod='pluglin'}
            </p>
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
                {l s='of' mod='pluglin'}
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
            <p><a href="{$url_panel}" target="_blank">{l s='Manage your subscription in Pluglin' mod='pluglin'}</a></p>
        </div>
    {/if}

{*    <div class="form-wrapper">*}
{*        <div>{l s='Hemos preseleccionado el plan de Pluglin que mejor se adapta a ti:' mod='pluglin'}</div>*}
{*        {foreach from=$plans item='plan' key='key'}*}
{*            <div id="plan_{$key}" data-key="{$key}" class="row plan">*}
{*                <div class="col-md-12 {if $plan['max_words']<$total_words}disabled{/if} {if $plan['id_pluglin_plan'] == $plan_selected}selected{/if}">*}
{*                    <span class="plan_prices">{$plan['price']}€ / {l s='month' mod='pluglin'}</span>*}
{*                    <span class="plan_text">*}
{*                        {if $plan['max_language']>0}*}
{*                            {$plan['max_language']} {l s='idiomas máximo (base + 1 traducción)' mod='pluglin'}.*}
{*                        {else}*}
{*                            {l s='Idiomas ilimitados' mod='pluglin'}*}
{*                        {/if}*}
{*                        {l s='Hasta' mod='pluglin'} {$plan['max_words']} {l s='palabras gestionadas' mod='pluglin'}*}
{*                    </span>*}
{*                </div>*}
{*            </div>*}
{*        {/foreach}*}
{*    </div>*}
        <form>
            <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
            <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
            <input type="hidden" name="token" value="{Tools::getValue('token')}">
            <div class="pluglin_submit">
                <button type="submit" name="finish">{l s='Finish' mod='pluglin'}</button>
            </div>
        </form>
</div>

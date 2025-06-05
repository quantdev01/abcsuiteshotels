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
                    {l s='Manage your contents and translations' mod='pluglin'}
                </p>
            </div>
            <p>{l s='Last synchronization of contents and translations with Pluglin:' mod='pluglin'} <strong>{$date_last_sync}</strong>.</p>
            <div class="sync_now_container">
                <a id="sync_now" class="btn btn-default" href="">
                    <div class="syncing_ico">
                        <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2.00513 7.99312C2.00513 5.58583 3.73062 3.61274 6.00169 3.14592V3.99656L8.99911 1.99829L6.00169 0V1.08428C2.61849 1.5716 0.00683594 4.47752 0.00683594 7.99312C0.00683594 10.11 0.956045 12.0064 2.44693 13.2901L4.19651 12.1236C2.87533 11.2237 2.00513 9.70856 2.00513 7.99312Z" />
                            <path d="M11.5547 2.69615L9.80508 3.86263C11.1263 4.76254 11.9965 6.27767 11.9965 7.99311C11.9965 10.4034 10.2745 12.4031 7.99987 12.8694V11.9896L5.00244 13.9879L7.99987 15.9861V14.9059C11.3834 14.4184 13.9948 11.509 13.9948 7.99308C13.9948 5.87628 13.0456 3.97984 11.5547 2.69615Z" />
                        </svg>
                    </div>
                    <span>{l s='Sync now' mod='pluglin'}</span>
                </a>
            </div>
            <div class="clearfix table-responsive">
                <table>
                    <thead>
                        <th>{l s='Language' mod='pluglin'}</th>
                        <th>{l s='Words' mod='pluglin'}</th>
                        <th>{l s='Progress' mod='pluglin'}</th>
                        <th>{l s='Actions' mod='pluglin'}</th>
                    </thead>
                    <tbody>
                    {foreach from=$languages item="lang"}
                        <tr>
                            <td class="name_language">
                                <div>
                                    <img src="/img/l/{$lang['id_lang']}.jpg">
                                    <span>{$lang['name']}</span>
                                </div>
                            </td>
                            <td>
                                <span>{$total_words}</span>
                            </td>
                            <td class="progress_language">
                                <div>
                                    <div class="progress_translated">
                                        <span>{$lang['translated']}% {l s='translated' mod='pluglin'}</span>
                                    </div>
                                    <div class="progress_revised">
                                        <span>{$lang['revised']}% {l s='revised' mod='pluglin'}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{$url_project}" target="_blank">
                                    {l s='Manage in Pluglin' mod='pluglin'}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
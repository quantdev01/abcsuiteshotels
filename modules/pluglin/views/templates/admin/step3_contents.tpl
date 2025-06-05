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
    <div class="progress_bar progress_bar_3"></div>
    <div class="form-wrapper">
        <p class="pluglin_title">{l s='We are synchronizing content' mod='pluglin'}</p>
        <p>{l s='Let us make a first discovery of the volume of translatable content in your store.' mod='pluglin'}</p>
        <div class="content_type_container">
            {foreach from=$contents_type item='name' key='key'}
                <div id="content_{$key}" data-key="{$key}" class="content_type row">
                    <div class="col-md-6">
                        <div class="icon_type" id="icon_{$key}">
                            <span class="ico_completed">
                                <svg width="21" height="16" viewBox="0 0 21 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.6515 15.2742C7.46075 15.4668 7.20052 15.5742 6.93019 15.5742C6.65987 15.5742 6.39963 15.4668 6.20889 15.2742L0.448363 9.49103C-0.149454 8.89096 -0.149454 7.91791 0.448363 7.31897L1.16967 6.59476C1.76767 5.99469 2.73595 5.99469 3.33376 6.59476L6.93019 10.2049L16.6483 0.450052C17.2463 -0.150017 18.2155 -0.150017 18.8124 0.450052L19.5337 1.17426C20.1315 1.77433 20.1315 2.74719 19.5337 3.34632L7.6515 15.2742Z" />
                                </svg>
                            </span>
                            <span class="ico_loading">
                                <svg width="15" height="16" viewBox="0 0 15 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.00513 7.99312C2.00513 5.58583 3.73062 3.61274 6.00169 3.14592V3.99656L8.99911 1.99829L6.00169 0V1.08428C2.61849 1.5716 0.00683594 4.47752 0.00683594 7.99312C0.00683594 10.11 0.956045 12.0064 2.44693 13.2901L4.19651 12.1236C2.87533 11.2237 2.00513 9.70856 2.00513 7.99312Z"/>
                                    <path d="M11.5547 2.69615L9.80508 3.86263C11.1263 4.76254 11.9965 6.27767 11.9965 7.99311C11.9965 10.4034 10.2745 12.4031 7.99987 12.8694V11.9896L5.00244 13.9879L7.99987 15.9861V14.9059C11.3834 14.4184 13.9948 11.509 13.9948 7.99308C13.9948 5.87628 13.0456 3.97984 11.5547 2.69615Z"/>
                                </svg>
                            </span>
                        </div>
                        <div class="name_type" id="name_{$key}"><span>{$name}</span></div>
                    </div>
                    <div class="col-md-6">
                        <div class="count_type">
                            <div>
                                <span class="last_type" id="last_{$key}"></span>
                                <span class="object_type" id="object_{$key}"></span>
                                /
                                <span class="words_type" id="words_{$key}"></span>
                                <span>{l s='words' mod='pluglin'}</span>
                            </div>
                        </div>
                        <div class="bar_progress_type" id="bar_{$key}">
                            <div class="bar_completed_type" id="bar_completed_{$key}"></div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <form>
            <input type="hidden" name="controller" value="{Tools::getValue('controller')}">
            <input type="hidden" name="configure" value="{Tools::getValue('configure')}">
            <input type="hidden" name="plans" value="1">
            <input type="hidden" name="token" value="{Tools::getValue('token')}">
            <div class="pluglin_submit">
                <button type="submit" name="sendContents" disabled class="disabled">{l s='Continue' mod='pluglin'}</button>
            </div>
        </form>
    </div>
</div>
{literal}
<script>
    $( document ).ready(function() {
        tables = [];
        i = 0;

        $(".content_type").each(function () {
            tables[i] = $(this).data('key');
            i++;
        });

        executeTables(0);
    });
</script>
{/literal}

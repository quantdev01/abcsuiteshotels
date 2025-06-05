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

{if isset($alert_message) && $alert_message}
        <div class="pluglin_alert">
                <div class="pluglin_alert_ico">
                        <svg width="23" height="20" viewBox="0 0 23 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M22.0474 14.9924L14.0972 1.6226C13.4962 0.606714 12.4347 0 11.2578 0C10.0809 0 9.01941 0.606714 8.41839 1.6226L0.46888 14.9924C-0.145025 16.0297 -0.156635 17.2754 0.437261 18.3243C1.03252 19.3739 2.10492 20 3.30758 20H19.208C20.4107 20 21.4831 19.3739 22.0783 18.3243C22.6723 17.2755 22.6607 16.0297 22.0474 14.9924ZM11.2578 4.0876C11.9873 4.0876 12.5785 4.68124 12.5785 5.41362V10.7178C12.5785 11.4501 11.9873 12.0438 11.2578 12.0438C10.5284 12.0438 9.93714 11.45 9.93714 10.7178V5.41362C9.9371 4.68124 10.5284 4.0876 11.2578 4.0876ZM9.27675 15.3588C9.27675 16.4557 10.1654 17.3479 11.2578 17.3479C12.3502 17.3479 13.2389 16.4557 13.2389 15.3588C13.2389 14.262 12.3502 13.3698 11.2578 13.3698C10.1654 13.3698 9.27675 14.262 9.27675 15.3588Z"/>
                        </svg>
                </div>
                <div class="pluglin_alert_text">
                        <p class="pluglin_alert_title">{$alert_message['title']}</p>
                        <p class="pluglin_alert_desc">{$alert_message['message']}</p>
                </div>
        </div>
{/if}
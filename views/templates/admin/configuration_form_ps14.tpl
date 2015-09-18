{*
 * 2015 CheckYourData
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
 *  @author    Thomas RIBIERE <thomas.ribiere@gmail.com>
 *  @copyright 2015 CheckYourData
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *}
<form action="{$action_url|escape:'htmlall':'UTF-8'}" method="post">
    <fieldset class="">
        <legend>
            <img src="../img/admin/cog.gif" alt="" class="middle" />{l s='Settings'}
        </legend>
        <label>{l s='Clée d\'accès CheckYourData' mod="checkyourdata"}</label>
        <div class="margin-form">
            <input name="checkyourdata_token" id="checkyourdata_token" value="{$token|escape:'htmlall':'UTF-8'}" class="" size="30" type="text">
        </div>
        <label>{l s='Google UA pour ajouter un tracking analytics standard' mod="checkyourdata"}</label>
        <div class="margin-form">
            <input name="checkyourdata_ua" id="checkyourdata_ua" value="{$ua|escape:'htmlall':'UTF-8'}" class="" size="30" type="text">
        </div>
        <center><input type="submit" name="{$submit_name|escape:'htmlall':'UTF-8'}" value="{l s='Save'}" class="button" /></center>
    </fieldset>
</form>


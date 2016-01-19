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
<form action="{$action_url|escape:'javascript':'UTF-8'}" method="post">
    <fieldset class="">
        <legend>
            <img src="../img/admin/cog.gif" alt="" class="middle" />{l s='General settings' mod='checkyourdata'}
        </legend>
        <label>{l s='Access key to CheckYourData' mod='checkyourdata'}</label>
        <div class="margin-form">
            <input name="checkyourdata_token" id="checkyourdata_token" value="{$token|escape:'htmlall':'UTF-8'}" class="" size="30" type="text">
        </div>
    </fieldset>
    <br/>
    <fieldset class="">
        <legend>
            <img src="../img/admin/cog.gif" alt="" class="middle" />{l s='Setting Google Analytics' mod='checkyourdata'}
        </legend>
        {*<label>{l s='Tracker activation' mod='checkyourdata'}</label>*}
        <div class="margin-form">
            <input name="checkyourdata_trackers_ganalytics" id="checkyourdata_trackers_ganalytics" class="" type="hidden" value="true" checked="checked"/>
        </div>
        <label>{l s='Google Analytics ID' mod='checkyourdata'}</label>
        <div class="margin-form">
            <input name="checkyourdata_ganalytics_ua" id="checkyourdata_ganalytics_ua" value="{$trackers['ganalytics']['ua']|escape:'htmlall':'UTF-8'}" class="" size="30" type="text">
        </div>
    </fieldset>
    <br/>
    <!--fieldset class="">
        <legend>
            <img src="../img/admin/cog.gif" alt="" class="middle" />{l s='Lengow' mod='checkyourdata'}
        </legend>
        <label>{l s='Tracker activation' mod='checkyourdata'}</label>
        <div class="margin-form">
            <input name="checkyourdata_trackers_lengow" id="checkyourdata_trackers_lengow" class="" type="checkbox"/>
        </div>
        <label>{l s='ID Lengow' mod='checkyourdata'}</label>
        <div class="margin-form">
            <input name="checkyourdata_lengow_id" id="checkyourdata_lengow_id" value="{$trackers['lengow']['id']|escape:'htmlall':'UTF-8'}" class="" size="30" type="text">
        </div>
    </fieldset>
    <br/>
    <fieldset class="">
        <legend>
            <img src="../img/admin/cog.gif" alt="" class="middle" />{l s='NetAffiliation' mod='checkyourdata'}
        </legend>
        <label>{l s='Tracker activation' mod='checkyourdata'}</label>
        <div class="margin-form">
            <input name="checkyourdata_trackers_netaffiliation" id="checkyourdata_trackers_netaffiliation" class="" type="checkbox"/>
        </div>
        <label>{l s='ID NetAffiliation' mod='checkyourdata'}</label>
        <div class="margin-form">
            <input name="checkyourdata_netaffiliation_id" id="checkyourdata_netaffiliation_id" value="{$trackers['netaffiliation']['id']|escape:'htmlall':'UTF-8'}" class="" size="30" type="text">
        </div>
    </fieldset-->
    <br/>
    <center><input type="submit" name="{$submit_name|escape:'htmlall':'UTF-8'}" value="{l s='Save' mod='checkyourdata'}" class="button" /></center>
</form>


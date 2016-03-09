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


<div class="checkyourdata {$checkyourdata_ps_version_class|escape:'htmlall':'UTF-8'}">
    <div class="row row-eq-height">
        <div class="col-md-6 right-block">
            <div class="panel panel-primary panel-transparent col-md-8 col-md-offset-2 signin-box">
                <div class="panel-body">
                    <div class="row checkyourdata_logo" id="checkyourdata_img_header">
                        <div class="col-lg-12">
                            <img src="{$checkyourdata_url_app|escape:'htmlall':'UTF-8'}public/img/logo_presta_bo1.3.svg"
                                 alt="CheckYourData"
                                 class="img-responsive" style="width:100%;"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center" >
                            <form class="text-center" role="form"
                                               action="{$checkyourdata_form_action|escape:'htmlall':'UTF-8'}"
                                               method="post" enctype="multipart/form-data"
                                               novalidate="">
                                <div class="col-xs-12 col-md-12">
                                    <div class="row input-group">
                                        <input type="text" class="form-control"
                                               id="checkyourdata_ganalytics_ua"
                                               name="checkyourdata_ganalytics_ua"
                                               value="{$checkyourdata_ganalytics_ua|escape:'htmlall':'UTF-8'}"
                                               placeholder="UA-XXXXXXXX-X">
                                        <span class="input-group-btn">
                                            <button type="submit"
                                                    id="{$checkyourdata_form_id|escape:'htmlall':'UTF-8'}"
                                                    name="{$checkyourdata_form_name|escape:'htmlall':'UTF-8'}"
                                                    class="btn btn-success">{l s='Update' mod='checkyourdata'}</button>
                                        </span>
                                    </div>
                                    <div class="row">
                                        <h4>{l s='My key CheckYourData' mod='checkyourdata'}</h4>
                                        <div class="row input-group">
                                            <input type="text" class="form-control"
                                                   id="checkyourdata_token"
                                                   name="checkyourdata_token"
                                                   value="{$checkyourdata_token|escape:'htmlall':'UTF-8'}">
                                            <span class="input-group-btn">
                                                <button type="submit"
                                                        id="{$checkyourdata_form_id|escape:'htmlall':'UTF-8'}"
                                                        name="{$checkyourdata_form_name|escape:'htmlall':'UTF-8'}"
                                                        class="btn btn-success">{l s='Update' mod='checkyourdata'}</button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-6 text-center left-block">
            <h2><strong>{l s='Welcome to CheckYourData' mod='checkyourdata'}</strong></h2>
            <br />
            <h3><strong>{l s='Google Analytics Ecommerce Expert Pack' mod='checkyourdata'}</strong></h3>

            <p>{l s='The most advanced Ecommerce addon dedicated to Google Analytics' mod='checkyourdata'}</p>

            <p>{l s='Check your Data perfecly implement Google Analytics and Optimize you Data. Then you will be able to Measure the real return on your marketing investment!' mod='checkyourdata'}</p>

            {*<p><strong>-</strong></p>*}
            <h4><strong>{l s='Benefits' mod='checkyourdata'}</strong></h4>

            <div class="row">
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/brain.png" width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Focusing on data-driven Strategy' mod='checkyourdata'}</strong><br/>
                        {l s='Unmatched Quality Data' mod='checkyourdata'}
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/time.png" width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Easy setup' mod='checkyourdata'}</strong><br/>
                        {l s='A simple and fast module to install' mod='checkyourdata'}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/config.png"
                         width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Custom settings' mod='checkyourdata'}</strong><br/>
                        {l s='Your needs deserve a customized experience' mod='checkyourdata'}
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/support.png"
                         width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Responsive Support' mod='checkyourdata'}</strong><br/>
                        {l s='We reply within 48 hours' mod='checkyourdata'}<br />
                        {l s='Call us at: 05 32 09 12 30' mod='checkyourdata'}
                    </p>
                </div>
            </div>

            <div class="row cyd-m-m">
                <a href="{$checkyourdata_url_app|escape:'htmlall':'UTF-8'}"
                   class="btn btn-success" role="button"> {l s='Advanced settings' mod='checkyourdata'}</a>
            </div>

            <div class="row cyd-m-m">
                <p><strong>{$checkyourdata_free_periode|escape:'htmlall':'UTF-8'}</strong></p>
            </div>
        </div>
    </div>

    {if {$checkyourdata_new_install_by_app|escape:'htmlall':'UTF-8'}}

        <!-- Modal -->
        <div class="modal fade" id="ppAnalytics" tabindex="-1" role="dialog" aria-labelledby="">
            <div class="modal-dialog text-center" role="document">
                <div class="modal-content">
                    <div class="modal-body">

                            <div class="col-lg-12">
                                <img src="{$checkyourdata_url_app|escape:'htmlall':'UTF-8'}public/img/logo_presta_bo1.3.svg"
                                     alt="CheckYourData"
                                     class="img-responsive center-block"/>
                            </div>
                        <p>
                            <strong>{l s='Have you disabled your former Google Analytics module ?' mod='checkyourdata'}</strong><br/>
                        </p>
                        <p>
                            {l s='If it\'s not already done, please disable all other modules implementing Google Analytics on your online shop' mod='checkyourdata'}
                        </p>
                        <p>
                            {l s='Otherwise, you will collect your data twice. ' mod='checkyourdata'}
                        </p>
                        <p>
                            {l s='If you need help, please feel free to contact us' mod='checkyourdata'} : <a
                                    href="mailto:contact@checkyourdata.net">contact@checkyourdata.net</a>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    {literal}
        <script language=javascript>

            <!--
            $( document ).ready(function() {;
                $('#ppAnalytics').modal('toggle');
            })
            // -->

        </script>
    {/literal}
    {/if}
</div>

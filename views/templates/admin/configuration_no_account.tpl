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
            <div class="panel panel-primary panel-transparent col-md-6 col-md-offset-3 signin-box">
                <div class="panel-body">
                    <div class="row checkyourdata_logo" id="checkyourdata_img_header">
                        <div class="col-lg-12">
                            <img src="{$checkyourdata_url_app|escape:'htmlall':'UTF-8'}public/img/logo.svg"
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

                                <input type="hidden" id="checkyourdata_current_language"
                                       name="checkyourdata_current_language"
                                       value="{$checkyourdata_current_language|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_back_url"
                                       name="checkyourdata_back_url"
                                       value="{$checkyourdata_back_url|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_lastname"
                                       name="checkyourdata_lastname"
                                       value="{$checkyourdata_lastname|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_firstname"
                                       name="checkyourdata_firstname"
                                       value="{$checkyourdata_firstname|escape:'htmlall':'UTF-8'}">

                                <input type="hidden" id="checkyourdata_site"
                                       name="checkyourdata_site"
                                       value="{$checkyourdata_site|escape:'htmlall':'UTF-8'}">

                                <div class="form-group">
                                    <input type="text" id="checkyourdata_email"
                                           name="checkyourdata_email"
                                           placeholder="Email"
                                           value="{$checkyourdata_email|escape:'htmlall':'UTF-8'}">

                                    <input type="text" class="form-control"
                                           id="checkyourdata_ganalytics_ua"
                                           name="checkyourdata_ganalytics_ua"
                                           placeholder="UA-XXXXXXXX-X">
                                </div>
                                <button type="submit"
                                        id="submitcheckyourdata_signin"
                                        name="submitcheckyourdata_signin"
                                        class="btn btn-lg btn-success">{l s='Install' mod='checkyourdata'}</button>

                            </form>
                            <p>{l s='Install Check Your Data, you accept' mod='checkyourdata'}
                                <a href="{$checkyourdata_url_cgv|escape:'htmlall':'UTF-8'}" target="_blank">
                                    {l s='Terms and Conditions Check Your Data' mod='checkyourdata'}</a>
                            </p>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row exist-token text-center">
                <div class="panel panel-primary panel-transparent col-md-10 col-md-offset-1 login-box clearfix">
                    <div class="panel-body">
                        <p>{l s='If you already have a Check Your Data account, contact your access key' mod='checkyourdata'}</p>
                        <form class="text-center" role="form"
                              action="{$checkyourdata_form_token_action|escape:'htmlall':'UTF-8'}"
                              method="post" enctype="multipart/form-data"
                              novalidate="">

                            <div class="input-group">
                                <input type="text" class="form-control"
                                       id="checkyourdata_token"
                                       name="checkyourdata_token">
                                <span class="input-group-btn">
                                    <button type="submit"
                                            class="btn btn-success"
                                            id="submitcheckyourdata_signin_token"
                                            name="submitcheckyourdata_signin_token">{l s='Save' mod='checkyourdata'}
                                    </button>
                                </span>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 text-center left-block">
            <h2><strong>{l s='Welcome CheckYourData' mod='checkyourdata'}</strong></h2>

            <p>{l s='The only module capable of installing, reliability and optimize Google Analytics for E-commerce' mod='checkyourdata'}</p>

            <p>{l s='Measure the real return on your marketing investment!' mod='checkyourdata'}</p>

            {*<p><strong>-</strong></p>*}
            <h4><strong>{l s='Avantage CheckYourData' mod='checkyourdata'}</strong></h4>

            <div class="row">
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/brain.png" width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Peace of mind' mod='checkyourdata'}</strong><br/>
                        {l s='Base your strategy on 100 percent reliable data' mod='checkyourdata'}
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/time.png" width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Easy installation' mod='checkyourdata'}</strong><br/>
                        {l s='A simple and fast module install in 5 minutes' mod='checkyourdata'}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/config.png"
                         width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Custom setting' mod='checkyourdata'}</strong><br/>
                        {l s='Took different needs into account in the configuring your account' mod='checkyourdata'}
                    </p>
                </div>
                <div class="col-md-6 text-center">
                    <img class="center-block" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/support.png"
                         width="80"
                         height="80"/><br/>
                    <p>
                        <strong>{l s='Reactive support at your service' mod='checkyourdata'}</strong><br/>
                        {l s='We will reply in 48 hours' mod='checkyourdata'}
                        {l s='Call us at: 05 32 09 12 30' mod='checkyourdata'}
                    </p>
                </div>
            </div>

            <div class="row center-block">

                <div class="col-md-6 ">
                    <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ps_partner.jpg"
                         height="100"/>
                </div>
                <div class="col-md-6 google-partner">
                    <img class="" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/google-partner.jpg"
                         height="80"/>
                </div>
            </div>

        </div>
    </div>

    <br/><br/>

</div>

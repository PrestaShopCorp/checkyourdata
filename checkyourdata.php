<?php
/**
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
 *  @author    Check Your Data <contact@checkyourdata.net>
 *  @copyright 2015 CheckYourData
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class CheckYourData extends Module
{
    private static $dcUrl = 'app.checkyourdata.net/';

    /**
     * Module instanciate
     */
    public function __construct()
    {
        if (!class_exists('CheckYourDataWSHelper')) {
            include_once dirname(__FILE__) . '/wshelper.inc.php';
        }
        // trackers
        if (!class_exists('CheckYourDataGAnalytics')) {
            include_once dirname(__FILE__) . '/trackers/ganalytics.inc.php';
        }

        // Environement configuration
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, 'ribie.re')) {
            // RECETTE
            self::$dcUrl = 'app-preprod.checkyourdata.net/';
        } elseif (strpos($host, 'cyd.com') || strpos($host, 'dc.com')) {
            // LOCAL
            self::$dcUrl = 'app2.cyd.com/';
        }

        if (getenv('CYD_ENV') == 'dev') {
            self::$dcUrl = getenv('CYD_APP') . '/';
        }

        $this->name = 'checkyourdata';
        $this->tab = 'analytics_stats';

        $this->version = '1.3.0';

        $this->author = 'Check Your Data - http://www.checkyourdata.net';

        // warnings in admin
        $this->need_instance = 1;

        if (version_compare(_PS_VERSION_,'1.5.6.2','>=')) {
            // BUG prestashop on v1.5.4.1 => compliancy not used properly
            // BUG fixed on v1.5.6.2.
            $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        }

        if (version_compare(_PS_VERSION_,'1.6','>=')) {
            // from PS1.6, theme bootstrap
            $this->bootstrap = true;
        }

        parent::__construct();

        $this->displayName = $this->l('Check Your Data - Analytics reports 100 percent reliable');
        $this->description = $this->l('Discover the real return on your marketing investments: collect 100 percent of your sales data in Google Analytics to get the most of your reports with clean, accurate and reliable data.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        // Warning if token not set
        if (!Configuration::get('checkyourdata_token')) {
            $this->warning = $this->l('Access key to checkyourdata.net not configured');
        } else {
            // Warning if demo
            $demoEnd = Configuration::get('checkyourdata_demo_end');
            if (!empty($demoEnd)) {
                $dt = new DateTime();
                $dend = DateTime::createFromFormat('Y-m-d H:i:s', $demoEnd);
                if ($dt > $dend) {
                    $this->warning = $this->l('Demo ended. Your data are no more tracked. Please complete your account informations on Check Your Data App');
                } else {
                    $this->warning = $this->l('Demo active. Your data are tracked until').' '.$dend->format('d/m/Y H:i:s');
                }
            }
        }


        if (version_compare(_PS_VERSION_,'1.5','<')) {
            /** Backward compatibility */
            require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
        }

        // TRACKERS
        $trackers = Tools::jsonEncode(Configuration::get('checkyourdata_trackers'), true);
        if ($trackers != null && !empty($trackers['ganalytics']['active']) && $trackers['ganalytics']['active']) {
            $res = CheckYourDataGAnalytics::init($trackers['ganalytics']['ua'], $this);
            if ($res != '') {
                $this->warning = $res;
            }
        }

        // show error messages if present
        $errs = Configuration::get('checkyourdata_last_errors');
        if (!empty($errs)) {
            $this->warning = $errs;
        }
    }

    /**
     * Uninstall module
     * @return bool : true if uninstall OK, false otherwise
     */
    public function uninstall()
    {
        $ko = false;

        // Common Hooks
        $ko = $ko || !$this->unregisterHook('header');
        $ko = $ko || !$this->unregisterHook('paymentTop');
        $ko = $ko || !$this->unregisterHook('updateOrderStatus');

        if (version_compare(_PS_VERSION_,'1.5','>=') && version_compare(_PS_VERSION_,'1.6','<')) {
            $ko = $ko || !$this->unregisterHook('displayMobileHeader');
        }

        if (version_compare(_PS_VERSION_,'1.5','<')) {
            // REFUND
            $ko = $ko || !$this->unregisterHook('cancelProduct');
        } else {
            // REFUND
            if (version_compare(_PS_VERSION_,'1.6','<')) {
                $ko = $ko || !$this->unregisterHook('displayAdminOrder');
            }

            $ko = $ko || !$this->unregisterHook('actionObjectOrderDetailUpdateAfter');
            $ko = $ko || !$this->unregisterHook('displayAdminOrderContentOrder');
        }
        
        $ko = $ko || !parent::uninstall();

        if (!$ko) {
            // reset conf data
            Configuration::updateValue('checkyourdata_token', '');
            Configuration::updateValue('checkyourdata_user_email', '');
            Configuration::updateValue('checkyourdata_last_errors', '');
            Configuration::updateValue('checkyourdata_demo_end', '');
        }

        return !$ko;
    }

    /**
     * Module install : HOOKs registration
     * @return bool : true if install OK, false otherwise
     */
    public function install()
    {
        $ko = !parent::install();

        // Commons Hooks
        $ko = $ko || !$this->registerHook('header');
        $ko = $ko || !$this->registerHook('paymentTop');
        $ko = $ko || !$this->registerHook('updateOrderStatus');

        if (version_compare(_PS_VERSION_,'1.5','>=') && version_compare(_PS_VERSION_,'1.6','<')) {
            $ko = $ko || !$this->registerHook('displayMobileHeader');
        }

        if (version_compare(_PS_VERSION_,'1.5','<')) {
            // REFUND
            $ko = $ko || !$this->registerHook('cancelProduct');
        } else {
            // REFUND
            if (version_compare(_PS_VERSION_,'1.6','<')) {
                $ko = $ko || !$this->registerHook('displayAdminOrder');
            }

            $ko = $ko || !$this->registerHook('actionObjectOrderDetailUpdateAfter');
            $ko = $ko || !$this->registerHook('displayAdminOrderContentOrder');
        }

        return !$ko;
    }

    /* Alias for PS 1.5 of hookDisplayAdminOrderContentOrder of PS 1.6 */
    public function hookDisplayAdminOrder($params)
    {
        $params['order'] = new Order($params['id_order']);
        return $this->hookDisplayAdminOrderContentOrder($params);
    }

    public function hookDisplayAdminOrderContentOrder($params)
    {
        // get CYD token
        $token = Configuration::get('checkyourdata_token');
        if (empty($token)) {
            return '';
        }

        $order = $params['order'];
        $oid = $order->id;

        // check if refund to send
        $ordersToRefound = Configuration::get('checkyourdata_ref_orders');
        if ($ordersToRefound === false) {
            return;
        }
        $ordersToRefound = Tools::jsonDecode($ordersToRefound, true);
        if (empty($ordersToRefound[$oid])) {
            return;
        }

        // data perpare
        $data = array(
            'token' => $token,
            'action' => 'partialRefound',
            'data' => array(
                'items' => Tools::jsonEncode($ordersToRefound[$oid]),
                'orderId' => $oid,
            ),
        );
        // send to APP
        $res = CheckYourDataWSHelper::send(self::$dcUrl, $data);

        // if ok, delete 'toRefund' in order
        if ($res['state'] == 'ok') {
            unset($ordersToRefound[$oid]);
            Configuration::updateValue('checkyourdata_ref_orders', Tools::jsonEncode($ordersToRefound));
        }
    }

    public function hookActionObjectOrderDetailUpdateAfter($params)
    {
        $orderDet = $params['object'];//OrderDetail

        $pid = $orderDet->product_id;
        $paid = $orderDet->product_attribute_id;
        // if no qty refunded, no action
        if (empty($orderDet->product_quantity_refunded)) {
            return;
        }
        $qtyRefound = $orderDet->product_quantity_refunded;

        // save to send after
        $ordersToRefound = Configuration::get('checkyourdata_ref_orders');
        if ($ordersToRefound == false) {
            $ordersToRefound = array();
        } else {
            $ordersToRefound = Tools::jsonDecode($ordersToRefound, true);
        }
        // set of order if not set
        $oid = $orderDet->id_order;
        if (!isset($ordersToRefound[$oid])) {
            $ordersToRefound[$oid] = array();
        }
        // set of qty to refund and product id
        $ordersToRefound[$oid][$pid . '_' . $paid] = $qtyRefound;

        Configuration::updateValue('checkyourdata_ref_orders', Tools::jsonEncode($ordersToRefound));
    }

    /**
     * HOOK displayHeader : add Google Analytics JS tracking code
     * @return string : html to add to header
     */
    public function hookDisplayMobileHeader()
    {
        return $this->hookHeader();
    }

    /**
     * HOOK displayHeader : add Google Analytics JS tracking code
     * @return string : html to add to header
     */
    public function hookHeader()
    {
        $token = Configuration::get('checkyourdata_token');
        // if module is not configured
        if (empty($token)) {
            return;
        }
        $out = '';

        // All pages, except confirmation
        $controller = $this->context->controller->php_self;
        if ($controller == 'order-confirmation') {
            return;
        }

        // trackers
        $trackers = Tools::jsonDecode(Configuration::get('checkyourdata_trackers'), true);
        // ganalytics is activated ?
        if ($trackers['ganalytics']['active']) {
            $this->trackerAction(
                CheckYourDataGAnalytics::hookHeader($trackers['ganalytics']['ua']),
                $out
            );
        }

        return $out;
    }


    /**
     * HOOK Payment choice page : JS call to APP, order init
     * @return string : html / JS to add to page
     */
    public function hookPaymentTop()
    {
        $error = array();
        // get CYD token
        $token = Configuration::get('checkyourdata_token');
        if (empty($token)) {
            return '';
        }
        $out = '';

        // get order (cart)
        $cart = $this->context->cart;

        // trackers
        $trackers = Tools::jsonDecode(Configuration::get('checkyourdata_trackers'), true);
        // ganalytics is activated ?
        if ($trackers['ganalytics']['active']) {

            if (!CheckYourDataGAnalytics::addTrackerData($trackers['ganalytics']['ua'])) {
                $error[] = 'No_GCID';
            };
        }


        if (count($error) == 0) {
            $trData = CheckYourDataWSHelper::getTrackersData();
            $res = $this->sendInitOrderToApp($cart->id, $trData);

            // errors
            if ($res['state'] != 'ok') {
                // save cart to re send
                $this->addCartInError($cart->id);
                error_log('Checkyourdata WS Update Order error : ' . implode("\n", $res['errors']));
            } else {
                // APP CYD is OK
                $this->sendCartsInError($cart->id);
            }
        } else {
            if ($trackers['ganalytics']['active']) {

                $data = $this->formatDataToSend($cart->id);
                $enc = CheckYourDataWSHelper::encodeData($data);
                // add JS vars
                $this->trackerAction(
                    array(
                        'tpl' => array(
                            'file' => 'ganalytics/payment-top.tpl',
                            'smarty' => array(
                                'url' => '//' . self::$dcUrl . 'ws/',
                                'data' => 'k=' . $enc['key'] . '&d=' . $enc['data'],
                            ),
                        ),
                    ),
                    $out
                );

            }
        }

        return $out;
    }

    public function sendInitOrderToApp($cartId, $trData)
    {
        $data = $this->formatDataToSend($cartId);

        return CheckYourDataWSHelper::send(self::$dcUrl, $data, $trData);

    }

    /**
     * HOOK order state change
     * @param type $params : array containing order details
     */
    public function hookUpdateOrderStatus($params)
    {
        // get CYD token
        $token = Configuration::get('checkyourdata_token');
        if (empty($token)) {
            return;
        }

        // send to APP
        $res = $this->sendOrderToApp($params['id_order'], $params['newOrderStatus']->id);


        if ($res['state'] != 'ok') {
            // save order to re send
            $this->addOrderInError($params['id_order']);
            error_log('Checkyourdata WS Update Order error : ' . implode("\n", $res['errors']));
        } else {
            // all ok
            //try send blocked carts
            $this->sendCartsInError();

            //try send blocked orderb
            $this->sendOrdersInError();
        }
    }

    private function sendOrderToApp($orderId, $nextState = null)
    {
        $token = Configuration::get('checkyourdata_token');
        $order = new Order($orderId);

        if (!Validate::isLoadedObject($order)) {
            // no order
            return;
        }

        if ($nextState === null) {
            $nextState = $order->getCurrentState();
        }

        // conversion rate
        $conversion_rate = 1;
        $currency = new Currency((int)$order->id_currency);
        /*if ($order->id_currency != Configuration::get('PS_CURRENCY_DEFAULT')) {
            $conversion_rate = (float) $currency->conversion_rate;
        }*/

        // amounts (with taxes on shipping)
        $tax = $order->getTotalProductsWithTaxes() - $order->getTotalProductsWithoutTaxes();
        $tax += $this->getShippingTotal($order);

        // Order general information
        $trans = array(
            'id' => (int)$order->id,
            'cartId' => (int)$order->id_cart,
            'store' => htmlentities(Configuration::get('PS_SHOP_NAME')),
            'total' => Tools::ps_round((float)$order->total_paid / (float)$conversion_rate, 2),
            'shipping' => Tools::ps_round((float)$order->total_shipping / (float)$conversion_rate, 2),
            'tax' => $tax,
            //'city' => addslashes($delivery_address->city),
            'state' => $nextState,
            //'country' => addslashes($delivery_address->country),
            'currency' => $currency->iso_code
        );


        $pms = PaymentModule::getInstalledPaymentModules();
        $pmid = '';
        foreach ($pms as $pm) {
            if ($pm['name'] == $order->module) {
                $pmid = $pm['id_module'];
                break;
            }
        }

        // Product information
        $products = $order->getProducts();
        $items = array();
        foreach ($products as $p) {
            $categ = new Category($this->getProductDefaultCategory($p));
            $items [$p['product_id'] . '_' . $p['product_attribute_id']] = array(
                'name' => $p['product_name'],
                'price' => Tools::ps_round((float)$p['product_price_wt'] / (float)$conversion_rate, 2),
                'code' => $this->getProductReference($p),
                'category' => implode(" ", $categ->name),
                'qty' => $p['product_quantity'],
            );
        }

        $data = array(
            'token' => $token,
            'action' => 'changeOrderState',
            'data' => array(
                'total' => $trans["total"],
                'tax' => $trans["tax"],
                'shipping' => $trans["shipping"],
                'cartId' => $trans["cartId"],
                'items' => Tools::jsonEncode($items),
                'orderId' => $trans["id"],
                'state' => $trans['state'],
                'paymentModuleId' => $pmid,
                'currency' => $trans['currency']
            ),
        );
        $res = CheckYourDataWSHelper::send(self::$dcUrl, $data);
        return $res;
    }

    private function trackerAction($trackRes, &$out)
    {
        if (!empty($trackRes['tpl'])) {
            // smarty assign
            if (!empty($trackRes['tpl']['smarty'])) {
                $this->context->smarty->assign($trackRes['tpl']['smarty']);
            }
            // template load
            if (!empty($trackRes['tpl']['file'])) {
                $out .= $this->display(__FILE__, 'views/templates/hook/' . $trackRes['tpl']['file']);
            }
        }
    }

    private function sendShopParamsToApp($token)
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Get order states
        $oss = OrderState::getOrderStates($default_lang);
        $states = array();
        foreach ($oss as $os) {
            $states [$os['id_order_state']] = $os['name'];
        }

        // Get payment modules
        $token = Configuration::get('checkyourdata_token');
        $modules = array();
        $pms = PaymentModule::getInstalledPaymentModules();
        foreach ($pms as $pm) {
            $p = Module::getInstanceByName($pm['name']);
            if (is_object($p)) {
                $modules [$pm['id_module']] = $p->displayName;
            }
        }

        // get confirmation page url
        $l = new Link();
        $shopUrl = $this->getShopUrl();
        $confirmUrl = str_replace($shopUrl, '', $l->getPageLink('order-confirmation'));

        // get confirmation page title
        $meta = MetaCore::getMetaByPage('order-confirmation', $default_lang);
        $confirmTitle = $meta['title'];

        $data = array(
            'token' => $token,
            'action' => 'setShopParams',
            'data' => array(
                'modules' => $modules,
                'states' => $states,
                'trackers' => Configuration::get('checkyourdata_trackers'),
                'confirm_url' => $confirmUrl,
                'confirm_title' => $confirmTitle,
                'cyd_module_version' => $this->version,
                'shop_type' => 'prestashop',
                'shop_version' => _PS_VERSION_,
            ),
        );
        return CheckYourDataWSHelper::send(self::$dcUrl, $data);
    }

    public function createAccountInApp($email)
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $lang = new Language($default_lang);

        // Get order states
        $oss = OrderState::getOrderStates($default_lang);
        $states = array();
        foreach ($oss as $os) {
            $states [$os['id_order_state']] = $os['name'];
        }

        // Get payment modules
        $modules = array();
        $pms = PaymentModule::getInstalledPaymentModules();
        foreach ($pms as $pm) {
            $p = Module::getInstanceByName($pm['name']);
            $modules [$pm['id_module']] = $p->displayName;
        }

        $data = array(
            'action' => 'createAccount',
            'data' => array(
                'shopUrl' => $this->getShopUrl(),
                'email' => $email,
                'lang' => $lang->iso_code,
                'modules' => $modules,
                'states' => $states,
            ),
        );

        return CheckYourDataWSHelper::send(self::$dcUrl, $data);
    }

    /**
     * Configuration page for module in back office
     * @return string : html content of page
     */
    public function getContent()
    {

        $output = '';
        if (Tools::isSubmit('submit' . $this->name.'_signin_token')) {

            // reset errors
            Configuration::updateValue('checkyourdata_last_errors', '');

            $isOk = true;
            $token = (string)Tools::getValue('checkyourdata_token');
            $token = trim($token);
            if ($token == '' || !preg_match('@^[a-f0-9]{32}$@', $token)) {
                $output .= $this->displayError($this->l('Invalid token value'));
                $isOk = false;
            }

            if ($isOk) {
                // set token
                Configuration::updateValue('checkyourdata_token', $token);

                // save trackers conf
                $trackers = array(
                    'ganalytics' => array('active' => true),
                    'lengow' => array('active' => false),
                    'netaffiliation' => array('active' => false)
                );

                Configuration::updateValue('checkyourdata_trackers', Tools::jsonEncode($trackers), true);

                // send to app
                $res = $this->sendShopParamsToApp($token);
                if ($res['state'] == 'ok') {
                    $output .= $this->displayConfirmation(
                        sprintf($this->l('Configuration saved on %s'), 'https://' . self::$dcUrl)
                    );
                }
            }
        }
        elseif (Tools::isSubmit('submit' . $this->name . '_update')) {
            $isOk = true;
            $token = (string)Tools::getValue('checkyourdata_token');
            $token = trim($token);
            if ($token == '' || !preg_match('@^[a-f0-9]{32}$@', $token) || !Validate::isGenericName($token)) {
                $output .= $this->displayError($this->l('Invalid token value'));
                $isOk = false;
            }

            if ($isOk) {
                // validation GA
                $ua = (string)Tools::getValue('checkyourdata_ganalytics_ua');
                $ua = trim($ua);

                if ($ua != '' && !preg_match('@^UA-[0-9\-]+$@', $ua)) {
                    $output .= $this->displayError($this->l('Invalid UA value'));
                    $isOk = false;
                }

                if ($isOk) {

                    $trackers = array('ganalytics' => array(), 'lengow' => array(), 'netaffiliation' => array());

                    // TRACKERS
                    // Google
                    $trackers['ganalytics']['active'] = true;
                    $trackers['ganalytics']['ua'] = $ua;

                    // Lengow
                    $trackers['lengow']['active'] = (string)Tools::getValue('checkyourdata_trackers_lengow') == 'on';
                    $trackers['lengow']['id'] = (string)Tools::getValue('checkyourdata_lengow_id');

                    // NetAffiliation
                    $netaffiliation_active = (string)Tools::getValue('checkyourdata_trackers_netaffiliation') == 'on';
                    $trackers['netaffiliation']['active'] = $netaffiliation_active;
                    $trackers['netaffiliation']['id'] = (string)Tools::getValue('checkyourdata_netaffiliation_id');

                    $trackerUpdated = false;
                    $tokenUpdated = false;

                    // save trackers conf
                    $JSON_trackers = Tools::jsonEncode($trackers);
                    if (Configuration::get('checkyourdata_trackers') != $JSON_trackers){
                        Configuration::updateValue('checkyourdata_trackers', $JSON_trackers, true);
                        $trackerUpdated = true;
                    }

                    // set token
                    if (Configuration::get('checkyourdata_token') != $token) {
                        Configuration::updateValue('checkyourdata_token', $token);
                        $tokenUpdated = true;
                    }

                    // send params to APP if token set
                    $res = $this->sendShopParamsToApp($token);
                    if ($res['state'] == 'ok') {
                        if ($tokenUpdated){
                            $output .= $this->displayConfirmation($this->l('Token updated'));
                        }
                        if ($trackerUpdated){
                            $output .= $this->displayConfirmation(
                                sprintf($this->l('Configuration saved on %s'), 'https://' . self::$dcUrl)
                            );
                        }
                    }

                }


            }
        }
        elseif (Tools::isSubmit('submit' . $this->name . '_token_valide')) {

            $token = (string)Tools::getValue('checkyourdata_token');
            $ua = (string)Tools::getValue('checkyourdata_ganalytics_ua');

            $trackers = array('ganalytics' => array(), 'lengow' => array(), 'netaffiliation' => array());

            // TRACKERS
            // Google
            $trackers['ganalytics']['active'] = true;
            $trackers['ganalytics']['ua'] = $ua;

            // Lengow
            $trackers['lengow']['active'] = (string)Tools::getValue('checkyourdata_trackers_lengow') == 'on';
            $trackers['lengow']['id'] = (string)Tools::getValue('checkyourdata_lengow_id');

            // NetAffiliation
            $netaffiliation_active = (string)Tools::getValue('checkyourdata_trackers_netaffiliation') == 'on';
            $trackers['netaffiliation']['active'] = $netaffiliation_active;
            $trackers['netaffiliation']['id'] = (string)Tools::getValue('checkyourdata_netaffiliation_id');

            // save trackers conf
            $JSON_trackers = Tools::jsonEncode($trackers);
            if (Configuration::get('checkyourdata_trackers') != $JSON_trackers){
                Configuration::updateValue('checkyourdata_trackers', $JSON_trackers, true);
                $trackerUpdated = true;
            }

            // set token
            if (Configuration::get('checkyourdata_token') != $token) {
                Configuration::updateValue('checkyourdata_token', $token);
                $tokenUpdated = true;
            }

            if ($tokenUpdated){
                $output .= $this->displayConfirmation($this->l('Token updated'));
            }
            if ($trackerUpdated){
                $output .= $this->displayConfirmation(
                    sprintf($this->l('Configuration saved on %s'), 'https://' . self::$dcUrl)
                );
            }
        }

        $errs = Configuration::get('checkyourdata_last_errors');
        if (!empty($errs)) {
            $output .= $this->displayError($errs);
        }

        // Warning if demo
        $demoEnd = Configuration::get('checkyourdata_demo_end');
        if (!empty($demoEnd)) {
            $dt = new DateTime();
            $dend = DateTime::createFromFormat('Y-m-d H:i:s', $demoEnd);
            if ($dt > $dend) {
                $output .= $this->displayError($this->l('Demo ended. Your data are no more tracked. Please complete your account informations on Check Your Data App'));
            } else {
                $output .= $this->displayConfirmation($this->l('Demo active. Your data are tracked until') . ' ' . $dend->format('d/m/Y H:i:s'));
            }
        }

        $token = Configuration::get('checkyourdata_token');

        if (empty($token)) {
            $output .= $this->displayFormNoAccount($output);
        } else {
            $output .= $this->displayForm();
        }

        return $output;
    }

    public function displayFormNoAccount($output)
    {

        $stylesheets = '<link rel="stylesheet" href="'.$this->_path.'css/bootstrap-cyd.css">';
        $stylesheets .= '<link rel="stylesheet" href="'.$this->_path.'css/checkyourdata-admin-config.css">';
        $output = $stylesheets.$output;

        $languages = Language::getLanguages(true, $this->context->shop->id);
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        /** @var EmployeeCore $employee */
        $employee = $this->context->employee;

        $this->context->smarty->assign(array(
            $this->name.'_form_action' => ' http://' . self::$dcUrl.'actions/prestashop.php?action=register',
            $this->name.'_email' => $employee->email,
            $this->name.'_lastname' => $employee->lastname,
            $this->name.'_firstname' => $employee->firstname,
            $this->name.'_site' => $this->getShopUrl(),
            $this->name.'_languages' => $languages,
            $this->name.'_current_language' => $default_lang,
            $this->name.'_back_url' => $this->getCompleteAdminUrl(),
            $this->name.'_url_app' => 'http://' . self::$dcUrl,
            $this->name.'_url_cgv' => 'http://' . self::$dcUrl . 'cgv.php',

            $this->name.'_form_token_action' => $this->getAdminUrl(),
            $this->name.'_ps_version_class' => 'ps-'.str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3))
        ));

        $output .= $this->display(__FILE__, 'views/templates/admin/configuration_no_account.tpl') ;

        return $output;

    }

    /**
     * Configuration form
     * => compatibility PS1.5+
     * @return string : form html
     */
    public function displayForm($output)
    {

        $stylesheets = '<link rel="stylesheet" href="'.$this->_path.'css/bootstrap-cyd.css">';
        $stylesheets .= '<link rel="stylesheet" href="'.$this->_path.'css/checkyourdata-admin-config.css">';
        $output = $stylesheets.$output;

        // TRACKERS
        $trackers = Configuration::get('checkyourdata_trackers');
        if (!empty($trackers)) {
            $trackers = Tools::jsonDecode($trackers, true);
        } else {
            $trackers = array(
                'ganalytics' => array('active' => false),
                'lengow' => array('active' => false),
                'netaffiliation' => array('active' => false),
            );
        }

        // GOOGLE
        if (!empty($trackers['ganalytics']['ua'])) {
            $checkyourdata_ganalytics_ua = $trackers['ganalytics']['ua'];
        } else {
            $checkyourdata_ganalytics_ua = '';
        }

        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $lang = new Language($default_lang);

        $token = Configuration::get('checkyourdata_token');
        $this->context->smarty->assign(array(
            $this->name.'_form_action' => $this->getAdminUrl(),
            $this->name.'_url_app' => 'http://'.self::$dcUrl,
            $this->name.'_form_id' => 'submitcheckyourdata_update',
            $this->name.'_form_name' => 'submitcheckyourdata_update',
            $this->name.'_token' => $token,
            $this->name.'_ganalytics_ua' => $checkyourdata_ganalytics_ua,
            $this->name.'_current_language' => $lang->iso_code,
            $this->name.'_ps_version_class' => 'ps-'.str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3))
        ));

        $output .= $this->display(__FILE__, 'views/templates/admin/configuration.tpl') ;
        return $output;
    }

    /**
     * Configuration form
     * => PS1.4 only (no HelperForm)
     * @return string : form html
     */
    public function displayFormPs14()
    {
        $this->context->smarty->assign(
            array(
                'action_url' => $_SERVER['REQUEST_URI'],
                'token' => Configuration::get('checkyourdata_token'),
                'trackers' => Tools::jsonDecode(Configuration::get('checkyourdata_trackers'), true),
                'submit_name' => 'submit' . $this->name,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/configuration_form_ps14.tpl');
    }

    /**
     * Aliases for PS1.4 hooks
     */
    public function getShopUrl()
    {
        if (version_compare(_PS_VERSION_,'1.5','<')) {
            return $this->context->link->getPageLink('', true);
        }
        return $this->context->shop->getBaseURL();
    }

    public function hookCancelProduct($params)
    {
        // TODO : PS1.4 refund
    }

    /**
     * Fonctions for PS14
     */
    private function getShippingTotal($order)
    {
        if (version_compare(_PS_VERSION_,'1.5','<')) {
            return $order->total_shipping;
        } else {
            $shipping = $order->getShippingTaxesBreakdown();
            if (count($shipping) > 0 && isset($shipping[0]['total_amount'])) {
                return $shipping[0]['total_amount'];
            }
        }
        return 0;
    }

    private function getProductDefaultCategory($prod)
    {
        if (version_compare(_PS_VERSION_,'1.5','<')) {
            $p = new Product($prod['product_id']);
            return $p->id_category_default;
        }
        return $prod["id_category_default"];
    }

    private function getProductReference($prod)
    {
        if (version_compare(_PS_VERSION_,'1.5','<')) {
            $p = new Product($prod['product_id']);
            return $p->reference;
        }
        return $prod['reference'];
    }

    /**
     * Prepare for sending
     * @param $cartId
     * @return array
     */
    protected function formatDataToSend($cartId)
    {
        $token = Configuration::get('checkyourdata_token');

        $cart = new Cart($cartId);

        // amounts
        $trans = array();
        $totalWithoutTaxes = $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS);
        $total = $cart->getOrderTotal();
        $trans["tax"] = $total - $totalWithoutTaxes;
        $trans["shipping"] = $cart->getOrderTotal(false, Cart::ONLY_SHIPPING);
        $trans["total"] = $total;

        // items
        $products = $cart->getProducts();
        $items = array();

        foreach ($products as $p) {
            $items [$p['id_product'] . '_' . $p['id_product_attribute']] = array(
                'name' => $p['name'],
                'price' => $p['price'],
                'code' => $p['reference'],
                'category' => $p['category'],
                'qty' => $p['cart_quantity'],
            );
        }

        $data = array(
            'token' => $token,
            'action' => 'initOrder',
            'data' => array(
                'total' => $trans["total"],
                'tax' => $trans["tax"],
                'shipping' => $trans["shipping"],
                'cartId' => $cart->id,
                'items' => Tools::jsonEncode($items),
            ),
        );
        return $data;
    }


    /**
     * @param null $cartId current cartId to unset
     * @return array|null
     */
    private function sendCartsInError($cartId = null)
    {
        $cartsToResend = Configuration::get('checkyourdata_carts_in_error');
        if (!empty($cartsToResend)) {
            $aCartsToResend = Tools::jsonDecode($cartsToResend, true);
            if (count($aCartsToResend) > 0) {

                // remove current sent cart from carts in error if in
                if ($cartId && isset($aCartsToResend[$cartId])) {
                    unset($aCartsToResend[$cartId]);
                }
                // try to resend old carts
                $newCartsToResend = array();
                foreach ($aCartsToResend as $cid => $trData) {
                    if (empty($cid)) {
                        continue;
                    }
                    $r = $this->sendInitOrderToApp($cid, $trData);
                    if ($r['state'] != 'ok') {
                        // keep in resend array
                        $newCartsToResend[$cid] = $trData;
                    }
                }
                Configuration::updateValue('checkyourdata_carts_in_error', Tools::jsonEncode($newCartsToResend));
                return $r;
            }
        }
        return null;
    }

    /**
     * @param $orderId
     */
    private function addOrderInError($orderId)
    {
        $ordersInError = Configuration::get('checkyourdata_orders_in_error');
        if (!empty($ordersInError)) {
            $ordersInError = Tools::jsonDecode($ordersInError, true);
        } else {
            $ordersInError = array();
        }
        if (!in_array($orderId, $ordersInError)) {
            $ordersInError[] = $orderId;
            Configuration::updateValue('checkyourdata_orders_in_error', Tools::jsonEncode($ordersInError));
        }
    }

    private function sendOrdersInError()
    {
        $toResend = Configuration::get('checkyourdata_orders_in_error');
        if (!empty($toResend)) {
            $toResend = Tools::jsonDecode($toResend, true);
        } else {
            $toResend = array();
        }
        // and after carts, try to resend old orders
        $newToResend = array();
        foreach ($toResend as $orderId) {
            if (empty($orderId)) {
                continue;
            }
            $r = $this->sendOrderToApp($orderId);
            if ($r['state'] != 'ok') {
                // keep in resend array
                $newToResend[] = $orderId;
            }
        }
        Configuration::updateValue('checkyourdata_orders_in_error', Tools::jsonEncode($newToResend));
    }

    /**
     * @param $cartId
     */
    private function addCartInError($cartId)
    {
        $toResend = Configuration::get('checkyourdata_carts_in_error');
        if (!empty($toResend)) {
            $toResend = Tools::jsonDecode($toResend, true);
        } else {
            $toResend = array();
        }
        if (!isset($toResend[$cartId])) {
            $toResend[$cartId] = CheckYourDataWSHelper::getTrackersData();
            Configuration::updateValue('checkyourdata_carts_in_error', Tools::jsonEncode($toResend));
        }
    }

    /**
     * Returns the admin url.
     * @return string the url.
     */
    protected function getAdminUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Returns the admin url.
     * @return string the url.
     */
    protected function getCompleteAdminUrl()
    {
        return $_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['HTTP_HOST'].$this->getAdminUrl();
    }

}

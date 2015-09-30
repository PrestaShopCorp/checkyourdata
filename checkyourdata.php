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
 *  @author    Thomas RIBIERE <thomas.ribiere@gmail.com>
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
        if (! class_exists('CheckYourDataWSHelper')) {
            include_once dirname(__FILE__).'/wshelper.inc.php';
        }
        
        // Environement configuration
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, 'ribie.re')) {
            // RECETTE
            self::$dcUrl = 'app-preprod.checkyourdata.net/';
        } elseif (strpos($host, 'cyd.com')) {
            // LOCAL
            self::$dcUrl = 'app.cyd.com/';
        }
        
        $this->name = 'checkyourdata';
        $this->tab = 'analytics_stats';
        
        // 1.0.3 => prototype version
        // 1.1.0 => compat PS1.4 + rework + comments
        // 1.1.1 => send data adjust for APP (currency + payment module labels)
        // 1.2.0 => adjusts for APP v1.0
        // 1.2.1 => http / https
        $this->version = '1.2.2';

        $this->author = 'Check Your Data - http://www.checkyourdata.net';
        
        // warnings in admin
        $this->need_instance = 1;
        
        if (_PS_VERSION_ >= '1.5.6.2') {
            // BUG prestashop on v1.5.4.1 => compliancy not used properly
            // BUG fixed on v1.5.6.2.
            $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        }
        
        if (_PS_VERSION_ >= '1.6.0.0') {
            // from PS1.6, theme bootstrap
            $this->bootstrap = true;
        }

        parent::__construct();

        $this->displayName = $this->l('CheckYourData Prestashop Module');
        $this->description = $this->l('Specifically postpones transactions in GoogleAnalytics.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        // Warning if token not set
        if (!Configuration::get('checkyourdata_token')) {
            $this->warning = $this->l('Access key to checkyourdata.net not configured');
        }
        
        if (_PS_VERSION_ < '1.5.0.0') {
            /** Backward compatibility */
            require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
        
        // verify module ganalytics present
        $dcUa = Configuration::get('checkyourdata_ua');
        if ($dcUa != null) {
            $gaUa = '';
            $ga = Module::getInstanceByName('ganalytics');
            if ($ga !== false && $ga->active) {
                // get UA configured
                $gaUa = Configuration::get('GANALYTICS_ID');// PS 14 / PS 15
                if ($gaUa === false) {
                    $gaUa = Configuration::get('GA_ACCOUNT_ID');// PS 16
                }
                // warning if UA of ganalytics module same as checkyourdata module
                if ($gaUa !== false && $gaUa == $dcUa) {
                    $this->warning = $this->l('Your Google Analytics UA tracking id is already set (in GoogleAnalytics module). Tracking might be duplicated in Google Anlaytics. You should desactivate the ganalytics module, or set a new google analytics tracking ID in Checkyourdata module.');
                }
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
        
        if (_PS_VERSION_ < '1.5.0.0') {
            // REFUND
            $ko = $ko || !$this->unregisterHook('cancelProduct');
        } else {
            // REFUND
            if (_PS_VERSION_ < '1.6.0.0') {
                $ko = $ko || !$this->unregisterHook('displayAdminOrder');
            }

            $ko = $ko || !$this->unregisterHook('actionObjectOrderDetailUpdateAfter');
            $ko = $ko || !$this->unregisterHook('displayAdminOrderContentOrder');
        }
        $ko = $ko || !parent::uninstall();
        
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
        
        if (_PS_VERSION_ < '1.5.0.0') {
            // REFUND
            $ko = $ko || !$this->registerHook('cancelProduct');
        } else {
            // REFUND
            if (_PS_VERSION_ < '1.6.0.0') {
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
        $ordersToRefound[$oid][$pid.'_'.$paid] = $qtyRefound;
        
        Configuration::updateValue('checkyourdata_ref_orders', Tools::jsonEncode($ordersToRefound));
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
        
        // get UA configured
        $ua = Configuration::get('checkyourdata_ua');
        if (empty($ua)) {
            // no tracking if CYD UA not set
            return;
        }
        
        $this->context->smarty->assign('ua', $ua);
        
        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }
    
    /**
     * HOOK Payment choice page : JS call to APP, order init
     * @return string : html / JS to add to page
     */
    public function hookPaymentTop()
    {
        // get CYD token
        $token = Configuration::get('checkyourdata_token');
        if (empty($token)) {
            return '';
        }
        // get UA configured
        $ua = Configuration::get('checkyourdata_ua');
        if (empty($ua)) {
            // no tracking if CYD UA not set
            return;
        }
        
        // data to send
        // get order (cart)
        $cart = $this->context->cart;

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
            $items [$p['id_product'].'_'.$p['id_product_attribute']] = array(
                'name'=>$p['name'],
                'price'=>$p['price'],
                'code'=>$p['reference'],
                'category'=>$p['category'],
                'qty'=>$p['cart_quantity'],
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
        $enc = CheckYourDataWSHelper::encodeData($data);

        // add JS vars
        $this->context->smarty->assign(
            array(
                'url' => '//'.self::$dcUrl.'ws/',
                'data' => 'k='.$enc['key'].'&d='.$enc['data'],
            )
        );

        return $this->display(__FILE__, 'views/templates/hook/payment-top.tpl');
    }
    
    /**
     * HOOK order state change
     * @param type $params : array containing order details
     */
    public function hookUpdateOrderStatus($params)
    {
        $token = Configuration::get('checkyourdata_token');
        if (empty($token)) {
            return;
        }
        $nextState = $params['newOrderStatus']->id;
        
        $order = new Order($params['id_order']);
        
        if (!Validate::isLoadedObject($order)) {
            // no order
            return;
        }
        $currentState = $order->getCurrentState();
        if ($currentState === false) {
            $currentState = '0';
        }

        // conversion rate
        $conversion_rate = 1;
        $currency = new Currency((int) $order->id_currency);
        if ($order->id_currency != Configuration::get('PS_CURRENCY_DEFAULT')) {
            $conversion_rate = (int) $currency->conversion_rate;
        }

        // amounts (with taxes on shipping)
        $tax = $order->getTotalProductsWithTaxes() - $order->getTotalProductsWithoutTaxes();
        $tax += $this->getShippingTotal($order);

        // Order general information
        $trans = array(
            'id' => (int) $order->id,
            'cartId' => (int) $order->id_cart,
            'store' => htmlentities(Configuration::get('PS_SHOP_NAME')),
            'total' => Tools::ps_round((float) $order->total_paid / (float) $conversion_rate, 2),
            'shipping' => Tools::ps_round((float) $order->total_shipping / (float) $conversion_rate, 2),
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

            $items [$p['product_id'].'_'.$p['product_attribute_id']] = array(
                'name'=>$p['product_name'],
                'price'=>Tools::ps_round((float)$p['product_price_wt'] / (float)$conversion_rate, 2),
                'code'=>$this->getProductReference($p),
                'category'=> implode(" ", $categ->name),
                'qty'=>$p['product_quantity'],
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
        // errors
        if ($res['state'] != 'ok') {
            error_log('Checkyourdata WS Update Order error : '.implode("\n", $res['errors']));
        }
    }
    
    private function sendShopParamsToApp($token)
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

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
            $modules [$pm['id_module']] = $p->displayName;
        }
        
        $data = array(
            'token' => $token,
            'action' => 'setShopParams',
            'data' => array(
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

        if (Tools::isSubmit('submit' . $this->name)) {
            // Google UA
            $ua = (string) Tools::getValue('checkyourdata_ua');
            if (!Validate::isGenericName($ua)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('checkyourdata_ua', $ua);
                $output .= $this->displayConfirmation($this->l('UA updated'));
            }
            // TOKEN
            $token = (string) Tools::getValue('checkyourdata_token');
            if (empty($token) || !Validate::isGenericName($token)) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('checkyourdata_token', $token);
                $output .= $this->displayConfirmation($this->l('Token updated'));
                
                // send params to APP if token set
                $res = $this->sendShopParamsToApp($token);
                if ($res['state'] == 'ok') {
                    $output .= $this->displayConfirmation(
                        sprintf($this->l('Configuration saved on %s'), 'https://'.self::$dcUrl)
                    );
                }
            }
        }
        $errs = Configuration::get('checkyourdata_last_errors');
        if (!empty($errs)) {
            $output .= $this->displayError($errs);
        }
        
        if (_PS_VERSION_ < '1.5.0.0') {
            // HelperForm not defined in PS 1.4
            $output .= $this->displayFormPs14();
        } else {
            $output .= $this->displayForm();
        }
        
        // header image
        $token = Configuration::get('checkyourdata_token');
        if (empty($token)) {
            $img = 'no_account.png';
            $link = 'signin.php';
        } else {
            // random image (from 1 to 5)
            $img = 'com'.rand(1, 5).'.png';
            $link = '';
        }
        
        $this->context->smarty->assign(
            array(
                'img_url' => '//'.self::$dcUrl.'img/'.$img,
                'link_url' => '//'.self::$dcUrl.$link,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl').$output;
    }
    
    /**
     * Configuration form
     * => compatibility PS1.5+
     * @return string : form html
     */
    public function displayForm()
    {
        // Get default language
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Access key to CheckYourData'),
                    'name' => 'checkyourdata_token',
                    'size' => 20,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Google UA tracking ID, to add universal tracking on your site.'),
                    'name' => 'checkyourdata_ua',
                    'size' => 20,
                    'required' => false
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $token = Configuration::get('checkyourdata_token');
        $helper->fields_value['checkyourdata_token'] = $token;
        $helper->fields_value['checkyourdata_ua'] = Configuration::get('checkyourdata_ua');

        return $helper->generateForm($fields_form);
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
                'action_url' => Tools::safeOutput($_SERVER['REQUEST_URI']),
                'token' => Configuration::get('checkyourdata_token'),
                'ua' => Configuration::get('checkyourdata_ua'),
                'submit_name' => 'submit'.$this->name,
            )
        );
        return $this->display(__FILE__, 'views/templates/admin/configuration_form_ps14.tpl');
    }

    /**
     * Aliases for PS1.4 hooks
     */
    public function hookCancelProduct($params)
    {
        // TODO : PS1.4 refund
    }

    /**
     * Fonctions for PS14
     */
    private function getShippingTotal($order)
    {
        if (_PS_VERSION_ < '1.5.0.0') {
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
        if (_PS_VERSION_ < '1.5.0.0') {
            $p = new Product($prod['product_id']);
            return $p->id_category_default;
        }
        return $prod["id_category_default"];
    }
    private function getProductReference($prod)
    {
        if (_PS_VERSION_ < '1.5.0.0') {
            $p = new Product($prod['product_id']);
            return $p->reference;
        }
        return $prod['reference'];
    }
}

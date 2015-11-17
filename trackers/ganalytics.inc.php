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

class CheckYourDataGAnalytics
{

    public static function init($cydUa, & $cydModule)
    {
        // verify module ganalytics present
        if ($cydUa != null) {
            $gaUa = '';
            $ga = Module::getInstanceByName('ganalytics');
            if ($ga !== false && $ga->active) {
                // get UA configured
                $gaUa = Configuration::get('GANALYTICS_ID');// PS 14 / PS 15
                if ($gaUa === false) {
                    $gaUa = Configuration::get('GA_ACCOUNT_ID');// PS 16
                }
                // warning if UA of ganalytics module same as checkyourdata module
                if ($gaUa !== false && $gaUa == $cydUa) {
                    return $cydModule->l('Your Google Analytics UA tracking id is already set (in GoogleAnalytics module). Tracking might be duplicated in Google Anlaytics. You should desactivate the ganalytics module, or set a new google analytics tracking ID in Checkyourdata module.');
                }
            }
        }
        return '';
    }
    
    public static function hookHeader($ua)
    {
        if (empty($ua)) {
            // no tracking if CYD UA not set
            return;
        }

        $res = array(
            'tpl'=> array(
                'file'=>'ganalytics/header.tpl',
                'smarty'=>array('ua' => $ua),
            ),
        );
        
        return $res;
    }
    
    public static function hookPaymentTop($ua, $cart)
    {
        if (empty($ua)) {
            // no tracking if CYD UA not set
            return;
        }
        
        CheckYourDataWSHelper::addTrackerData('ganalytics.gcid', self::getGCID());
    }
    
    private static function getGCID()
    {
        $cookies = Context::getContext()->cookie;
        if (!empty($cookies) && isset($cookies['_ga'])) {
            $gcid = preg_replace('@^GA[0-9]\.[0-9]\.@', '', $cookies['_ga']);
            return $gcid;
        }
        return '';
    }
}

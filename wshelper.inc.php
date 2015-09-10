<?php
/**
* 2015 CheckYourDataWSHelper
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

class CheckYourDataWSHelper
{
    /**
     * Encode data in json and send with APP CYD pubkey
     * @param type $data : data array
     * @return string : encoded string
     */
    public static function encodeData($data)
    {
        $retour = false;
        
        // Get APP public key
        $dir = dirname(__FILE__);
        $pubkey = openssl_pkey_get_public('file://'.$dir.'/ssl/publickey.cer');
        
        // encoding
        if ($pubkey !== false) {
            $ekeys = array();
            $crypted = '';
            if (openssl_seal(Tools::jsonEncode($data), $crypted, $ekeys, array($pubkey))) {
                $retour = array(
                    'key' => urlencode($ekeys[0]),
                    'data' => urlencode($crypted),
                );
            }
            openssl_free_key($pubkey);
        }
        
        return $retour;
    }
    
    /**
     * WS call APP
     * @param type $data : data array
     * @return array : WS return JSON decoded
     */
    public static function send($url, $data)
    {
        $url = 'http://'.$url.'ws/';
        
        $enc = self::encodeData($data);
        $dat = 'k='.$enc['key'].'&d='.$enc['data'];
        
        if (function_exists('curl_init')) {
            // if curl active
            $c = curl_init($url);
            curl_setopt($c, CURLOPT_POST, true);
            curl_setopt($c, CURLOPT_POSTFIELDS, $dat);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($c);
        } else {
            // otherwise : file_get_contents
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $dat
                )
            );
            $context = stream_context_create($opts);
            $res = Tools::file_get_contents($url, false, $context);
        }
        
        $ret = Tools::jsonDecode($res, true);
        Configuration::updateValue('checkyourdata_last_errors', implode(', ', $ret['errors']));
        
        return $ret;
    }
}

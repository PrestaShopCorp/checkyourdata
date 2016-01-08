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
<script>
ga(function() {
    var tracker = ga.getByName('checkyourdata_ga');
    if(tracker == null){
        console.log('Tracker DC not found');
        return;
    }
    var clientId = tracker.get('clientId');
    {literal}$.ajax('{/literal}{$url|escape:'javascript':'UTF-8'}{literal}',{
        method:'POST',
        data:'{/literal}{$data|escape:'javascript':'UTF-8'}{literal}&& +&gcid='+clientId
    }{/literal});
});
</script>
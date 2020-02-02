#!/usr/bin/php

<?php
echo '<html><head><meta charset="utf-8" /></head>
	<body style="background-image: url(https://justvps.com/assets/justvps/img/main-bg.png); background-color: #ecf7ff; font-family: Ubuntu, sans-serif;  font-weight: 600;  font-style: normal;">
	<a href="https://my.justvps.com/billmgr"><img src="https://my.justvps.com/manimg/orion/local_b911442061ac/logo.svg"></a><center>';
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "AdvCashresult");

require_once 'bill_util.php';

$param = CgiInput(true);
$page = '';
if ($param["ac_transaction_status"] == "COMPLETED") { // оплата прошла успешно
	LocalQuery("payment.setpaid", array("elid" => $param["ac_order_id"], ));  // зачисляем платеж и формируем страницу успешного платежа
	$page.= "<h2>Payment was successful</h2>
	Justvps thanks you for using our services. A payment of ".$param["ac_amount"]." ".$param["ac_merchant_currency"]." is enlisted.
	<h3>Payment details:</h3><table>
	<tr><td>Customer's Advanced Cash wallet number:</td><td>".$param["ac_src_wallet"]."</td></tr>
	<tr><td>Merchant's Advanced Cash wallet number:</td><td>".$param["ac_dest_wallet"]."</td></tr>
	<tr><td>The amount credited to the Seller's wallet:</td><td>".$param["ac_amount"]."</td></tr>
	<tr><td>Amount billed to the Buyer:</td><td>".$param["ac_merchant_amount"]."</td></tr>
	<tr><td>Currency of the amount credited to the Seller's wallet: </td><td>".$param["ac_merchant_currency"]."</td></tr>
	<tr><td>Commission deducted by the Advanced Cash system from the Buyer's account:</td><td>".$param["ac_fee"]."</td></tr>
	<tr><td>Seller's store name:</td><td>".$param["ac_sci_name"]."</td></tr>
	<tr><td>Date time of operation:</td><td>".$param["ac_start_date"]." </td></tr>
	<tr><td>The order number:</td><td>".$param["ac_order_id"]." </td></tr>
	<tr><td>Payment system:</td><td>".$param["ac_ps"]."</td></tr>
	<tr><td>Transaction status:</td><td>".$param["ac_transaction_status"]."</td></tr>
	<tr><td>Review for payment:</td><td>".$param["ac_comments"]." </td></tr>
	<tr><td>Buyer's mail:</td><td>".$param["ac_buyer_email"]."</td></tr>
	</table>"; 
	$timer_js = " <script> var i=7;
			function return_ac () {
				document.getElementById('return_button').value = 'Return to your account ' + i + '  sec';
				i--;
				if (i<0) document.return_account.submit();
			}
			setInterval(return_ac,1000);
	</script>";
}  else {  // оплата не прошла, формируем страницу ошибки оплаты
	$page.= "<center><h2>Failed payment! :(</h2>
	Something went wrong, try again 
	<h3>Payment details:</h3><table>
	<tr><td>Merchant's Advanced Cash wallet number:</td><td>".$param["ac_dest_wallet"]."</td></tr>
	<tr><td>Order amount:</td><td>".$param["ac_amount"]."</td></tr>
	<tr><td>The currency of the order:</td><td>".$param["ac_merchant_currency"]."</td></tr>
	<tr><td>Seller's store name:</td><td>".$param["ac_sci_name"]."</td></tr>
	<tr><td>The order number:</td><td>".$param["ac_order_id"]." </td></tr>
	</table>"; 
	$timer_js = '';
}
echo $page."<br><br><form action='https://my.justvps.com/billmgr' name='return_account'><input style='
	background: #0071ff;
    border-radius: 4px;
    font-size: 15px;
    padding: 8px 30px;
    color: #fff;' 
    type='submit' id='return_button' value='Return to your account'></form></center>".$timer_js."</body></html>";
Debug("out: ". implode($param));
?>
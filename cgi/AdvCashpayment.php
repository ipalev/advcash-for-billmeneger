#!/usr/bin/php
<?php
set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "AdvCash");

require_once 'bill_util.php';

echo "Content-Type: text/html\n\n";
$client_ip = ClientIp();
$param = CgiInput();

//$param["auth"] =123; $param["elid"]=23;

if ($param["auth"] == "") {
	echo "no auth info";
	throw new Err("no auth info");
} else {
	$info = LocalQuery("payment.info", array("elid" => $param["elid"], ));
	$elid = (string)$info->payment[0]->id;
	$amount = (string)$info->payment[0]->paymethodamount;
	$currency = (string)$info->payment[0]->currency[1]->iso;
	$comment = (string)$info->payment[0]->description;
	$ac_account_email = (string)$info->payment[0]->paymethod[1]->ac_account_email;
	$ac_sci_name = (string)$info->payment[0]->paymethod[1]->ac_sci_name;
	$return_url = (string)$info->payment[0]->paymethod[1]->return_url;
	$secret = (string)$info->payment[0]->paymethod[1]->secret;
	$ac_sign = hash('sha256', $ac_account_email.":".$ac_sci_name.":".$amount.":".$currency.":".$secret.":".$elid);
	Debug("поля для сигнатуры: ".$ac_account_email.":".$ac_sci_name.":".$amount.":".$currency.":".$secret.":".$elid." сигнатура: ".$ac_sign);	

	echo "<html>\n";
	echo "<head>\n";
	echo "	<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />\n";
	echo "	<link rel='shortcut icon' href='billmgr.ico' type='image/x-icon' />\n";
	echo "	<script language='JavaScript'>\n";
	echo "		function DoSubmit() {\n";
	echo "			document.AdvCash.submit();\n";
	echo "		}\n";
	echo "	</script>\n";
	echo "</head>\n";
	echo "<body onload='DoSubmit()'>\n";
	echo '<form name="AdvCash" action="https://wallet.advcash.com/sci/" method="post">
				 <input type="hidden" name="ac_account_email" value="'.$ac_account_email.'" />
		         <input type="hidden" name="ac_sci_name" value="'.$ac_sci_name.'" />
      		     <input type="hidden" name="ac_amount" value="'.$amount.'" />
   		         <input type="hidden" name="ac_currency" value="'.$currency.'" />
    		     <input type="hidden" name="ac_order_id" value="'.$elid.'" />
         		 <input type="hidden" name="ac_sign" value="'.$ac_sign.'" />
         
         		 <input type="hidden" name="ac_success_url" value="'.$return_url.'" />
         		 <input type="hidden" name="ac_success_url_method" value="GET" />
         		 <input type="hidden" name="ac_fail_url" value="'.$return_url.'" />
         		 <input type="hidden" name="ac_fail_url_method" value="GET" />
         		 <input type="hidden" name="ac_status_url" value="'.$return_url.'" />
         		 <input type="hidden" name="ac_status_url_method" value="GET" />
         		 <input type="hidden" name="ac_comments" value="'.$comment.'" />
         		 <input type="hidden" name="ac_client_lang" value="en" />
			  </form>';
	echo "</body>\n";
	echo "</html>\n";
}
?>
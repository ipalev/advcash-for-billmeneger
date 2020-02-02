#!/usr/bin/php
<?php

set_include_path(get_include_path() . PATH_SEPARATOR . "/usr/local/mgr5/include/php");
define('__MODULE__', "AdvCash");
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('max_execution_time', 0);
require_once 'MerchantWebService.php';
require_once 'bill_util.php';

function send_maney ($amount, $currency, $mail_send, $mail_get, $api_name, $api_pass, $note='') {
	$merchantWebService = new MerchantWebService();

	$arg0 = new authDTO();
	$arg0->apiName = $api_name;
	$arg0->accountEmail = $mail_send;
	$arg0->authenticationToken = $merchantWebService->getAuthenticationToken($api_pass);

	$arg1 = new sendMoneyRequest();
	$arg1->amount = (float)$amount;
	$arg1->currency = $currency;
	$arg1->email = $mail_get;
	//$arg1->walletId = "U000000000000";
	$arg1->note = $note;
	$arg1->savePaymentTemplate = false;

	$validationSendMoney = new validationSendMoney();
	$validationSendMoney->arg0 = $arg0;
	$validationSendMoney->arg1 = $arg1;

	$sendMoney = new sendMoney();
	$sendMoney->arg0 = $arg0;
	$sendMoney->arg1 = $arg1;
	Debug($amount." | ".$currency." | ".$mail_send." | ".$mail_get." | ".$note." | ".$api_name." | ".$api_pass);	
	try {
  		$merchantWebService->validationSendMoney($validationSendMoney);
  		$sendMoneyResponse = $merchantWebService->sendMoney($sendMoney);
  		Debug("true send");
		return true;
	} catch (Exception $e) {
  	 	echo "ERROR MESSAGE => " . $e->getMessage() . "<br/>";
   		echo $e->getTraceAsString();
   		Debug("false send ".$e);
   		return false;
	}
}

$longopts  = array
(
    "command:",
    "payment:",
    "amount:",
);
$options = getopt("", $longopts);

try {
	$command = $options['command'];
	Debug("command ". $options['command']);

	if ($command == "config") {
		$config_xml = simplexml_load_string($default_xml_string);
		$feature_node = $config_xml->addChild("feature");
		$feature_node->addChild("refund", 		"on");
		//$feature_node->addChild("rfvalidate", 	"on");
		$feature_node->addChild("rfset", 			"on");
		$feature_node->addChild("transfer", 		"on");
		//$feature_node->addChild("tfvalidate", 	"on");		
		$feature_node->addChild("tfset", 			"on");
		$feature_node->addChild("redirect", 		"on");
		$feature_node->addChild("notneedprofile", 	"on");
		$param_node = $config_xml->addChild("param");
		$param_node->addChild("payment_script", "/mancgi/AdvCashpayment.php");
		echo $config_xml->asXML();
	} elseif ($command == "pmtune") {
		$paymethod_form = simplexml_load_string(file_get_contents('php://stdin'));
		Debug($paymethod_form->asXML());
		echo $paymethod_form->asXML();
	} elseif ($command == "tfvalidate") {
		$paymethod_form = simplexml_load_string(file_get_contents('php://stdin'));
		Debug($paymethod_form->asXML());
		echo $paymethod_form->asXML();
	} elseif ($command == "rfset" || $command == "tfset") {
		$paymethod_form = simplexml_load_string(file_get_contents('php://stdin'));
		$elid = $paymethod_form->source_payment;
		$info = LocalQuery("payment.info", array("elid" => $elid, ));
		$mail_get = (string)$info->payment[0]->useremail;
		$currency = (string)$info->payment[0]->currency[1]->iso;
		$amount = (-1)*(real)$paymethod_form->payment_paymethodamount;
		$note = $paymethod_form->payment_description;
		$xml_params = simplexml_load_string($paymethod_form->paymethod_xmlparams);
		$mail_send = $xml_params->ac_account_email;
		$api_name = $xml_params->api_name;
		$api_pass = $xml_params->api_pass;
		Debug($paymethod_form->asXML());
		if (send_maney($amount, $currency, $mail_send, $mail_get, $api_name, $api_pass, $note)) echo $paymethod_form->asXML();
	}	
	else {
		throw new Error("unknown command");
	}
} catch (Exception $e) {
	echo $e;
}
?>
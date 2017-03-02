<?php

// This file is responsible for receiving the response from the
// payment provider and forward it to the application in a standardized way.

require_once(dirname(__FILE__) . "/../PSPInterface.php"); // Callback is called directly by Payment Service Provider, so we need to include PSPInterface.php

if (isset($_GET["AppContinue"]) === true)
{
	PSP::RedirectToContinueUrl($_GET["AppContinue"]);
}
else if (isset($_GET["AppCallback"]) === true)
{
	// TODO: Extract information from request required to identify transaction,
	// and then call the application callback initially provided to RedirectToPaymentForm(..)
	// to inform the application that a payment was successfully carried through.

	$cfg = PSP::GetConfig("Example");

	$transactionId = $_POST["transaction"];
	$orderId = $_POST["order"];
	$amount = $_POST["amount"];
	$currency = $_POST["currency"];

	// Log request - nothing will be logged if logging is disabled in PSPI
	PSP::Log("Example - Callback.php : AppCallback - TransactionID=" . $transactionId . ", OrderID=" . $orderId . ", Amount=" . $amount . ", Currency=" . $currency);

	// Invoke applicaton callback specified in RedirectToPaymentForm(..).
	// Using PSP::InvokeCallback(..) which implements security measures
	// to prevent man-in-the-middle attacks.
	// Application callback must call PSP::GetCallbackData() to securely obtain data, which
	// is returned as an associative array - e.g. array("TransactionId" => "893R-993-887923219", "OrderId" => "0042", "Amount" => 32585, "Currency" => "USD");
	PSP::InvokeCallback($_GET["AppCallback"], $transactionId, $orderId, (int)$amount, $currency);
}

?>

<?php

require_once(dirname(__FILE__) . "/../../PSPInterface.php");

$data = PSP::GetCallbackData(); // Obtain data passed to callback

$transactionId = $data["TransactionId"];	// String
$orderId = $data["OrderId"];				// String
$amount = $data["Amount"];					// Integer
$currency = $data["Currency"];				// String

if (PSP::GetDebugMail() !== "")
	mail(PSP::GetDebugMail(), "DIBS - custom callback invoked", "TransactionId: " . $transactionId . "\nOrderId: " . $orderId . "\nAmount: " . $amount . "\nCurrency: " . $currency);

// Immediately capture or cancel payment

if ($transactionId !== "") // Empty if Capture/Cancel is not supported
{
	$p = PSP::GetPaymentProvider("DIBS");
	$result = false;

	if ($amount <= 30000) // Capture payment if equal to or below DKK 300.00
		$result = $p->CapturePayment($transactionId, $amount);
	else // Cancel, we do not accept payments larger than DK 300.00
		$result = $p->CancelPayment($transactionId);
}

?>

<?php

require_once(dirname(__FILE__) . "/../../PSPInterface.php");

// Securely obtain data passed to callback

$data = PSP::GetCallbackData();

$transactionId = $data["TransactionId"];	// String
$orderId = $data["OrderId"];				// String
$amount = $data["Amount"];					// Integer
$currency = $data["Currency"];				// String

// Log callback - nothing will be logged if logging is disabled in PSPI
PSP::Log("Payment successfully received! TransactionId: " . $transactionId . ", OrderId: " . $orderId . ", Amount: " . $amount . ", Currency: " . $currency);

// Immediately capture or cancel payment, depending on the amount

$p = PSP::GetPaymentProvider("Example");
$result = false;

if ($amount <= 50000) // Capture payment if equal to or below USD 500.00
{
	$result = $p->CapturePayment($transactionId, $amount);
}
else // Cancel, we do not accept payments larger than USD 500.00
{
	$result = $p->CancelPayment($transactionId);
}

?>

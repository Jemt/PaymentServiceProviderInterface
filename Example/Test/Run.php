<?php

// Example of how ANY application can communicate
// with ANY Payment Service Provider (PSP) using the
// Payment Service Provider Interface (PSPI).

require_once(dirname(__FILE__) . "/../../PSPInterface.php");

// Charge USD 300.50
$orderId = "1041";
$amount = 30050;
$currency = "USD";

// Configure callbacks.
// Notice: URL parameters are not allowed.
// TransactionId, OrderId, Amount, and Currency is passed to CustomCallback.php via POST.
$continueUrl = PSP::GetProviderUrl("DIBS") . "/Test/ThanksPage.php";
$callbackUrl = PSP::GetProviderUrl("DIBS") . "/Test/CustomCallback.php";

$p = PSP::GetPaymentProvider("Example");
$p->RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl, $callbackUrl);

?>

<?php

// Example of how ANY application can communicate
// with ANY Payment Service Provider (PSP) using the
// standardized payment interface (PSPI).

require_once(dirname(__FILE__) . "/../../PSPInterface.php");

$orderId = "1041";
$amount = 10;
$currency = "DKK";

// Notice: URL parameters are not allowed.
// TransactionId, OrderId, Amount, and Currency is passed to CustomCallback.php via POST.
$continueUrl = PSP::GetProviderUrl("DIBS") . "/Test/ThanksPage.php";
$callbackUrl = PSP::GetProviderUrl("DIBS") . "/Test/CustomCallback.php";

$p = PSP::GetPaymentProvider("DIBS");
$p->RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl, $callbackUrl);

?>

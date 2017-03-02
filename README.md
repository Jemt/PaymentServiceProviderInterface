# Standardized interface for carrying out online payments

The goal with this project is to provide a **common** and **standardized**
PHP interface for carrying out online payments, no matter what payment
provider has been chosen.
We call this *Payment Service Provider Interface (PSPI)*.

All Payment Service Providers (PSPs) define their own API and interface,
making it difficult and time consuming to add support for multiple payment
providers in applications and web shops.

PSPI solves this by exposing a very simple interface that allow developers
to rapidly implement support for online payments in PHP applications.

The PSPI solution is currently being incorporated into http://jsshop.org
which will ship with the next version of Sitemagic CMS (http://sitemagic.org)
around summer 2016. Follow the projects here: https://www.facebook.com/SitemagicCMS

**Code snippet**

The code below demonstrates how easy it is to receive payments.

*Payment.php*
```
<?php

require_once("PSP/PSPInterface.php");

$orderId = "34781"; // Unique Order ID
$amount = 30000;    // USD 300.00
$currency = "USD";  // Currency, ISO 4217
$continueUrl = "http://example.com/Thanks.html";  // User is returned to this page after a successful payment
$callbackUrl = "http://example.com/Callback.php"; // Called by Payment Provider when payment has been carried through

$p = PSP::GetPaymentProvider("DIBS"); // Use DIBS as payment provider
$p->RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl, $callbackUrl);

?>
```

The code below demonstrates how easy it is to register a successful payment.
In this example we capture (withdraw) the money if the amount is below USD 300,
or cancel the payment if the amount is above USD 300.

*Callback.php*
```
<?php

require_once("PSP/PSPInterface.php");

// Get payment information

$data = PSP::GetCallbackData(); // Securely obtain data passed to callback

$transactionId = $data["TransactionId"];  // String
$orderId = $data["OrderId"];              // String
$amount = $data["Amount"];                // Integer
$currency = $data["Currency"];            // String

// Capture (withdraw) or cancel payment

$p = PSP::GetPaymentProvider("DIBS"); // Use DIBS as payment provider

if ($amount < 30000)
  $p->CapturePayment($transactionId, $amount);
else
  $p->CancelPayment($transactionId);

?>
```

**Configuration**

The Payment Service Provider Interface requires some configuration to work.
First som PSPI specific configuration is set.

*Config.php*
```
<?php

$config = array
(
	// Config array defined by Payment Service Provider Interface.
	// Information MUST be supplied in order for PSPI to work properly.
	
	// Random value used to encrypt data to prevent man-in-the-middle attacks.
	"EncryptionKey" => "-mjhf6/43kBSD&24*f.GL;4917fd@DMBv_IQ512",
	
	// URL to folder containing PSPI package.
	"BaseUrl" => "http://example.com/libs/PSPI",
	
	// Path to log file.
	// WARNING: Log may contain sensitive information so use with caution!
	"LogFile" => "../../../logs/PSPI.log",
	
	// Log Mode - possible values are: Disabled, Simple, or Full.
	// WARNING: Log may contain sensitive information so use with caution!
	"LogMode" => "Disabled"
);

?>
```

Next the PSPM (Payment Service Provider Module) used must also be configured.
What configuration is required depends completely on the provider. The example
below shows what is required to use DIBS.

*DIBS/Config.php*
```
<?php

// The values below are provided by DIBS, or
// can be found in the DIBS administration system.

$config = array
(
	"Merchant ID"           => "12345678",
	"Encryption Key 1"      => "jd84hgGY/_7fe3D@45v.2D#1",  // OPTIONAL
	"Encryption Key 2"      => "-/dKKyJ62B.S?3:372JF/24G",  // OPTIONAL
	"API User: Username"    => "ExternalUser",              // Required by Cancel API call
	"API User: Password"    => "8e.76@5J_Sl/nq:61"          // Required by Cancel API call
);

?>
```

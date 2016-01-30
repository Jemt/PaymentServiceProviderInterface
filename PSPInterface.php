<?php

// Terminology:
// PSP:		"Payment Service Provider" - a company providing payment services
// PSPI:	"Payment Service Provider Interface" - standardized interface for carrying out online payments
// PSPM:	"Payment Service Provider Module" - module implementing PSPI and exposing payment functionality in a standardized way

interface PSPI // Payment Service Provider Interface
{
	// ==================================================
	// Configuration
	// ==================================================

	// Each PSP may require custom settings to work, e.g. WebService URL, authentication details, etc.
	// The following functions allow PSPM to expose required settings, and receive them from the application.

	// Returns configuration elements required for PSPM to work.
	// Data is returned as a simple associative array containg key/value pairs as strings.
	// Array returned can be used to construct a simple configuration form with the
	// required settings and place holder values helping the user to enter appropriate values.
	// E.g. array("Username" => "user@domain.com", "Password" => "*******", "Security token" => "778-r23hfi_2378GE");
	public function GetConfigurationTemplate();

	// Configure PSPM - receives e.g. username/password, security token, success/failure URL, etc. from application.
	// Data format is a simple associative array containing key/value pairs as strings.
	public function Configure($config);

	// Returns link to online documentation (configuration help)
	public function GetDocumentationLink();

	// ==================================================
	// Transaction support (requires PCI certification)
	// ==================================================

	// Authorize and Subscribe is provided with an application specific Order ID used
	// to identify an order/payment. The PSP may use another type of ID to identify
	// transactions. The mapping between Order IDs and Transaction IDs are achieved
	// using the PaymentResult object.
	// Currencies are specified using ISO 4217 (e.g. "USD"): https://en.wikipedia.org/wiki/ISO_4217
	// The amount is specified using the lowest possible unit (e.g. cents for USD).
	// All communication is synchronous - a request is made, and the application waits for the response.
	// Asynchronous/Non-blocking behaviour may be achieved using threading/AJAX.
	// All functions related to transactions return a PaymentResult object containing
	// the Order ID determined by the application, a PSP specific Transaction ID used by the
	// application for further processing of the payment (e.g. Renew or Capture), a Result Code
	// indicating success/failure, and an optional message useful for providing additional error details.

	public function Authorize($orderId, $amount, $currency, $cardNumber, $cardExpireYear, $cardExpireMonth, $cardVerificationNumber);
	public function Subscribe($orderId, $amount, $currency, $interval);
	public function Renew($transactionId); // Renew authorization
	public function Capture($transactionId, $partialAmount);
	public function Cancel($transactionId);
	public function Refund($transactionId, $amount);
	public function Payout(/* TDB */);

	// ==================================================
	// Payment window/form
	// ==================================================

	// Redirect user to payment form - returns nothing,
	// but $callbackUrl is NOT supposed to receive the PSP specific
	// result. Rather, the PSPM is responsible for providing a callback
	// URL to the PSP that receives the result, and "forwards" it in a
	// standardized way to the URL set in $callbackUrl.
	// $callbackUrl is expected to receive the following information via POST:
	// Verified				: boolean (indicates whether transaction was considered valid and secure)
	// OrderId				: string
	// Amount				: integer (in lowest unit, e.g. cents)
	// Currency				: string (ISO 4217)
	public function RedirectToPaymentForm($orderId, $amount, $currency, $callbackUrl);
}

class PaymentResult
{
	public $OrderId = "";
	public $TransactionId = "";
	public $ResultCode = -1;
	public $Message = "";
}


// ==================================================
// Result codes
// ==================================================

// -1		Unknown result
// 0		Success
// 100		Connection error
// 200		Insufficient funds
// 300		...

?>

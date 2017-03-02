<?php

// PSPM - Payment Service Provider Module.
// Must define a class with the same name as the folder in which
// this file is located, and implement the PSPI interface.
// Do not include PSPInterface.php - PSPM is instantiated
// from within the PSP system, so all necessary resources
// will be loaded runtime.

class Example implements PSPI
{
	// String, integer, string, string, string
	public function RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl = null, $callbackUrl = null)
	{
		$cfg = PSP::GetConfig("Example");

		// TODO:
		// Redirect user to Payment Window/Form.
		// Associate the payment with $orderId. Charge $amount (e.g. 32585 is equal to 325.85)
		// in $currency (e.g. USD or EUR). Make sure user is redirected to $continueUrl
		// when payment is carried through.
		// Also make sure payment provider calls PSP::GetProviderUrl("Example") . "/Callback.php"
		// which is responsible for extracting the unique Transaction ID and call $callbackUrl
		// to inform the application that the payment was carried through. See Callback.php for details.

		// Example code - redirect user with form using auto submit

		echo '
		<form id="PaymentForm" method="POST" action="https://example.com/api/form">
			<input type="hidden" name="merchant" value="' . $cfg["Merchant ID"] . '">
			<input type="hidden" name="orderid" value="' . $orderId . '">
			<input type="hidden" name="amount" value="' . $amount . '">
			<input type="hidden" name="currency" value="' . $currency . '">
			<input type="hidden" name="callbackurl" value="' . PSP::GetProviderUrl("Example") . '/Callback.php?AppCallback=' . $callbackUrl . '">
			<input type="hidden" name="continueurl" value="' . PSP::GetProviderUrl("Example") . '/Callback.php?AppContinue=' . $continueUrl . '">
			' . ((PSP::GetTestMode() === true) ? '<input type="hidden" name="test" value="true">' : '') . '
		</form>

		<script type="text/javascript">
			setTimeout(function() { document.getElementById("PaymentForm").submit(); }, 100);
		</script>
		';

		exit; // Prevent additional output by terminating script
	}

	// String, integer - returns True on success, False on failure
	public function CapturePayment($transactionId, $amount)
	{
		// TODO: Capture $amount from transaction identified by $transactionId.

		$cfg = PSP::GetConfig("Example");

		$data = array
		(
			"merchant"		=> $cfg["Merchant ID"],
			"transact"		=> $transactionId,
			"amount"		=> (string)$amount
		);

		$response = PSP::Post("http://example.com/api/capture", $data);

		// Log response - nothing will be logged if logging is disabled in PSPI
		PSP::Log("Example - CapturePayment: TransactionID=" . $transactionId . ", Amount: " . $amount . " - Result: " . $response);

		return ($response === "OK"); // Return True on success, otherwise False
	}

	// String - returns True on success, False on failure
	public function CancelPayment($transactionId)
	{
		// TODO: Cancel transaction identified by $transactionId.

		$cfg = PSP::GetConfig("Example");

		$data = array
		(
			"merchant"		=> $cfg["Merchant ID"],
			"transact"		=> $transactionId
		);

		$response = PSP::Post("http://example.com/api/cancel", $data);

		// Log response - nothing will be logged if logging is disabled in PSPI
		PSP::Log("Example - CancelPayment: TransactionID=" . $transactionId . " - Result: " . $response);

		return ($response === "OK"); // Return True on success, otherwise False
	}
}

?>

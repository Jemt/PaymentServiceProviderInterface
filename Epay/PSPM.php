<?php

class Epay implements PSPI
{
	public function RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl = null, $callbackUrl = null)
	{
		throw new Exception("Missing implementation");
	}

	public function CapturePayment($transactionId, $amount)
	{
		throw new Exception("Missing implementation");
	}

	public function CancelPayment($transactionId)
	{
		throw new Exception("Missing implementation");
	}
}

?>

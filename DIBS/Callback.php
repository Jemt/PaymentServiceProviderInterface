<?php

// This file is responsible for receiving the response from DIBS
// and forward it to the application in a standardized way.

require_once(dirname(__FILE__) . "/../PSPInterface.php"); // Callback is called directly by Payment Service Provider, so we need to include PSPInterface.php

if (isset($_POST["fullreply"]) === false) // Handle Continue URL
{
	PSP::RedirectToContinueUrl($_POST["CUSTOM_ContinueUrl"]);
}
else // Handle Server-To-Server Callback
{
	$config = PSP::GetConfig("DIBS");

	// Checksum (if keys are configured)

	$checksum = "";

	if (isset($config["Encryption Key 1"]) && $config["Encryption Key 1"] !== "" && isset($config["Encryption Key 2"]) && $config["Encryption Key 2"] !== "")
	{
		$k1 = $config["Encryption Key 1"];
		$k2 = $config["Encryption Key 2"];

		$checksum = md5($k2 . md5($k1 . "transact=" . $_POST["transact"] . "&amount=" . $_POST["amount"] . "&currency=" . $_POST["currency"]));

		if ($checksum !== $_POST["authkey"])
			throw new Exception("SecurityException: Integrity check failed - mismatching checksums");
	}

	// Invoke applicaton callback specified in RedirectToPaymentForm(..).
	// Using PSP::InvokeCallback(..) which implements security measures
	// to prevent man-in-the-middle attacks.

	if (PSP::GetDebugMail() !== "")
		mail(PSP::GetDebugMail(), "DIBS - raw callback data", "GET:\n" . print_r($_GET, true) . "\n\nPOST:\n" . print_r($_POST, true));

	PSP::InvokeCallback($_POST["CUSTOM_Callback"], $_POST["transact"] . ";" . $_POST["orderid"], $_POST["orderid"], (int)$_POST["amount"], $_POST["currency"]);
}

?>

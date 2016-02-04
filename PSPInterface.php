<?php

// ======================================================================================
// Payment Service Provider Interface
// ======================================================================================

/// <container name="PSPI">
/// 	Interface exposing payment functionality
/// </container>
interface PSPI
{
	/// <function container="PSPI" name="RedirectToPaymentForm" access="public">
	/// 	<description> Redirects user to payment form/window </description>
	/// 	<param name="orderId" type="string"> Unique ID identifying payment </param>
	/// 	<param name="amount" type="integer"> Order amount in smallest possible unit (e.g. Cents for USD) </param>
	/// 	<param name="currency" type="string"> Currency in the format defined by ISO 4217 (e.g. USD, GBP, or USD) </param>
	/// 	<param name="continueUrl" type="string"> URL to which user is redirected after completing payment - e.g. a receipt </param>
	/// 	<param name="callbackUrl" type="string">
	/// 		URL called asynchronously when payment is successfully carried through.
	/// 		Use PSP::GetCallbackData() to obtain OrderId, Amount, and Currency.
	/// 	</param>
	/// </function>
	public function RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl = null, $callbackUrl = null);

	/// <function container="PSPI" name="CapturePayment" access="public" returns="boolean">
	/// 	<description> Capture payment previously authorized using payment form - returns True on success, otherwise False </description>
	/// 	<param name="transactionId" type="string"> Unique ID identifying transaction </param>
	/// 	<param name="amount" type="integer"> Amount to withdraw in smallest possible unit (e.g. Cents for USD) </param>
	/// </function>
	public function CapturePayment($transactionId, $amount);

	/// <function container="PSPI" name="CancelPayment" access="public" returns="boolean">
	/// 	<description> Cancel payment previously authorized using payment form - returns True on success, otherwise False </description>
	/// 	<param name="transactionId" type="string"> Unique ID identifying transaction </param>
	/// </function>
	public function CancelPayment($transactionId);
}

// ======================================================================================
// Payment Service Provider Wrapper (PSPW) - wraps PSPM
// ======================================================================================

class PSPW implements PSPI
{
	private $pspm = null;

	public function __construct(PSPI $pspModule)
	{
		$this->pspm = $pspModule;
	}

	public function RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl = null, $callbackUrl = null)
	{
		if (is_string($orderId) === false || is_integer($amount) === false || is_string($currency) === false || ($continueUrl !== null && is_string($continueUrl) === false) || ($callbackUrl !== null && is_string($callbackUrl) === false))
			throw new Exception("Invalid argument(s) passed to RedirectToPaymentForm(string, integer, string[, string[, string]])");

		if (strpos($continueUrl, "?") !== false || strpos($callbackUrl, "?") !== false)
			throw new Exception("Invalid callback URL(s) passed - URL parameters are not allowed");

		$this->pspm->RedirectToPaymentForm($orderId, $amount, $currency, $continueUrl, $callbackUrl);
	}

	public function CapturePayment($transactionId, $amount)
	{
		if (is_string($transactionId) === false || is_int($amount) === false)
			throw new Exception("Invalid argument(s) passed to CapturePayment(string, integer)");

		return $this->pspm->CapturePayment($transactionId, $amount);
	}

	public function CancelPayment($transactionId)
	{
		if (is_string($transactionId) === false)
			throw new Exception("Invalid argument passed to CancelPayment(string)");

		return $this->pspm->CancelPayment($transactionId);
	}
}

// ======================================================================================
// PSP Helper class
// ======================================================================================

/// <container name="PSP">
/// 	Class exposing functionality useful to Payment Service Provider Modules
/// </container>
class PSP
{
	private static $baseConfig = null;
	private static $configuration = null;
	private static $currencies = null;
	private static $numCurrencies = null;

	// Factory

	/// <function container="PSP" name="GetPaymentProvider" access="public" static="true" returns="PSPI">
	/// 	<description> Get instance of Payment Service Provider Module which implements the PSPI interface </description>
	/// 	<param name="provider" type="string"> Name of Payment Service Provider Module </param>
	/// </function>
	public static function GetPaymentProvider($provider)
	{
		if (is_string($provider) === false)
			throw new Exception("Invalid argument passed to GetPaymentProvider(string)");

		$path = dirname(__FILE__) . "/" . $provider . "/PSPM.php";

		if (is_file($path) === false)
			throw new Exception("Unable to load PSPM from '" . $path . "' - not found");

		require_once($path);

		if (class_exists($provider, false) === false)
			throw new Exception("Unable to create instance of PSPM class '" . $provider . "' - not defined");

		return new PSPW(new $provider()); // Both PSPM and PSPW implements PSPI interface
	}

	// Communication

	/// <function container="PSP" name="Post" access="public" static="true" returns="string">
	/// 	<description> Post data to given URL </description>
	/// 	<param name="url" type="string"> Target URL </param>
	/// 	<param name="data" type="string[]"> Associative array contain data as key/value pairs </param>
	/// </function>
	public static function Post($url, $data)
	{
		if (is_string($url) === false)
			throw new Exception("Invalid argument(s) passed to Post(string, string[])");

		foreach ($data as $key => $value)
		{
			if (is_string($key) === false || is_string($value) === false)
				throw new Exception("Invalid argument(s) passed to Post(string, string[])");
		}

		$sc = stream_context_create(
			array(
				"http" => array(
					"method" => "POST",
					"header" => "Content-Type: application/x-www-form-urlencoded",
					"content" => http_build_query($data)
				)
			)
		);

		// Perform request

		$response = file_get_contents($url, false, $sc);

		if ($response === false)
			throw new Exception("Unable to perform request to URL '" . $url . "'");

		// Return response

		return $response; //return array("Headers" => $http_response_header, "Response" => $response);
	}

	/// <function container="PSP" name="RedirectToContinueUrl" access="public" static="true">
	/// 	<description> Redirect user to Continue URL passed to RedirectToPaymentForm(..) </description>
	/// 	<param name="url" type="string"> Continue URL </param>
	/// </function>
	public static function RedirectToContinueUrl($url)
	{
		if (is_string($url) === false)
			throw new Exception("Invalid argument passed to RedirectToContinueUrl(string)");

		if (strpos($url, "?") !== false) // Prevent PSPM from appending arguments
			throw new Exception("Invalid Continue URL passed - URL parameters are not allowed");

		header("location: " . $url);
		exit;
	}

	/// <function container="PSP" name="InvokeCallback" access="public" static="true" returns="string">
	/// 	<description> Invoke callback - used by PSPM to invoke callbacks passed to RedirectToPaymentForm(..) </description>
	/// 	<param name="callbackUrl" type="string"> Callback URL </param>
	/// 	<param name="transactionId" type="string">
	/// 		Transaction ID used for further processing (e.g. Capture/Cancel).
	/// 		Pass empty string if further processing is not supported.
	/// 	</param>
	/// 	<param name="orderId" type="string"> Order ID </param>
	/// 	<param name="amount" type="integer"> Order amount in smallest possible unit (e.g. Cents for USD) </param>
	/// 	<param name="currency" type="string"> Currency in the format defined by ISO 4217 (e.g. USD, GBP, or USD) </param>
	/// </function>
	public static function InvokeCallback($callbackUrl, $transactionId, $orderId, $amount, $currency)
	{
		if (is_string($callbackUrl) === false || is_string($transactionId) === false || is_string($orderId) === false || is_int($amount) === false || is_string($currency) === false)
			throw new Exception("Invalid argument(s) passed to InvokeCallback(string, string, string, integer, string)");

		if (is_numeric($currency) === true)
			$currency = self::NumericValueToCurrencyCode($currency); // Ensure consistency: Always pass currency name (e.g. USD) rather than numeric value (e.g. 840)

		$data = array();
		$data["TransactionId"] = $transactionId;
		$data["OrderId"] = $orderId;
		$data["Amount"] = (string)$amount;
		$data["Currency"] = $currency;
		$data["Checksum"] = md5(self::getEncryptionKey() . $transactionId . $orderId . $amount . $currency);

		return self::Post($callbackUrl, $data);
	}

	/// <function container="PSP" name="GetCallbackData" access="public" static="true" returns="object[]">
	/// 	<description>
	/// 		Securely obtain data sent to application callback specified in RedirectToPaymentForm(..).
	/// 		This function takes care of ensuring data integrity - an exception is thrown
	/// 		if data has been tampered with.
	/// 		Data is returned in an associative array containing the following keys:
	/// 		 - TransactionId (string value): Used to capture or cancel payment - empty string if not supported.
	/// 		 - OrderId (string value).
	/// 		 - Amount (integer value).
	/// 		 - Currency (string value): ISO 4217 (e.g. USD, GBP, or USD).
	/// 	</description>
	/// </function>
	public static function GetCallbackData()
	{
		$transactionId = $_POST["TransactionId"];
		$orderId = $_POST["OrderId"];
		$amount = (int)$_POST["Amount"];
		$currency = $_POST["Currency"];
		$checksum = $_POST["Checksum"];
		$newChecksum = md5(self::getEncryptionKey() . $transactionId . $orderId . $amount . $currency);

		if ($newChecksum !== $checksum)
			throw new Exception("SecurityException: Integrity check failed - mismatching checksums");

		return array("TransactionId" => $transactionId, "OrderId" => $orderId, "Amount" => (int)$amount, "Currency" => $currency);
	}

	// Configuration

	/// <function container="PSP" name="GetConfig" access="public" static="true" returns="string[]">
	/// 	<description>
	/// 		Used by PSPM to obtain associative configuration array
	/// 		defined in Config.php with key/value pairs </description>
	/// 	<param name="provider" type="string"> Name of PSPM </param>
	/// </function>
	public static function GetConfig($provider)
	{
		if (is_string($provider) === false)
			throw new Exception("Invalid argument passed to GetConfig(string)");

		self::ensureProviderConfig($provider);
		return self::$configuration;
	}

	/// <function container="PSP" name="GetProviderPath" access="public" static="true" returns="string">
	/// 	<description> Returns path to folder containing PSPM </description>
	/// 	<param name="provider" type="string"> Name of PSPM </param>
	/// </function>
	public static function GetProviderPath($provider)
	{
		if (is_string($provider) === false)
			throw new Exception("Invalid argument passed to GetProviderPath(string)");

		self::ensureBaseConfig();
		return self::$baseConfig["BasePath"] . "/" . $provider;
	}

	/// <function container="PSP" name="GetProviderUrl" access="public" static="true" returns="string">
	/// 	<description> Returns external URL to folder containing PSPM </description>
	/// 	<param name="provider" type="string"> Name of PSPM </param>
	/// </function>
	public static function GetProviderUrl($provider)
	{
		if (is_string($provider) === false)
			throw new Exception("Invalid argument passed to GetProviderUrl(string)");

		self::ensureBaseConfig();
		return self::$baseConfig["BaseUrl"] . "/" . $provider;
	}

	/// <function container="PSP" name="GetDebugMail" access="public" static="true" returns="string">
	/// 	<description>
	/// 		Returns an e-mail address that can be used to send debugging information.
	/// 		An empty string indicates that debugging is not enabled.
	/// 	</description>
	/// </function>
	public static function GetDebugMail()
	{
		self::ensureBaseConfig();
		return self::$baseConfig["DebugMail"];
	}

	// Conversion

	/// <function container="PSP" name="CurrencyCodeToNumericValue" access="public" static="true" returns="string">
	/// 	<description>
	/// 		Converts a currency code (e.g. USD) to its numeric equivalent (e.g. 840).
	/// 		Value is returned as string to preserve any leading zeros.
	/// 	</description>
	/// 	<param name="currencyCode" type="string"> Alphabetical currency code as defined by ISO 4217 </param>
	/// </function>
	public static function CurrencyCodeToNumericValue($currencyCode)
	{
		if (is_string($currencyCode) === false)
			throw new Exception("Invalid argument passed to CurrencyCodeToNumericValue(string)");

		self::ensureCurrencies();

		if (isset(self::$currencies[$currencyCode]) === false)
			throw new Exception("No numeric equivalent to '" . $currencyCode . "' found - pass a valid value such as USD, EUR, GBP, etc.");

		return self::$currencies[$currencyCode]; // Numeric values are stored as strings to preserve any leading zeros (e.g. ALL = 008)
	}

	/// <function container="PSP" name="NumericValueToCurrencyCode" access="public" static="true" returns="string">
	/// 	<description>
	/// 		Converts a numeric concurrency representation (e.g. 840) to its alphabetical equivalent (e.g. USD)
	/// 	</description>
	/// 	<param name="numericCurrencyValue" type="string"> Numeric currency representation as defined by ISO 4217 </param>
	/// </function>
	public static function NumericValueToCurrencyCode($numericCurrencyValue)
	{
		if (is_string($numericCurrencyValue) === false)
			throw new Exception("Invalid argument passed to NumericValueToCurrencyCode(string)");

		self::ensureCurrencies();

		if (isset(self::$numCurrencies[$numericCurrencyValue]) === false)
			throw new Exception("No currency name equivalent to '" . $numericCurrencyValue . "' found - pass a valid numeric currency value such as 840 for USD, 978 for EUR, 826 for GBP, etc.");

		return self::$numCurrencies[$numericCurrencyValue]; // Numeric values are stored as strings to preserve any leading zeros (e.g. ALL = 008)
	}

	// Private

	private static function ensureBaseConfig()
	{
		if (self::$baseConfig === null)
		{
			require_once(dirname(__FILE__) . "/Config.php");
			self::$baseConfig = $config;
		}
	}

	private static function ensureProviderConfig($provider)
	{
		if (is_string($provider) === false)
			throw new Exception("Invalid argument passed to ensureProviderConfig(string)");

		if (self::$configuration === null)
		{
			require_once(dirname(__FILE__) . "/" . $provider . "/Config.php");
			self::$configuration = $config;
		}
	}

	private static function ensureCurrencies()
	{
		if (self::$currencies === null)
		{
			require_once(dirname(__FILE__) . "/Currencies.php");

			self::$currencies = $currencies;

			self::$numCurrencies = array();
			foreach ($currencies as $key => $value)
				self::$numCurrencies[$value] = $key;
		}
	}

	private static function getEncryptionKey()
	{
		self::ensureBaseConfig();
		return self::$baseConfig["EncryptionKey"];
	}
}

// ======================================================================================
// Error handling - only in effect if DebugMail has been configured
// ======================================================================================

function PSPErrorHandler($errNo, $errMsg, $errFile, $errLine)
{
	mail(PSP::GetDebugMail(), "PSP - payment error occured", "Error ID: " . $errNo . "\nError message: " . $errMsg . "\nFile: " . $errFile . "\nLine: " . $errLine);
	return false; // Return control to PHP's error handler
}

function PSPExceptionHandler(Exception $ex)
{
	$errNo = $ex->getCode();
	$errMsg = $ex->getMessage();
	$errFile = $ex->getFile();
	$errLine = $ex->getLine();

	mail(PSP::GetDebugMail(), "PSP - payment error occured", "Error ID: " . $errNo . "\nError message: " . $errMsg . "\nFile: " . $errFile . "\nLine: " . $errLine);

	header("HTTP/1.1 500 Internal Server Error");
	header("Content-Type: text/html; charset=ISO-8859-1");

	echo "<b>An unhandled error occured</b><br><br>";
	echo $ex->getMessage();
	echo "<br><br><b>Stack trace</b><br><pre>";
	echo $ex->getTraceAsString();
	echo "</pre>";
}

if (PSP::GetDebugMail() !== "")
{
	error_reporting(E_ALL | E_STRICT);
	ini_set("display_errors", 1);

	set_error_handler("PSPErrorHandler");
	set_exception_handler("PSPExceptionHandler");
}

?>

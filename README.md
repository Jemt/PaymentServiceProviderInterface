# Standardized interface for carrying out online payments

The goal with this project is to provide a **common** and **standardized** interface
for carrying out online payments, no matter what payment provider has been chosen.

All Payment Service Providers (PSPs) have defined their own interface,
making it too difficult and time consuming to add support for multiple
payment providers in applications and web shops.

Currently the interface has been defined in PHP, but the standard *must* be
language independent: https://github.com/Jemt/StandardizedPaymentProviderInterface/blob/master/PSPInterface.php

**Code snippet**

The code below is an example of how the interface currently works (not committed just yet).

```
require_once("PSPInterface.php");

$orderId = "10381";
$amount = 25; // 25 cents
$currency = "USD";

$p = PSP::GetPaymentProvider("DIBS");
$p->RedirectToPaymentForm($orderId, $amount, $currency, "http://test.com/Thanks.html", "http://test.com/Callback.php");
```

When the payment has been carried through, the user is taken back to *Thanks.html*.

Callback.php is invoked behind the scene (server-to-server communication) and can be used to update the order status when the payment is successful. Callback.php receives the Order ID, the Amount, and Currency. Data integrety is ensured using checksums and encryption keys - all automatically.

**Callback.php example**

```
require_once("PSPInterface.php");

$data = PSP::GetCallbackData(); // Data is secure (data integrity check performed)

$orderId = $data["OrderId"];
$amount = $data["Amount"];
$currency = $data["Currency"];

mail("sales@test.com", "New paying customer", "OrderId: " . $orderId . "\nAmount: " . $amount . "\nCurrency: " . $currency);
```

Any web application can integrate payment support in a couple of minutes - all it takes is the code above.

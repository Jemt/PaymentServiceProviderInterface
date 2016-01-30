# Standardized interface for carrying out online payments

The goal with this project is to provide a **common** and **standardized** interface
for carrying out online payments, no matter what payment provider has been chosen.

All Payment Service Providers (PSPs) have defined their own interface,
making it too difficult and time consuming to add support for multiple
payment providers in applications and web shops.

Currently the interface has been defined in PHP, but the standard *must* be
language independent: https://github.com/Jemt/StandardizedPaymentProviderInterface/blob/master/PSPInterface.php

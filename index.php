<?php
require_once './vendor/autoload.php';
require './bootstrap.php';

$invoices = InvoiceController::getInvoices();
foreach($invoices->results as $invoice) {
  UserController::updateUsers($invoice);
}

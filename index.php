<?php
require_once './vendor/autoload.php';
require './bootstrap.php';

$invoices = InvoiceController::getInvoices();
$res = [];
foreach($invoices->results as $invoice) {
  $result = UserController::updateUsers($invoice);
  if($result) {
    array_push($res, $result);
  }
}
echo json_encode($res);

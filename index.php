<?php
// get current dir
$current_dir = dirname(__FILE__);
require_once $current_dir.'/vendor/autoload.php';
require $current_dir.'/bootstrap.php';

$invoices = InvoiceController::getInvoices();
$res = [];
foreach($invoices->results as $invoice) {
  $result = UserController::updateUsers($invoice);
  if($result) {
    array_push($res, $result);
  }
}
echo json_encode($res);

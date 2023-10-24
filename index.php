<?php
// get current dir
$current_dir = dirname(__FILE__);
require_once $current_dir.'/vendor/autoload.php';
require_once $current_dir.'/bootstrap.php';


$opts = getopt('', array('date::'));
$parseDate = isset($opts['date']) ? $opts['date'] : null;

$todayDayNumber = Carbon\Carbon::now()->format('d');
// check if today is greater than 10th of the month
if($todayDayNumber < 10 && !$parseDate) {
  $parseDate = Carbon\Carbon::now()->startOfMonth()->toDateString();
}
function recrusiveOrders($parseDate, $page = 1) {
  $records = InvoiceController::getInvoices($parseDate, $page);
  if(isset($records->_links->next)) {
    $page++;
    $records->results = array_merge($records->results, recrusiveOrders($parseDate, $page)->results);
  }
  return $records;
}

$invoices = recrusiveOrders($parseDate);


$res = [];
foreach($invoices->results as $invoice) {
  $result = UserController::updateUsers($invoice);
  if($result) {
    array_push($res, $result);
  }
}
echo json_encode($res);

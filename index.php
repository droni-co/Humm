<?php
// get current dir
$current_dir = dirname(__FILE__);
require_once $current_dir.'/vendor/autoload.php';
require_once $current_dir.'/bootstrap.php';


$opts = getopt('d:', array('d::'));
$parseDate = isset($opts['d']) ? $opts['d'] : null;
$toDate = null;

$todayDayNumber = Carbon\Carbon::now()->format('d');
// check if today is greater than 10th of the month
if($todayDayNumber < 15 && !$parseDate) {
  $parseDate = Carbon\Carbon::now()->startOfMonth()->toDateString();
  $toDate = Carbon\Carbon::now()->addDays(1)->toDateString();
}
function recrusiveOrders($parseDate, $page = 1, $toDate = null) {
  $records = InvoiceController::getInvoices($parseDate, $page, $toDate);
  if(isset($records->_links->next)) {
    $page++;
    $records->results = array_merge($records->results, recrusiveOrders($parseDate, $page, $toDate)->results);
  }
  return $records;
}

$invoices = recrusiveOrders($parseDate);

$res = [
  'info' => [
    'todayDayNumber' => $todayDayNumber,
    'parseDate' => $parseDate,
    'toDate' => $toDate,
    'opts' => $opts,
    'invoices' => [
      'siigo' => count($invoices->results),
      //'cuenti' => count($invoicesCuenti),
    ],
  ],
  'data' => []
];
foreach($invoices->results as $invoice) {
  $result = UserController::updateUsers($invoice);
  if($result) {
    array_push($res['data'], $result);
  }
}
echo json_encode($res);

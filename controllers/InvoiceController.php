<?php
use Carbon\Carbon;
class InvoiceController {
  public static function getInvoices($forDate=null, $page=1, $toDate=null) {
    $token = InvoiceController::getToken();
    $today = $forDate ? $forDate : Carbon::now()->addDays(-1)->format('Y-m-d');
    $toDate = $toDate ? $toDate : Carbon::now()->addDays(1)->toDateString();
    $page_zise = 100;
    try {
      $client = new GuzzleHttp\Client(['base_uri' => $_ENV['SIIGO_API']]);
      $url = '/v1/invoices?created_start='.$today.'&created_end='.$toDate.'&page_size='.$page_zise.'&page='.$page;
      $requestInvoices = $client->request('GET', $url, [
        'headers' => [
          'Authorization' => 'Bearer ' . $token->access_token,
          'Partner-Id' => 'dro'
        ]
      ]);
      return json_decode($requestInvoices->getBody()->getContents());
    }
    catch (Exception $e) {
      return $e;
    }
  }
  public static function getToken() {
    $current_dir = dirname(__FILE__);
    $nombre_fichero = $current_dir."/../token.json";
    $gestor = fopen($nombre_fichero, "r");
    $saveToken = "[]";
    if(filesize($nombre_fichero) > 0) {
      $saveToken = fread($gestor, filesize($nombre_fichero));
    }
    fclose($gestor);
    $savedToken = json_decode($saveToken);
    if(isset($savedToken->access_token) && $savedToken->created_at > Carbon::now()->subSeconds(86400)->timestamp) {
      $finalToken = $savedToken;
    } else {
      $finalToken = InvoiceController::generateToken();
      $finalToken->created_at = Carbon::now()->timestamp;
      $gestor = fopen($nombre_fichero, "w+");
      fwrite($gestor, json_encode($finalToken));
      fclose($gestor);
    }
    return $finalToken;
  }
  public static function generateToken() {
    $client = new GuzzleHttp\Client(['base_uri' => $_ENV['SIIGO_API']]);
      // Send a request to https://foo.com/api/test
      $requestToken = $client->request('POST', '/auth', [
        'json' => [
          'username' => $_ENV['SIIGO_USER'],
          'access_key' => $_ENV['SIIGO_API_KEY']
        ]
      ]);
    return json_decode($requestToken->getBody()->getContents());
  }

  public static function logData($data) {
    $current_dir = dirname(__FILE__);
    $nombre_fichero = $current_dir."/fetchLog.log";
    $gestor = fopen($nombre_fichero, "a+");
      fwrite($gestor, date('y-m-d H:i'). "\n".$data."\n ------------------------------------ \n");
      fclose($gestor);
  }
}

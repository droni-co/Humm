<?php
use GuzzleHttp\Client;
use Carbon\Carbon;
class InvoiceController {
  public static function getInvoices() {
    $token = InvoiceController::getToken();
    $today =  Carbon::now()->toDateString();
    $page_zise = 100;
    try {
      $client = new GuzzleHttp\Client(['base_uri' => $_ENV['SIIGO_API']]);
      $url = '/v1/invoices?created_start='.$today.'&page_size='.$page_zise;
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
    $nombre_fichero = $current_dir."/token.json";
    $gestor = fopen($nombre_fichero, "r");
    $saveToken = "[]";
    if(filesize($nombre_fichero) > 0) {
      $saveToken = fread($gestor, filesize($nombre_fichero));
    }
    fclose($gestor);
    $savedToken = json_decode($saveToken);
    if($savedToken->access_token && $savedToken->created_at > Carbon::now()->subSeconds(86400)->timestamp) {
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
}

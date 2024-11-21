<?php
use Carbon\Carbon;
class UserController {
  public static function updateUsers($invoice) {
    $res = [];
    if(Siigolog::where('siigo_id', $invoice->id)->first()) {
      array_push($res, [
        'status' => 'error',
        'message' => 'Invoice already exists',
        'id' => $invoice->id
      ]);
      return $res;
    }
    $client = UserController::getClientById($invoice->customer->id);
    $user = User::where('email', ($client->contacts[0]->email ?? 'noEmail'))->first();
    $finalItem = UserController::getFinalItem($invoice->items);
    if(
      $user &&
      $finalItem &&
      intval($invoice->balance) == 0 &&
      $user->subscription_expiration != '0000-00-00 00:00:00'
    ) {
      $siigoLog = new Siigolog;
      $siigoLog->user_id = $user->id;
      $siigoLog->siigo_id = $invoice->id;
      $siigoLog->subscription_expiration_old = $user->subscription_expiration;
      $siigoLog->subscription_expiration = Carbon::parse($user->subscription_expiration)
        ->addMonths($finalItem->quantity)->toDateTimeString();
      $siigoLog->code = $finalItem->code;
      $siigoLog->quantity = $finalItem->quantity;
      $siigoLog->seller = $invoice->seller;

      //save user
      $user->subscription_expiration = $siigoLog->subscription_expiration;
      if($_ENV['DEPLOY'] == 'prod') {
        $user->save();
      }
      $siigoLog->save();

      array_push($res, [
        'status' => 'success',
        'message' => 'User updated',
        'id' => $invoice->id
      ]);
    }
    return $res;
  }
  public static function updateUsersCuenti($invoice) {
    $res = [];
    if(Siigolog::where('cuenti_id', $invoice->id_transacion)->first()) {
      array_push($res, [
        'status' => 'error',
        'message' => 'Invoice Cuenti already exists',
        'id' => $invoice->id_transacion
      ]);
      return $res;
    }
    $clients = UserController::getClientsCuenti();

    $finClient = null;
    foreach( $clients as $client) {
      if($client->identificacion == $invoice->identificacion) {
        $finClient = $client;
        break;
      }
    }

    $user = User::where('email', ($finClient->email1 ?? 'noEmail'))->first();

    if($user && $invoice->vendedor == 'Monitoreo CYJ') {
      $siigoLog = new Siigolog;
      $siigoLog->user_id = $user->id;
      $siigoLog->cuenti_id = $invoice->id_transacion;
      $siigoLog->subscription_expiration_old = $user->subscription_expiration;
      $siigoLog->subscription_expiration = Carbon::parse($user->subscription_expiration)
        ->addMonths(1)->toDateTimeString();
      $siigoLog->code = '';
      $siigoLog->quantity = 0;
      $siigoLog->seller_name = $invoice->vendedor;

      //save user
      $user->subscription_expiration = $siigoLog->subscription_expiration;
      if($_ENV['DEPLOY'] == 'prod') {
        $user->save();
      }
      $siigoLog->save();

      array_push($res, [
        'status' => 'success',
        'message' => 'User updated',
        'id' => $invoice->id_transacion
      ]);
    }
    
    return $res;

  }
  public static function getClientById($customerId) {
    $token = InvoiceController::getToken();
    try {
      $client = new GuzzleHttp\Client(['base_uri' => $_ENV['SIIGO_API']]);
      $requestInvoices = $client->request('GET', '/v1/customers/'.$customerId, [
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
  public static function getFinalItem($items) {
    $servicios = ['PLTS02', 'MONI03', 'MONI02', 'PLTS01', 'MONI01'];
    $finalItem = null;
    foreach($items as $item) {
      if(in_array($item->code, $servicios) && $item->quantity < ($finalItem->quantity ?? 1000)) {
        $finalItem = $item;
      }
    }
    return $finalItem;
  }
  public static function getClientsCuenti() {
    $token = InvoiceController::getTokenCuenti();
    $apiUrl = 'https://api.cuenti.co';
    $endpoint = '/jServerj4ErpPro/com/j4ErpPro/server/adm/cliente/consultarCliente/1';
    $client = new GuzzleHttp\Client([
      'base_uri' => $apiUrl,
      'headers' => [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        'X-Auth-Token' => $token->map1->token,
        'X-Auth-Token-empresa' => '14045',
        'X-Auth-Token-id-usuario' => '21963',
        'X-gtm' => 'GMT-0500'
      ]
    ]);
    // Send a request to https://foo.com/api/test
    $users = $client->request('GET', $endpoint);
    return json_decode($users->getBody()->getContents());
  }
}

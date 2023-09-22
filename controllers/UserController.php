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
    $user = User::where('email', $client->contacts[0]->email)->first();
    $finalItem = UserController::getFinalItem($invoice->items);
    if(
      $user &&
      $finalItem &&
      intval($invoice->balance) == 0
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
}

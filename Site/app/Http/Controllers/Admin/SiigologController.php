<?php namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Tobuli\Entities\Siigolog;
use Tobuli\Entities\User;
use Carbon\Carbon;

class SiigologController extends BaseController {
  public function index(Request $request) {
    $from_date = $request->from_date ?? Carbon::now()->subDays(7)->toDateString();
    $to_date = $request->to_date ?? Carbon::now()->addDays(1)->toDateString();
    $siigologs = Siigolog::orderBy('created_at', 'desc');

    if ($request->siigo_id) {
      $siigologs = $siigologs->where('siigo_id', $request->siigo_id);
    } else {
      $siigologs = $siigologs->whereBetween('created_at', [$from_date, $to_date]);
    }

    $siigologs = $siigologs->paginate(50);

    return view('siigologs.index', compact('siigologs', 'from_date', 'to_date'));
  }
  public function store(Request $request) {
    // $current_dir = dirname(__FILE__);
    // $nombre_fichero = $current_dir."/cuenti.log";
    // $gestor = fopen($nombre_fichero, "a+");
    // fwrite($gestor, date('y-m-d H:i'). "\n ------------------------------------ \n");
    // fwrite($gestor, json_encode($request->all()));
    // fclose($gestor);

    $siigolog = new Siigolog();

    

    $data = $request->all()[0];
    $cliente = $data[array_search('cliente', array_column($data, 'consulta'))]['resultado'][0];
    $encabezados = $data[array_search('Encabezados', array_column($data, 'consulta'))]['resultado'][0];
    $detalleEncabezado = $data[array_search('DetalleEncabezado', array_column($data, 'consulta'))]['resultado'][0];

    $user = User::where('email', ($cliente['email1'] ?? 'noEmail'))->first();

    $res = [];

    if(
      $encabezados['vendedor'] == 'Monitoreo CYJ' &&
      $user &&
      $detalleEncabezado['cantidad'] > 0 &&
      is_int($detalleEncabezado['cantidad'])
      ) {

      $siigoLog = new Siigolog;
      $siigoLog->user_id = $user->id;
      $siigoLog->cuenti_id = $encabezados['id_transacion'];
      $siigoLog->subscription_expiration_old = $user->subscription_expiration;
      $siigoLog->subscription_expiration = Carbon::parse($user->subscription_expiration)
        ->addMonths($detalleEncabezado['cantidad'])->toDateTimeString();
      $siigoLog->code = 'hook';
      $siigoLog->quantity = $detalleEncabezado['cantidad'];
      $siigoLog->seller_name = $encabezados['vendedor'];

      //save user
      $user->subscription_expiration = $siigoLog->subscription_expiration;
      $user->save();
      $siigoLog->save();

      $res = [
        'status' => 'success',
        'message' => 'User updated',
        'id' => $encabezados['id_transacion']
      ];
    } else {
      $res = [
        'status' => 'error',
        'message' => 'there was a problem saving data',
        'id' => $encabezados['id_transacion']
      ];
    }
    // Log
    $current_dir = dirname(__FILE__);
    $nombre_fichero = $current_dir."/cuenti.log";
    $gestor = fopen($nombre_fichero, "c");
    fwrite($gestor, date('y-m-d H:i'). "\n -------------- ERROR ---------------------- \n");
    fwrite($gestor, json_encode($request->all()));
    fclose($gestor);
    
    

    return response()->json($res);
  }
}

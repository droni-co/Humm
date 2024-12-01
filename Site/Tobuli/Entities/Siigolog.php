<?php namespace Tobuli\Entities;

use Eloquent;
use Illuminate\Support\Facades\DB;

class Siigolog extends Eloquent {
	protected $table = 'siigologs';

  public function user() {
    return $this->belongsTo('Tobuli\Entities\User', 'user_id');
  }
}

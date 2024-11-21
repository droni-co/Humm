<?php
use Illuminate\Database\Eloquent\Model as Eloquent;
class Siigolog extends Eloquent
{
  /**
  * The attributes that should be hidden for arrays.
  *
  * @var array
  */
  protected $hidden = [
    'password', 'remember_token',
  ];
}

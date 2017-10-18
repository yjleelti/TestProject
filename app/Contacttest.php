<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Contacttest extends Model implements AuthenticatableContract {

    use Authenticatable;
    protected $table = 'Contacttest';
    protected $primaryKey  = 'contacttestid';
    public $timestamps = false;
}
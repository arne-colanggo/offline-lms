<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{
    protected $table = 'profiles';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'firstname',
        'middlename',
        'lastname',
        'gender',
        'dateofbirth',
        'addressid',
        'religion',
        'contact',
        'fbaccount',
        'email',
        'picture',
        'deleted'
    ];


}

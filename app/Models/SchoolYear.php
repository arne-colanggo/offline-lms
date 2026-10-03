<?php

namespace App\Models;

use CodeIgniter\Model;

class SchoolYear extends Model
{
    protected $table = 'schoolyears';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
    ];


}

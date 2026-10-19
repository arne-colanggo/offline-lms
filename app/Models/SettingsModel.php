<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = [
        'schoolname',
        'schooladdress',
        'region',
        'division',
        'district',
        'schoolid',
        'phone',
        'schoolhead',
        'schoolheaddesignation',
        'logo',
        'favicon',
        'schoolyear',
        'email'
    ];

}

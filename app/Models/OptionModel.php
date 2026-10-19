<?php

namespace App\Models;

use CodeIgniter\Model;

class OptionModel extends Model
{
    protected $table         = 'options';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['questionid', 'description', 'match_text', 'isanswer'];
    protected $useTimestamps = false;
}

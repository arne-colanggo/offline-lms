<?php

namespace App\Models;

use CodeIgniter\Model;

class RubricDescriptorModel extends Model
{
    protected $table         = 'rubric_descriptors';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['criterion_id','level_id','description'];
}

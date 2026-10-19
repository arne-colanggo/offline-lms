<?php

namespace App\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    protected $table         = 'questions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['bowdetailsid', 'option_type', 'question_type', 'description', 'createdby'];
    protected $useTimestamps = false;
}

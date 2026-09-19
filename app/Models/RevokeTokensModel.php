<?php

namespace App\Models;

use CodeIgniter\Model;

class RevokeTokensModel extends Model
{
    protected $table = 'revoked_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'jti',
        'user_id',
        'expires_at',
        'created_at'
    ];
}

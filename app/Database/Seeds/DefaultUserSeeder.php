<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    public function run()
    {
        //
        $data = [
            'username' => 'admin',
            'password' => password_hash('1245', PASSWORD_BCRYPT),
            'email' => 'admin@localhost',
            'role' => 'admin',
            'status' => 1
        ];

        $this->db->query('INSERT INTO users(username,`password`,email,`role`,`status`) VALUES(:username:, :password:, :email:, :role:, :status:)', $data);
    }

}

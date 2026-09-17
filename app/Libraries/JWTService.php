<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class JWTService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $expiration;

    public function __construct()
    {
        $secretKey = env('JWT_SECRET_KEY');

        if (empty($secretKey)) {
            throw new RuntimeException(
                'JWT_SECRET_KEY is not configured in .env'
            );
        }

        $this->secretKey = $secretKey;

        $this->expiration = (int) env(
            'JWT_EXPIRATION',
            3600
        );
    }

    public function generate(array $user): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->expiration;

        $payload = [
            'iss' => base_url(),
            'iat' => $issuedAt,
            'exp' => $expire,

            'sub' => (string) $user['id'],

            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];

        return JWT::encode(
            $payload,
            $this->secretKey,
            $this->algorithm
        );
    }

    public function validate(string $token): object
    {
        return JWT::decode(
            $token,
            new Key(
                $this->secretKey,
                $this->algorithm
            )
        );
    }
}
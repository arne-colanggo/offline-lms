<?php

namespace App\Filters;

use App\Libraries\JwtService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Throwable;

class JWTAuthFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        $header = $request->getHeaderLine('Authorization');

        if (!$header) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => false,
                    'message' => 'Authorization token is required.'
                ]);
        }

        if (!preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status' => false,
                    'message' => 'Invalid authorization header.'
                ]);
        }

        $token = $matches[1];

        try {

            $jwtService = new JwtService();

            $decoded = $jwtService->validate($token);

            // Store authenticated user information
            $request->user = $decoded->user;
            //$request->setAttribute('user', $decoded->user);

        } catch (Throwable $e) {

            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid or expired token'
                ]);
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
    }
}
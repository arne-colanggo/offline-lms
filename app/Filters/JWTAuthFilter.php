<?php

namespace App\Filters;

use App\Libraries\JwtService;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\RevokeTokensModel;
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
        //$token = session()->get('jwt_token');
        try {

            $jwtService = new JwtService();

            $decoded = $jwtService->validate($token);

            // Make sure jti exists
            if (!isset($decoded->jti)) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'JWT does not contain a jti.'
                    ]);
            }

            /*
             * Check if JWT has been revoked
             */
            $revokedTokenModel = new RevokeTokensModel();

            $revoked = $revokedTokenModel
                ->where('jti', $decoded->jti)
                ->first();

            if ($revoked) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Token has been revoked.'
                    ]);
            }


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
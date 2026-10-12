<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            // Check if it's an API request
            if ($request->is('json') || strpos($request->getPath(), 'api/') === 0) {
                return service('response')
                    ->setJSON(['error' => 'Unauthorized'])
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            }
            return redirect()->to(base_url('login'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}

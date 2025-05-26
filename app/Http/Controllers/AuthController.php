<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Controllers\Controller;

use App\Http\Requests\AuthRequest\LoginRequest;
use App\Http\Requests\AuthRequest\resetPassword;

class AuthController extends Controller
{
    /**
     * The authentication service instance.
     *
     * @var AuthService
     */
    protected $authService;

    /**
     * Create a new AuthController instance.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login an existing user.
     *
     * @param LoginRequest $request The request containing user login credentials.
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        // Validate the request data
        $credentials = $request->validated();

        // Call the AuthService to login the user
        $result = $this->authService->login($credentials);

        // Return a success or error response based on the result
        return $result['status'] === 201
            ? self::success($result['data'], $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }


    public function resetPassword(resetPassword $request)
    {// Validate the request data
        $data = $request->validated();

        // Call the AuthService to logout the user
        $result = $this->authService->resetPassword($data);

        // Return a success or error response based on the result
        return $result['status'] === 200
            ? self::success(null, $result['message'], $result['status'])
            : self::error(null, $result['message'], $result['status']);
    }




}

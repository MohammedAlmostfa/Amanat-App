<?php

namespace App\Services;

use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
    /**
     * Handles user login.
     *
     * This method is responsible for authenticating the user by verifying
     * the provided credentials (email and password). If successful, it returns
     * the user data. Otherwise, it responds with appropriate error messages.
     *
     * @param array $credentials User credentials (email and password).
     * @return array Response including the status, message, and token data.
     */
    public function login($credentials)
    {
        try {
            // Fetch the single user's password from the database
            $user = User::first();

            // Check if the provided password matches the hashed password
            if (!Hash::check($credentials['password'], $user->password)) {
                return [
                    'status' => 401, // Unauthorized
                    'message' => ['فشل في تسجيل الدخول. كلمة المرور غير صحيحة.'], // Arabic message for invalid login
                ];
            }

            // If authentication succeeds, return success message
            return [
                'message' => 'Login successful.', // Success message
                'status' => 201, // HTTP status code for created
                'data' => [
                    'user_id' => $user->id, // ID of the authenticated user
                    'name' => $user->name, // Name of the authenticated user
                ],
            ];
        } catch (Exception $e) {
            // Log the error details for debugging purposes
            Log::error('Error during login: ' . $e->getMessage());

            // Return error response for unexpected issues
            return [
                'status' => 500, // Internal Server Error
                'message' => ['حدث خطأ أثناء معالجة الطلب.'], // Arabic message for server error
            ];
        }
    }

    /**
     * Handles password reset functionality.
     *
     * This method verifies the old password, and if valid, updates it with the
     * new password after hashing. If any step fails, appropriate error messages are returned.
     *
     * @param array $data Contains old password, new password, and user ID.
     * @return array Response including the status, message, and token data.
     */
    public function resetPassword($data)
    {
        try {
            // Fetch the user
            $user = User::first(); // Assuming there's only one user in the database

            if (!$user) {
                return [
                    'status' => 404, // User not found
                    'message' => ['المستخدم غير موجود.'], // Arabic message for user not found
                ];
            }

            // Check if the provided old password matches the hashed password
            if (!Hash::check($data['old_password'], $user->password)) {
                return [
                    'status' => 401, // Unauthorized
                    'message' => ['كلمة المرور القديمة غير صحيحة.'], // Arabic message for invalid old password
                ];
            }

            // Update the password to the new one
            $user->password=bcrypt($data['new_password']); // Hash the new password
            $user->save();

            // Return success response
            return [
                'status' => 200, // Success
                'message' => ['تم تغيير كلمة المرور بنجاح.'], // Arabic message for successful password reset
            ];
        } catch (Exception $e) {
            // Log any error encountered for debugging purposes
            Log::error('Error resetting password: ' . $e->getMessage());

            // Return error response for unexpected issues
            return [
                'status' => 500, // Internal Server Error
                'message' => ['حدث خطأ أثناء معالجة الطلب.'], // Arabic message for server error
            ];
        }
    }
}

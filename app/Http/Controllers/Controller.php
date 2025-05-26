<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller
 * Provides shared methods for generating consistent JSON responses across the application.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return a successful JSON response with a single data object.
     *
     * This is useful for individual records or single entities.
     *
     * @param mixed $data The data to be returned in the response.
     * @param string $message The success message.
     * @param int $status The HTTP status code (default: 200).
     * @return \Illuminate\Http\JsonResponse JSON response containing the success status, message, and data.
     */
    public static function successshow($data = null, $message = '', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Return a successful JSON response.
     *
     * This method is intended for lists of data or arrays of items.
     *
     * @param mixed $data The data to be returned in the response.
     * @param string $message The success message.
     * @param int $status The HTTP status code (default: 200).
     * @return \Illuminate\Http\JsonResponse JSON response containing the success status, message, and data.
     */
    public static function success($data = [], $message = '', $status = 200)
    {
        // Ensure the data is structured as a list (array)
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => is_array($data) ? array_values($data) : [$data], // Ensures it's returned as a list
        ], $status);
    }

    /**
     * Return an error JSON response.
     *
     * This method is used when an error occurs during processing.
     *
     * @param mixed $data The data to be returned in the response, if any.
     * @param string $message The error message to describe the problem.
     * @param int $status The HTTP status code (default: 400).
     * @return \Illuminate\Http\JsonResponse JSON response containing the error status, message, and data.
     */
    public static function error($data = [], $message = '', $status = 400)
    {
        // Ensure the data is structured as a list (array)
        return response()->json([
            'status' => 'error',
            'errors' => $message,
            'data' => is_array($data) ? array_values($data) : [$data], // Ensures it's returned as a list
        ], $status);
    }

    /**
     * Generates a JSON response with paginated data.
     *
     * This method is helpful for APIs returning lists of items with pagination.
     * It transforms the paginated items using a provided resource class for consistent formatting.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator The paginator instance containing the items.
     * @param string $resourceClass The resource class used to transform the paginated items.
     * @param string $message An optional message to be included in the response.
     * @param int $status The HTTP status code.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing the paginated data and metadata.
     */
    public static function paginated(LengthAwarePaginator $paginator, $resourceClass, $message = '', $status = 200)
    {
        // Transform paginated items using the given resource class
        $transformedItems = $resourceClass::collection($paginator->items());

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $transformedItems,
            'pagination' => [
                'total' => $paginator->total(), // Total number of records
                'count' => $paginator->count(), // Number of records on the current page
                'per_page' => $paginator->perPage(), // Records per page
                'current_page' => $paginator->currentPage(), // Current page number
                'total_pages' => $paginator->lastPage(), // Total number of pages
            ],
        ], $status);
    }
}

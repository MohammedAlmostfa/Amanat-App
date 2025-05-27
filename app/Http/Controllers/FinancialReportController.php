<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FinancialReportService;
use App\Http\Requests\FinancialReportFilteringData;

/**
 * Class FinancialReportController
 *
 * Handles financial report operations and interactions with the FinancialReportService.
 */
class FinancialReportController extends Controller
{
    /**
     * Service instance used for managing financial reports.
     *
     * @var FinancialReportService
     */
    protected FinancialReportService $FinancialReportService;

    /**
     * Constructor to inject the FinancialReportService dependency.
     *
     * @param FinancialReportService $FinancialReportService
     */
    public function __construct(FinancialReportService $FinancialReportService)
    {
        $this->FinancialReportService = $FinancialReportService;
    }

    /**
     * Retrieves financial report data based on filtered request criteria.
     *
     * @param FinancialReportFilteringData $request Validated filter parameters
     * @return \Illuminate\Http\JsonResponse Financial report response
     */
    public function index(FinancialReportFilteringData $request)
    {
        // Validate and retrieve filtered financial report data
        $result = $this->FinancialReportService->getFinancialReport($request->validated());

        // Return an appropriate response based on the result status
        return $result['status'] === 200
            ? $this->successshow($result['data'], $result['message'], $result['status'])  // Success response with financial report data
            : $this->error(null, $result['message'], $result['status']);  // Error response
    }
}

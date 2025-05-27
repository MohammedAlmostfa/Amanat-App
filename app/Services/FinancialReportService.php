<?php

namespace App\Services;

use Exception;
use App\Services\Service;
use App\Models\CustomerDebt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class FinancialReportService
 *
 * This service handles financial report calculations based on customer debts.
 */
class FinancialReportService extends Service
{
    /**
     * Retrieves financial report data based on a given date range.
     *
     * @param array $filterDate Array containing 'start_date' and 'end_date' for filtering
     * @return \Illuminate\Http\JsonResponse Financial report response with calculated values
     */
    public function getFinancialReport($filterDate)
    {
        try {
            // Determine the start and end date based on provided filter or fallback values
            $startDate = Carbon::parse($filterDate['start_date'] ?? CustomerDebt::first()?->due_date ?? now())->toDateString();
            $endDate = Carbon::parse($filterDate['end_date'] ?? now())->toDateString();

            // Calculate financial metrics based on the filtered date range
            $amount_paid = CustomerDebt::whereBetween('due_date', [$startDate, $endDate])->sum('amount_paid');
            $amount_due = CustomerDebt::whereBetween('due_date', [$startDate, $endDate])->sum('amount_due');
            $remaining_amount = $amount_due - $amount_paid; // Ensure correct calculation for outstanding balance

            // Prepare response data
            $data = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'remaining_amount' => (int)$remaining_amount,
                'amount_paid' =>  (int)$amount_paid,
                'amount_due' =>  (int)$amount_due
            ];

            // Return success response with financial data
            return $this->successResponse('تم استرجاع التقرير المالي بنجاح', $data);
        } catch (\Exception $e) {
            // Log the error details for debugging

            Log::error('حدث خطأ أثناء استرجاع التقرير المالي: ' . $e->getMessage());

            // Return an error response in case of failure
            return $this->errorResponse('حدث خطأ أثناء استرجاع التقرير المالي. يرجى المحاولة لاحقًا.');
        }
    }
}

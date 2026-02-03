<?php

namespace App\Http\Controllers;

use App\Models\BillingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BillingSummaryController extends Controller
{
   public function billing_summary(Request $request)
    {
        try {
            $query = BillingModel::query()
                ->join('users', 'users.id', '=', 'billings.user_id')
                ->selectRaw('
                    billings.user_id,
                    users.name,
                    users.email,
                    SUM(billings.extra_calls) AS total_extra_calls,
                    SUM(billings.amount) AS total_amount
                ')
                ->groupBy(
                    'billings.user_id',
                    'users.name',
                    'users.email'
                );

            if ($request->filled('user_id')) {
                $query->where('billings.user_id', $request->user_id);
            }

            if ($request->filled('billing_month')) {
                $year  = substr($request->billing_month, 0, 4);
                $month = substr($request->billing_month, 5, 2);

                $query->whereYear('billings.billing_month', $year)
                      ->whereMonth('billings.billing_month', $month);
            }

            $data = $query->get();

            return response()->json([
                'status' => true,
                'filters' => [
                    'user_id'       => $request->user_id,
                    'billing_month' => $request->billing_month,
                ],
                'summary' => $data->map(fn ($row) => [
                    'user_id'           => $row->user_id,
                    'name'              => $row->name,
                    'email'             => $row->email,
                    'total_extra_calls' => (int) $row->total_extra_calls,
                    'total_amount'      => number_format($row->total_amount, 2),
                ]),
            ]);
        } catch (\Throwable $e) {
            Log::error(
                __METHOD__ . ' Error',
                [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString()
                ]
            );

            return response()->json([
                'status' => false,
                'error'  => INTERNAL_SERVER_ERROR_MSG
            ], INTERNAL_SERVER_ERROR_CODE);
        }
    }

}

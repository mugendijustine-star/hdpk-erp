<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\FieldLead;
use App\Models\FieldOrder;
use App\Models\FieldVisit;
use App\Models\Sale;
use App\Models\SalesRep;
use App\Models\SalesTarget;
use App\Models\SalesTerritory;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FieldSalesReportController extends Controller
{
    public function repPerformanceJson(Request $request)
    {
        $data = $this->prepareRepPerformanceData($request);

        return response()->json($data['rows']);
    }

    public function repPerformancePdf(Request $request)
    {
        $data = $this->prepareRepPerformanceData($request);

        $pdf = Pdf::loadView('reports.field_sales_rep', $data)->setPaper('a4');

        $dateFrom = $data['date_from'] ?? 'start';
        $dateTo = $data['date_to'] ?? Carbon::today()->toDateString();

        return $pdf->download("field-sales-rep-{$dateFrom}-to-{$dateTo}.pdf");
    }

    public function territoryPerformanceJson(Request $request)
    {
        $data = $this->prepareTerritoryPerformanceData($request);

        return response()->json($data['rows']);
    }

    public function territoryPerformancePdf(Request $request)
    {
        $data = $this->prepareTerritoryPerformanceData($request);

        $pdf = Pdf::loadView('reports.field_sales_territory', $data)->setPaper('a4');

        $dateFrom = $data['date_from'] ?? 'start';
        $dateTo = $data['date_to'] ?? Carbon::today()->toDateString();

        return $pdf->download("field-sales-territory-{$dateFrom}-to-{$dateTo}.pdf");
    }

    private function prepareRepPerformanceData(Request $request): array
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $salesRepId = $request->query('sales_rep_id');

        $reps = SalesRep::query();

        if ($salesRepId) {
            $reps->where('id', $salesRepId);
        }

        $monthSpans = $this->generateMonthSpans($dateFrom, $dateTo);

        $rows = $reps->get()->map(function (SalesRep $rep) use ($dateFrom, $dateTo, $monthSpans) {
            $visitsCount = $this->countWithDateRange(
                FieldVisit::query()->where('sales_rep_id', $rep->id),
                $dateFrom,
                $dateTo,
                ['date']
            );

            $leadsCount = $this->countWithDateRange(
                FieldLead::query()->where('sales_rep_id', $rep->id),
                $dateFrom,
                $dateTo,
                ['created_at']
            );

            $leadsConverted = $this->countWithDateRange(
                FieldLead::query()->where('sales_rep_id', $rep->id)->where('status', 'converted'),
                $dateFrom,
                $dateTo,
                ['created_at']
            );

            $ordersQuery = FieldOrder::query()->where('sales_rep_id', $rep->id);
            $ordersCount = $this->countWithDateRange($ordersQuery, $dateFrom, $dateTo, ['created_at', 'requested_date']);

            $ordersDispatched = $this->countWithDateRange(
                FieldOrder::query()->where('sales_rep_id', $rep->id)->where('status', 'dispatched'),
                $dateFrom,
                $dateTo,
                ['created_at', 'requested_date']
            );

            $salesTotal = $this->sumSalesTotalForOrders(
                FieldOrder::query()->where('sales_rep_id', $rep->id),
                $dateFrom,
                $dateTo
            );

            $targetAmount = $this->sumTargetAmount($rep->id, $monthSpans);
            $achievementPercent = $targetAmount > 0 ? ($salesTotal / $targetAmount) * 100 : 0;

            return [
                'rep' => $rep->name,
                'region' => $rep->region,
                'visits_count' => $visitsCount,
                'leads_count' => $leadsCount,
                'leads_converted' => $leadsConverted,
                'orders_count' => $ordersCount,
                'orders_dispatched_count' => $ordersDispatched,
                'sales_total' => $salesTotal,
                'target_amount' => $targetAmount,
                'achievement_percent' => $achievementPercent,
            ];
        })->values();

        return [
            'rows' => $rows,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function prepareTerritoryPerformanceData(Request $request): array
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $salesTerritoryId = $request->query('sales_territory_id');

        $territories = SalesTerritory::query();

        if ($salesTerritoryId) {
            $territories->where('id', $salesTerritoryId);
        }

        $territoryRows = $territories->get()->map(function (SalesTerritory $territory) use ($dateFrom, $dateTo) {
            $visitsCount = $this->countWithDateRange(
                FieldVisit::query()->where('sales_territory_id', $territory->id),
                $dateFrom,
                $dateTo,
                ['date']
            );

            $ordersQuery = FieldOrder::query()->where('sales_territory_id', $territory->id);
            $ordersCount = $this->countWithDateRange($ordersQuery, $dateFrom, $dateTo, ['created_at', 'requested_date']);

            $ordersDispatched = $this->countWithDateRange(
                FieldOrder::query()->where('sales_territory_id', $territory->id)->where('status', 'dispatched'),
                $dateFrom,
                $dateTo,
                ['created_at', 'requested_date']
            );

            $repIds = $territory->salesReps()->pluck('sales_reps.id')->all();

            $leadsCount = empty($repIds)
                ? 0
                : $this->countWithDateRange(
                    FieldLead::query()->whereIn('sales_rep_id', $repIds),
                    $dateFrom,
                    $dateTo,
                    ['created_at']
                );

            $leadsConverted = empty($repIds)
                ? 0
                : $this->countWithDateRange(
                    FieldLead::query()->whereIn('sales_rep_id', $repIds)->where('status', 'converted'),
                    $dateFrom,
                    $dateTo,
                    ['created_at']
                );

            $salesTotal = $this->sumSalesTotalForOrders(
                FieldOrder::query()->where('sales_territory_id', $territory->id),
                $dateFrom,
                $dateTo
            );

            return [
                'territory' => $territory->name,
                'visits_count' => $visitsCount,
                'leads_count' => $leadsCount,
                'leads_converted' => $leadsConverted,
                'orders_count' => $ordersCount,
                'orders_dispatched_count' => $ordersDispatched,
                'sales_total' => $salesTotal,
            ];
        })->values();

        return [
            'rows' => $territoryRows,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function countWithDateRange($query, ?string $dateFrom, ?string $dateTo, array $columns): int
    {
        $this->applyDateFilters($query, $dateFrom, $dateTo, $columns);

        return $query->count();
    }

    private function applyDateFilters($query, ?string $dateFrom, ?string $dateTo, array $columns): void
    {
        if (!$dateFrom && !$dateTo) {
            return;
        }

        $query->where(function ($outer) use ($dateFrom, $dateTo, $columns) {
            foreach ($columns as $column) {
                $outer->orWhere(function ($inner) use ($dateFrom, $dateTo, $column) {
                    if ($dateFrom) {
                        $inner->whereDate($column, '>=', $dateFrom);
                    }

                    if ($dateTo) {
                        $inner->whereDate($column, '<=', $dateTo);
                    }
                });
            }
        });
    }

    private function sumSalesTotalForOrders($orderQuery, ?string $dateFrom, ?string $dateTo): float
    {
        $this->applyDateFilters($orderQuery, $dateFrom, $dateTo, ['created_at', 'requested_date']);

        /** @var Collection<int, FieldOrder> $orders */
        $orders = $orderQuery->with('sale')->get();

        return $orders->reduce(function ($carry, FieldOrder $order) {
            if (!$order->sale instanceof Sale) {
                return $carry;
            }

            $total = $order->sale->total ?? 0;

            return $carry + ($total ?: 0);
        }, 0.0);
    }

    private function generateMonthSpans(?string $dateFrom, ?string $dateTo): Collection
    {
        if (!$dateFrom || !$dateTo) {
            return collect();
        }

        $start = Carbon::parse($dateFrom)->startOfMonth();
        $end = Carbon::parse($dateTo)->endOfMonth();

        $months = collect();
        $current = $start->copy();

        while ($current->lte($end)) {
            $months->push([
                'month' => $current->month,
                'year' => $current->year,
            ]);

            $current->addMonth();
        }

        return $months;
    }

    private function sumTargetAmount(int $salesRepId, Collection $monthSpans): float
    {
        if ($monthSpans->isEmpty()) {
            return 0.0;
        }

        $targets = SalesTarget::query()->where('sales_rep_id', $salesRepId)->where(function ($query) use ($monthSpans) {
            $monthSpans->each(function ($span) use ($query) {
                $query->orWhere(function ($subQuery) use ($span) {
                    $subQuery
                        ->where('month', $span['month'])
                        ->where('year', $span['year']);
                });
            });
        })->get();

        return $targets->sum(fn (SalesTarget $target) => $target->target_amount ?? 0);
    }
}

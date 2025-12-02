<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Support\NumberObfuscator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class StockLossReportController extends Controller
{
    public function indexJson(Request $request)
    {
        $data = $this->prepareData($request);

        return response()->json($data);
    }

    public function indexPdf(Request $request)
    {
        $data = $this->prepareData($request);

        $pdf = Pdf::loadView('reports.stock_loss', $data)->setPaper('a4');

        $dateFrom = $data['date_from'] ?? 'start';
        $dateTo = $data['date_to'] ?? Carbon::today()->toDateString();

        return $pdf->download("stock-loss-{$dateFrom}-to-{$dateTo}.pdf");
    }

    private function prepareData(Request $request): array
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $branchId = $request->query('branch_id');
        $responsibleUserId = $request->query('responsible_user_id');
        $lossType = $request->query('loss_type');

        $query = DB::table('stock_audits')
            ->join('stock_audit_lines', 'stock_audits.id', '=', 'stock_audit_lines.stock_audit_id')
            ->join('product_variants', 'stock_audit_lines.item_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('users', 'stock_audit_lines.responsible_user_id', '=', 'users.id')
            ->where('stock_audits.status', 'approved')
            ->where('stock_audit_lines.difference_qty', '<', 0);

        if ($dateFrom) {
            $query->whereDate('stock_audits.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('stock_audits.created_at', '<=', $dateTo);
        }

        if ($branchId) {
            $query->where('stock_audits.branch_id', $branchId);
        }

        if ($responsibleUserId) {
            $query->where('stock_audit_lines.responsible_user_id', $responsibleUserId);
        }

        if ($lossType) {
            $query->where('stock_audit_lines.loss_type', $lossType);
        }

        $lines = $query
            ->orderBy('stock_audits.created_at')
            ->select([
                'stock_audits.created_at as audit_date',
                'stock_audits.branch_id',
                'stock_audit_lines.difference_qty',
                'stock_audit_lines.loss_type',
                'stock_audit_lines.manager_comment',
                'stock_audit_lines.admin_comment',
                'products.name as product_name',
                'product_variants.sku',
                'product_variants.size',
                'product_variants.colour',
                'product_variants.cost_enc',
                'users.name as responsible_name',
            ])
            ->get();

        $rows = [];
        $totalQtyLost = 0.0;
        $totalValueLost = 0.0;

        foreach ($lines as $line) {
            $qtyLost = abs((float) ($line->difference_qty ?? 0));
            $valueLost = $qtyLost * $this->decodeNumeric($line->cost_enc);

            $totalQtyLost += $qtyLost;
            $totalValueLost += $valueLost;

            $rows[] = [
                'date' => Carbon::parse($line->audit_date)->toDateString(),
                'branch' => $line->branch_id,
                'product' => $line->product_name,
                'sku' => $line->sku,
                'size' => $line->size,
                'colour' => $line->colour,
                'loss_type' => $line->loss_type,
                'qty_lost' => $qtyLost,
                'value_lost' => $valueLost,
                'responsible' => $line->responsible_name,
                'manager_comment' => $line->manager_comment,
                'admin_comment' => $line->admin_comment,
            ];
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'rows' => $rows,
            'total_qty_lost' => $totalQtyLost,
            'total_value_lost' => $totalValueLost,
        ];
    }

    private function decodeNumeric($encryptedValue): float
    {
        if ($encryptedValue === null) {
            return 0.0;
        }

        $decrypted = Crypt::decryptString($encryptedValue);

        return NumberObfuscator::decode((float) $decrypted) ?? 0.0;
    }
}

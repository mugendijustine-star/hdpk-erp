<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockAudit;
use App\Models\StockAuditLine;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:stock.adjust')->only('approve');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'audit_id' => ['required', 'integer'],
            'lines' => ['required', 'array'],
            'lines.*.item_id' => ['required', 'integer'],
            'lines.*.expected_qty' => ['required', 'numeric'],
            'lines.*.counted_qty' => ['required', 'numeric'],
            'lines.*.difference_qty' => ['required', 'numeric'],
            'lines.*.loss_type' => ['nullable', 'string', 'in:loss,damage,expiry'],
            'lines.*.responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'lines.*.manager_comment' => ['nullable', 'string'],
            'lines.*.admin_comment' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated) {
            $audit = StockAudit::findOrFail($validated['audit_id']);

            $lines = collect($validated['lines'])->map(function ($line) use ($audit) {
                return $audit->lines()->create([
                    'item_id' => $line['item_id'],
                    'expected_qty' => $line['expected_qty'],
                    'counted_qty' => $line['counted_qty'],
                    'difference_qty' => $line['difference_qty'],
                    'loss_type' => $line['loss_type'] ?? null,
                    'responsible_user_id' => $line['responsible_user_id'] ?? null,
                    'manager_comment' => $line['manager_comment'] ?? null,
                    'admin_comment' => $line['admin_comment'] ?? null,
                ]);
            });

            return response()->json([
                'audit' => $audit->fresh('lines'),
                'lines' => $lines,
            ]);
        });
    }

    public function approve(Request $request, StockAudit $audit)
    {
        $validated = $request->validate([
            'lines' => ['required', 'array'],
            'lines.*.id' => ['required', 'integer'],
            'lines.*.admin_comment' => ['nullable', 'string'],
            'lines.*.loss_type' => ['nullable', 'string', 'in:loss,damage,expiry'],
            'lines.*.responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'lines.*.manager_comment' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($validated, $audit) {
            foreach ($validated['lines'] as $lineInput) {
                /** @var StockAuditLine $line */
                $line = $audit->lines()->findOrFail($lineInput['id']);
                $line->fill([
                    'loss_type' => $lineInput['loss_type'] ?? $line->loss_type,
                    'responsible_user_id' => $lineInput['responsible_user_id'] ?? $line->responsible_user_id,
                    'manager_comment' => $lineInput['manager_comment'] ?? $line->manager_comment,
                    'admin_comment' => $lineInput['admin_comment'] ?? $line->admin_comment,
                ]);
                $line->save();

                $differenceQty = (float) $line->difference_qty;

                if ($differenceQty < 0 && !$line->stockMovement) {
                    $movement = new StockMovement([
                        'type' => 'adjustment',
                        'qty_change' => $differenceQty,
                        'reference' => 'STOCK-AUDIT-' . $audit->id,
                        'branch_id' => $audit->branch_id,
                        'user_id' => Auth::id(),
                    ]);

                    $line->stockMovement()->save($movement);

                    // Dr Stock Loss Expense / Damaged Stock Expense
                    // Cr Inventory
                }
            }

            $audit->status = 'approved';
            $audit->approved_by = Auth::id();
            $audit->approved_at = now();
            $audit->save();

            return response()->json($audit->fresh('lines'));
        });
    }
}

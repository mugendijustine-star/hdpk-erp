<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    /**
     * Download a delivery note PDF for the given sale.
     */
    public function printDeliveryNote(Sale $sale)
    {
        $sale->load(['customer', 'lines']);

        $pdf = Pdf::loadView('documents.delivery_note', [
            'sale' => $sale,
        ]);

        return $pdf->download('delivery-note-' . $sale->id . '.pdf');
    }
}

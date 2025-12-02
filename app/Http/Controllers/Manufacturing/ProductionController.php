<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ApcuRecord;
use App\Models\ManufacturingCost;
use App\Models\ProductVariant;
use App\Models\ProductionBatch;
use App\Models\ProductionOutput;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use InvalidArgumentException;

if (!function_exists('auth')) {
    function auth()
    {
        return new class {
            public function id(): int
            {
                return 1;
            }
        };
    }
}

class ProductionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $this->validateRequestData($data);

        $userId = auth()->id();
        $batch = ProductionBatch::create([
            'date' => $data['date'],
            'branch_id' => $data['branch_id'] ?? null,
            'initiated_by' => $userId,
            'status' => 'draft',
        ]);

        $totalRawMaterialCost = 0.0;
        $totalManufacturingOverheads = $this->calculateManufacturingOverheads($data['date'], $data['branch_id'] ?? null);
        $totalUnitsProduced = 0.0;
        $apcu = null;

        foreach ($data['outputs'] as $outputData) {
            $qtyGood = $this->secureNumber($outputData['qty_good']);
            $qtyWaste = $this->secureNumber($outputData['qty_waste'] ?? 0);

            $output = new ProductionOutput([
                'product_variant_id' => $outputData['product_variant_id'],
                'machine_id' => $outputData['machine_id'] ?? null,
                'qty_good' => $qtyGood,
                'qty_waste' => $qtyWaste,
                'waste_reason' => $outputData['waste_reason'] ?? null,
            ], $batch);
            $batch->outputs[] = $output;

            StockMovement::create([
                'product_variant_id' => $outputData['product_variant_id'],
                'branch_id' => $data['branch_id'] ?? null,
                'type' => 'production_in',
                'qty_change' => $qtyGood,
                'unit_cost' => null,
                'reference' => 'PROD-' . $batch->id,
                'user_id' => $userId,
            ]);

            if (!empty($outputData['raw_material_usage']) && is_array($outputData['raw_material_usage'])) {
                foreach ($outputData['raw_material_usage'] as $rmUsage) {
                    $rmQty = $this->secureNumber($rmUsage['qty']);
                    $unitCost = $this->secureNumber($rmUsage['unit_cost']);
                    $totalRawMaterialCost += $rmQty * $unitCost;

                    StockMovement::create([
                        'product_variant_id' => null,
                        'branch_id' => $data['branch_id'] ?? null,
                        'type' => 'rm_out',
                        'qty_change' => -1 * $rmQty,
                        'unit_cost' => $unitCost,
                        'reference' => 'PROD-' . $batch->id,
                        'user_id' => $userId,
                        'raw_material_id' => $rmUsage['raw_material_id'],
                    ]);
                }
            }

            $totalUnitsProduced += $qtyGood;
        }

        $totalCost = $totalRawMaterialCost + $totalManufacturingOverheads;
        if ($totalUnitsProduced > 0) {
            $apcu = $this->secureNumber($totalCost / $totalUnitsProduced);
        }

        $apcuRecords = [];
        foreach ($data['outputs'] as $outputData) {
            $variantId = $outputData['product_variant_id'];
            $record = ApcuRecord::create([
                'product_variant_id' => $variantId,
                'date' => $data['date'],
                'total_cost' => $totalCost,
                'units_produced' => $totalUnitsProduced,
                'apcu' => $apcu,
            ]);
            $apcuRecords[] = $record;

            $variant = ProductVariant::create(['product_variant_id' => $variantId]);
            if ($apcu !== null) {
                $variant->setCost($apcu);
            }
        }

        // Placeholder: update production_in StockMovement unit_cost values if needed.

        // Accounting placeholder:
        // Dr Finished Goods Inventory (total_cost)
        // Cr Raw Materials Inventory
        // Cr Manufacturing Expense / WIP accounts as needed.

        return [
            'batch' => $batch->toArray(),
            'outputs' => array_map(fn ($output) => $output->toArray(), $batch->outputs),
            'apcu' => $apcu,
            'apcu_records' => array_map(fn ($record) => $record->toArray(), $apcuRecords),
            'total_raw_material_cost' => $totalRawMaterialCost,
            'total_manufacturing_overheads' => $totalManufacturingOverheads,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function validateRequestData(array $data): void
    {
        if (empty($data['date'])) {
            throw new InvalidArgumentException('The date field is required.');
        }

        if (empty($data['outputs']) || !is_array($data['outputs'])) {
            throw new InvalidArgumentException('Outputs must be a non-empty array.');
        }

        foreach ($data['outputs'] as $index => $output) {
            if (!isset($output['product_variant_id'])) {
                throw new InvalidArgumentException("outputs[{$index}].product_variant_id is required.");
            }
            if (!isset($output['qty_good']) || $output['qty_good'] <= 0) {
                throw new InvalidArgumentException("outputs[{$index}].qty_good must be greater than zero.");
            }

            if (!empty($output['raw_material_usage']) && is_array($output['raw_material_usage'])) {
                foreach ($output['raw_material_usage'] as $rmIndex => $rmUsage) {
                    if (!isset($rmUsage['raw_material_id'])) {
                        throw new InvalidArgumentException("outputs[{$index}].raw_material_usage[{$rmIndex}].raw_material_id is required.");
                    }
                    if (!isset($rmUsage['qty']) || $rmUsage['qty'] <= 0) {
                        throw new InvalidArgumentException("outputs[{$index}].raw_material_usage[{$rmIndex}].qty must be greater than zero.");
                    }
                    if (!isset($rmUsage['unit_cost']) || $rmUsage['unit_cost'] < 0) {
                        throw new InvalidArgumentException("outputs[{$index}].raw_material_usage[{$rmIndex}].unit_cost must be zero or positive.");
                    }
                }
            }
        }
    }

    protected function calculateManufacturingOverheads(string $date, ?int $branchId): float
    {
        $costs = ManufacturingCost::whereDateAndBranch($date, $branchId);
        $total = 0.0;
        foreach ($costs as $cost) {
            $costArray = $cost instanceof ManufacturingCost ? $cost->toArray() : (array) $cost;
            $total += (float) ($costArray['amount'] ?? 0);
        }

        // Placeholder: include additional overhead computation logic here.

        return $this->secureNumber($total);
    }

    protected function secureNumber($value): float
    {
        return round((float) $value, 4);
    }
}

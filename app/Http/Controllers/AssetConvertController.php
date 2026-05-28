<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetReceipt;
use App\Models\AssetReceiptItem;
use App\Services\AssetNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetConvertController extends Controller
{
    public function index()
    {
        return view('assets.convert.index', [
            'receiptItems' => AssetReceiptItem::with(['receipt.procurement', 'procurementItem'])
                ->where('is_converted_to_asset', false)
                ->where('quantity_received', '>', 0)
                ->latest()
                ->paginate(10),
        ]);
    }

    public function show(AssetReceipt $receipt)
    {
        $receipt->load(['procurement', 'items.procurementItem']);

        return view('assets.convert.show', ['receipt' => $receipt]);
    }

    public function convert(Request $request, AssetReceiptItem $receiptItem, AssetNumberService $numberService)
    {
        if ($receiptItem->is_converted_to_asset) {
            return back()->with('error', 'Item ini sudah dikonversi menjadi aset.');
        }

        $created = DB::transaction(function () use ($request, $receiptItem, $numberService) {
            $receiptItem->load('receipt.procurement');
            $quantity = max(1, (int) floor((float) $receiptItem->quantity_received));
            $assets = collect();

            for ($i = 1; $i <= $quantity; $i++) {
                $asset = Asset::create([
                    'asset_code' => $numberService->assetCode(),
                    'procurement_id' => $receiptItem->receipt->procurement_id,
                    'receipt_id' => $receiptItem->receipt_id,
                    'name' => $quantity > 1 ? "{$receiptItem->item_name} {$i}" : $receiptItem->item_name,
                    'specification' => $receiptItem->procurementItem?->specification,
                    'acquisition_date' => $receiptItem->receipt->received_date,
                    'supplier_name' => $receiptItem->receipt->supplier_name,
                    'source_type' => 'procurement',
                    'condition' => $this->normalizeCondition($receiptItem->condition),
                    'status' => $this->normalizeCondition($receiptItem->condition) === 'damaged' ? 'not_usable' : 'active',
                ]);

                $asset->statusLogs()->create([
                    'new_status' => $asset->status,
                    'new_condition' => $asset->condition,
                    'description' => 'Aset dibuat dari proses pengadaan.',
                    'changed_by' => $request->user()?->id,
                ]);

                $assets->push($asset);
            }

            $receiptItem->update(['is_converted_to_asset' => true]);
            $receipt = $receiptItem->receipt()->with('items')->first();
            if ($receipt->items->every(fn ($item) => $item->is_converted_to_asset)) {
                $receipt->update(['status' => 'converted_to_asset']);
                $receipt->procurement?->update(['status' => 'converted_to_asset']);
            }

            return $assets->count();
        });

        return redirect()->route('assets.convert.index')->with('success', "{$created} aset berhasil dibuat dari item penerimaan.");
    }

    private function normalizeCondition(?string $condition): string
    {
        return in_array($condition, ['good', 'minor_damage', 'damaged', 'under_repair', 'unknown'], true)
            ? $condition
            : 'good';
    }
}

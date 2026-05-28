<?php

namespace App\Http\Controllers;

use App\Models\AssetSupplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssetSupplierController extends Controller
{
    public function index()
    {
        $suppliers = AssetSupplier::query()
            ->when(request('search'), fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('assets.suppliers.index', [
            'suppliers' => $suppliers,
            'statuses' => $this->statuses(),
        ]);
    }

    public function create()
    {
        return view('assets.suppliers.create', [
            'supplier' => new AssetSupplier(['status' => 'active']),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request)
    {
        AssetSupplier::create($this->validatedSupplier($request));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(AssetSupplier $supplier)
    {
        return view('assets.suppliers.edit', [
            'supplier' => $supplier,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, AssetSupplier $supplier)
    {
        $supplier->update($this->validatedSupplier($request, $supplier));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(AssetSupplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    private function validatedSupplier(Request $request, ?AssetSupplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_suppliers', 'name')->ignore($supplier)],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in($this->statuses())],
        ]);
    }

    private function statuses(): array
    {
        return ['active', 'inactive'];
    }
}

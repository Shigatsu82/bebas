<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;

class BarangKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BarangKeluar::latest()->paginate(5);
        return view('outproducts.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productId = Product::all();
        return view('outproducts.create', compact('productId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $barangMasuk = BarangMasuk::where('product_id', $request->product_id)->latest()->first();
        $request->validate([
            'tgl_keluar' => ['required', 'date', $barangMasuk ? 'after:' . $barangMasuk->tgl_masuk : $barangMasuk->tgl_masuk],
            'qty' => ['required'],
            'product_id' => ['required'],
        ],[
            'after' => 'Tanggal mu salah'
        ]);

        $currentStock = Product::find($request->product_id);
        if($request->qty > $currentStock->stock){
            return back()->with('error', 'Kuantitas melebihi stok yang tersedia');
        }

        BarangKeluar::create($request->all());
        return redirect()->route('outproducts.index')->with('success', 'Barang Keluar record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = BarangKeluar::find($id);
        return view('outproducts.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $barangKeluar = BarangKeluar::find($id);
        return view('outproducts.edit', compact('barangKeluar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tgl_keluar',
            'qty',
            'product_id',
        ]);
        $currentStock = Product::find($request->product_id);
        if($request->qty > $currentStock->stock){
            return back()->with('error', 'Kuantitas melebihi stok yang tersedia');
        }else{
            $barangKeluar = BarangKeluar::find($id);
            $barangKeluar->update($request->all());
            return redirect()->route('outproducts.index')->with('success', 'Barang Keluar record updated successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = BarangKeluar::find($id);
        $item->delete();
        return redirect()->route('outproducts.index')->with('success', 'Barang Keluar record deleted successfully.');
    }
}

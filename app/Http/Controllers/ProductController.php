<?php

namespace App\Http\Controllers;

//import model product
use App\Models\Category;

//import return type View
use App\Models\Product; 

//import return type redirectResponse
use Illuminate\View\View;

//import Http Request
use App\Models\BarangMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index(Request $request) : View
    { 
        $searchTerm = $request->search;
        $productQuery = Product::query()
        ->select('products.id', 'products.title', 'products.image', 'products.stock', 'products.price', 'products.description', 'categories.category')
        ->join('categories', 'products.category_id', '=', 'categories.id');

        if($searchTerm){
            $productQuery->where(function($query) use ($searchTerm){
                $query->where('products.id', 'like', '%' . $searchTerm . '%')
                ->orWhere('products.title', 'like', '%' . $searchTerm . '%')
                ->orWhere('categories.category', 'like', '%' . $searchTerm . '%')
                ->orWhere('products.description', 'like', '%' . $searchTerm . '%');
            });
        }

        $products = $productQuery->paginate(3);

        $products->getCollection()->transform(function ($product){
            $categoryInfo = DB::select('SELECT infoKategori(?) as infoCategory', [$product->category])[0]->infoCategory;
            $product->category = $categoryInfo;
            return $product;
        });
        return view('products.index', compact('products'));
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        $categoryId= Category::all();
        return view('products.create', compact('categoryId'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        //validate form
        $request->validate([
            'image'         => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'title'         => 'required|min:5',
            'category_id'   => 'required',
            'description'   => 'required|min:1',
            'price'         => 'numeric',
            'stock'         => 'required|numeric'
        ]);
        //upload image
        $image = $request->file('image');
        $image->storeAs('public/products/', $image->hashName());
        //create product
        $barangInput = Product::create([
            'image'         => $image->hashName(),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => 0,
            'category_id'   => $request->category_id, //change to 'category_id
        ]);

        if($request->stock > 0){
            BarangMasuk::create([
                'tgl_masuk' => now(),
                'qty' => $request->stock,
                'product_id' => $barangInput->id,
            ]);
        }

        //redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    public function show(string $id){
        $product = Product::findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit(string $id){
        $product = Product::findOrFail($id);
        $categoryId = Category::all();

        //render view with product
        return view('products.edit', compact('product', 'categoryId'));
    }

    public function update(Request $request, $id){
        $request->validate([
            'image' => 'image|mimes:jpg,jpeg,png|max:5000',
            'title' => 'required|min:4|max:100',
            'description' => 'required|min:10',
            'price' => 'required|numeric',
            'stock' => 'required|numeric'
        ]);

        $product = Product::findOrFail($id);

        if ($request->hasFile('image')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/products/', $image->hashName());

            //delete old image
            Storage::delete('public/products/'.$product->image);

            //update product with new image
            $product->update([
                'image'         => $image->hashName(),
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);

        } else {

            //update product without image
            $product->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);
        }
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Diedit!']);
    }

    public function destroy(string $id){
        $product = Product::findOrFail($id);

        Storage::delete('public/products/'. $product->image);
        $product->delete();
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus!']);

    }
}
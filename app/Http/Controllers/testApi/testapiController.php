<?php

namespace App\Http\Controllers\testApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\testapi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class testapiController extends Controller
{
    public function index()
    {
        $products = testapi::latest()->paginate(5);
        return new ProductResource (true, 'List DataProduct', $products);
    }

    public function store(Request $request)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'         => 'required',
            'description'   => 'required',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('products', $image->hashName(), 'public');

        //create product
        $product = testapi::create([
            'image'         => $image->hashName(),
            'title'         => $request->title,
            'description'   => $request->description,
            'price'         => $request->price,
            'stock'         => $request->stock,
        ]);

        //return response
        return new ProductResource(true, 'Data Product Berhasil Ditambahkan!', $product);
    } 
    
    public function show($id)
    {
        $product = testapi::find($id);

        //return response
        return new ProductResource(true, 'Data Product Ditemukan!', $product);
    }

}

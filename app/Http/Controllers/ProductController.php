<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['search']]);
    }

    /**
     * Insere um produto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sell(Request $request)
    {
        $this->validate($request, [
            'id_category' => 'required|exists:category,id',
            'author' => 'required|string|min:10|max:255',
            'title' => 'required|string|min:5|max:50',
            'identifier' => 'required|string|min:3|max:50',
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/', 'gte:0.0'],
            'brief_desc' => 'required|string|min:20|max:255',
        ]);

        $id_seller = Auth::user()->id;

        if ( Auth::user()->sys_level > config('enums.USER_LEVEL.COMMOM')){
            return response()->json(['message' => 'Valid only for commom users.'], 403);
        }

        $category = Category::findOrFail($request->input('id_category'));

        if (!$category->active){
            return response()->json(['message' => 'Category inactive.'], 403);
        }

        $product = new Product([
            'id_category' => $request->input('id_category'),
            'id_seller' => $id_seller,
            'active' => true, 
            'already_selled' => false,
            'identifier' => $request->input('identifier'),
            'author' => $request->input('author'),
            'title' => $request->input('title'),
            'brief_desc' => $request->input('brief_desc'),
            'person_desc' => $request->input('person_desc'),         
            'price' => $request->input('price'),
        ]);

        $product->save();

        return response()->json(['message' => 'Added to sell catalogue!'], 200);
    }

    /**
     * Edita um produto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {

            $requestData = $request->json()->all();

            $active = $requestData['active'];

            $validator = Validator::make(['active' => $active], [
                'active' => 'required|boolean',
            ]);           

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }          

            $product = Product::findOrFail($id);

            if (($product->id_seller != Auth::user()->id) && (Auth::user()->sys_level < config('enums.USER_LEVEL.MODERATOR'))){
                return response()->json(['message' => 'Impossible edit non-self product.'], 403);
            }          

            $product->active = $active;
            $product->save();

            return response()->json(['product' => $product], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Realiza a compra de um produto (atribui um comprador).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function buy(Request $request, $id)
    {
        try {

            $buyer_id = Auth::user()->id;

            if ( Auth::user()->sys_level > config('enums.USER_LEVEL.COMMOM')){
                return response()->json(['message' => 'Valid only for commom users.'], 403);
            }

            $product = Product::findOrFail($id);

            if (! $product->active) {
                return response()->json(['error' => 'Product indisponible.'], 400);
            }

            if ($product->already_selled) {
                return response()->json(['error' => 'Product already selled.'], 400);
            }

            if ($product->id_seller === $buyer_id) {
                return response()->json(['error' => 'Impossible buy self item.'], 400);
            }

            $transaction = Transaction::create([
                'product_id' => $id,
                'id_buyer' => $buyer_id,
                'status' => 1, 
                'price' => $product->price,
            ]);

            $product->update(['already_selled' => true]);

            return response()->json(['message' => 'Buyed!', 'transaction' => $transaction], 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Realiza a procura de um produto com base em critérios 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $type_search = $request->query('type_search');
        $search_key = $request->query('search_key');

        if (!$type_search || (!$search_key)) {
            return response()->json(['error' => 'Type search and search key or category_id are required.'], 400);
        }

        $productsQuery = Product::where('active', true)
            ->where('already_selled', false);

        $products = [];

        switch ($type_search) {
            case 1:
                $products = $productsQuery->where('title', 'LIKE', "%{$search_key}%")->get();
                break;
            case 2:
                $products = $productsQuery->where('identifier', $search_key)->get();
                break;
            case 3:
                $products = $productsQuery->where('author', 'LIKE', "%{$search_key}%")->get();
                break;
            case 4:
                $products = $productsQuery->where('id_category', $search_key)->get();
                break;
            default:
                return response()->json(['error' => 'Invalid type search.'], 400);
        }

        return response()->json(['products' => $products], 200);
    }
    

}

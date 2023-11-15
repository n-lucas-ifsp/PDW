<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Traduz o tipo numérico para string descricional
     *
     * @return string
     */
    private function getMainTypeDescription($mainType)
    {
        switch ($mainType) {
            case 1:
                return 'Book';
            case 2:
                return 'Magazine';
            case 3:
                return 'Journal';
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Lista todas as categorias.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $user = Auth::user();

        if ($user && $user->sys_level >= config('enums.USER_LEVEL.MODERATOR')){
            $categories = Category::all()->map(function ($category) {
                $category->MainTypeDesc = $this->getMainTypeDescription($category->main_type);
                return $category;
            });
        } else {
            $categories = Category::where('active', true)->get()->map(function ($category) {
                $category->MainTypeDesc = $this->getMainTypeDescription($category->main_type);
                return $category;
            });
        }

        return response()->json(['categories' => $categories], 200);
    }

    /**
     * Add a new category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        try {
            $this->validate($request, [
                'active' => 'required|boolean',
                'main_type' => 'required|integer|gte:1|lte:3',
                'title' => 'required|string|min:5|max:20',
            ]);

            $category = Category::create($request->all());

            return response()->json(['category' => $category], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        }
    }

    /**
     * Edit a category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        try {

            $requestData = $request->json()->all();

            $title = $requestData['title'];

            $validator = Validator::make(['title' => $title], [
                'title' => 'required|string|min:5|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $category = Category::findOrFail($id);
            $category->title = $title;
            $category->save();

            return response()->json(['category' => $category], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete/inactivate product category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reenable($id)
    {
        $category = Category::findOrFail($id);

        if ($category->active > 0) {
            return response()->json(['message' => 'Already enabled.'], 400);
        } else {
            $category->update(['active' => true]);

            $productsCount = $category->products()->count();

            if ($productsCount > 0) {
                $category->products()->update(['active' => true]);
            }
            
            return response()->json(['message' => 'Success!', 'produtsReenabled' => $productsCount], 200);
        }
    }

    /**
     * Delete/inactivate product category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function exclude($id)
    {
        $category = Category::findOrFail($id);

        $productsCount = $category->products()->count();

        if ($productsCount > 0) {
            $category->products()->update(['active' => false]);

            $category->update(['active' => false]);

            return response()->json(['message' => 'Inactivated only.', 'produtsDisabled' => $productsCount], 200);
        } else {
            $category->delete();
            
            return response()->json(['message' => 'Sucess!'], 200);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Session;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $input         = $request->all();
        $search        = (object) null;
        $arr['search'] = $search;
        $showrecord    = trans('admin.ADMIN_PAGE_LIMIT_NO');
        $products      = new Product();

        if (isset($input['search_text']) && $input['search_text'] != '') {
            $search_text          = $input['search_text'];
            $products             = $products->where('name', 'LIKE', "%$search_text%")
                                             ->orWhere('brand', 'LIKE', "%$search_text%")
                                             ->orWhere('model_number', 'LIKE', "%$search_text%");
            $search->search_text  = $search_text;
        }

        if (isset($input['status']) && $input['status'] !== '') {
            $status            = $input['status'];
            $products          = $products->where('status', $status);
            $search->status    = $status;
        }

        if (isset($input['showrecord']) && $input['showrecord'] != '') {
            $showrecord         = $input['showrecord'];
            $search->showrecord = $showrecord;
            Session::put('showrecord', $showrecord);
        }

        $filters = $request->all();
        unset($filters['_token']);
        $arr['filters'] = $filters;

        $products = $products->orderBy('id', 'desc')->paginate($showrecord);

        return view('admin.products.index', compact('products'))->with($arr)
            ->with('i', ($request->input('page', 1) - 1) * $showrecord);
    }

    public function create()
    {
        return View::make('admin.products.add');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'         => 'required|string|max:255',
            'brand'        => 'required|string|max:255',
            'model_number' => 'required|string|max:255|unique:products,model_number',
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:0,1',
        ]);

        $data    = $request->all();
        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product->id)
            ->with('alert-success', 'Product has been created successfully');
    }

    public function show($id)
    {
        $product = Product::find($id);
        if (empty($product)) {
            return redirect()->route('admin.products.index')
                ->with('alert-error', 'Product not found');
        }

        return view('admin.products.view', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::find($id);
        if (empty($product)) {
            return redirect()->route('admin.products.index')
                ->with('alert-error', 'Product not found');
        }

        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'         => 'required|string|max:255',
            'brand'        => 'required|string|max:255',
            'model_number' => 'required|string|max:255|unique:products,model_number,' . $id,
            'description'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:0,1',
        ]);

        $product = Product::find($id);
        $product->update($request->all());

        return redirect()->route('admin.products.show', $product->id)
            ->with('alert-success', 'Product has been updated successfully');
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (empty($product)) {
            return redirect()->route('admin.products.index')
                ->with('alert-error', 'Product not found');
        }
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('alert-success', 'Product has been deleted successfully');
    }
}

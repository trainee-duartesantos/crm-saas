<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealProduct;
use Illuminate\Http\Request;

class DealProductController extends Controller
{
    public function store(Request $request, Deal $deal)
    {
        abort_if($deal->tenant_id !== app('tenant')->id, 403);

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        DealProduct::create([
            'tenant_id' => app('tenant')->id,
            'deal_id' => $deal->id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
        ]);

        activity_log('deal.product.added', $deal, [
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
        ]);

        return back();
    }

    public function destroy(DealProduct $dealProduct)
    {
        abort_if($dealProduct->tenant_id !== app('tenant')->id, 403);

        $dealProduct->delete();

        activity_log('deal.product.removed', null, [
            'deal_id' => $dealProduct->deal_id,
            'product_id' => $dealProduct->product_id,
        ]);

        return back();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function show($id)
    {
        return view('customer', [
            'customer' => Customers::find($id)
        ]);
    }

    public function store(Request $request)
    {
        $customer = new Customers();

        $customer->name = $request->name;
        $customer->phone = $request->phone;

        $customer->save();

        return redirect()->action([CustomerController::class, 'show'], ['id' => $customer->id]);
    }

    public function destroy($id)
    {
        $customer = Customers::find($id);
        $customer->delete();

        return view('welcome');
    }
}

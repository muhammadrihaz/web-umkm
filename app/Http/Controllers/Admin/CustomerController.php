<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()->where('role', 'customer')->withCount('orders')->withSum('orders', 'total')
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === 'customer', 404);

        return view('admin.customers.show', ['customer' => $customer->load('addresses'), 'orders' => $customer->orders()->latest()->paginate(20)]);
    }
}

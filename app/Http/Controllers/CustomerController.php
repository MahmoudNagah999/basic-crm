<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Customer::query()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $customer = Customer::query()->create($validated);

        return response()->json($customer, Response::HTTP_CREATED);
    }

    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $customer->update($validated);

        return response()->json($customer->refresh());
    }

    public function destroy(Customer $customer): Response
    {
        $customer->delete();

        return response()->noContent();
    }
}

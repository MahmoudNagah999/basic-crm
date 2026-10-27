<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Invoice::query()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'total' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'items' => ['required', 'array', 'list', 'min:1'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'status' => ['required', 'boolean'],
        ]);

        $invoice = Invoice::query()->create($validated);

        return response()->json($invoice, Response::HTTP_CREATED);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'total' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'items' => ['required', 'array', 'list', 'min:1'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'status' => ['required', 'boolean'],
        ]);

        $invoice->update($validated);

        return response()->json($invoice->refresh());
    }

    public function destroy(Invoice $invoice): Response
    {
        $invoice->delete();

        return response()->noContent();
    }
}

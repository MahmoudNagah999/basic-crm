<?php

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lists invoices with items and timestamps', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $items = [
        ['name' => 'Consulting', 'quantity' => 2, 'price' => '50.00'],
    ];
    $invoice = Invoice::query()->create([
        'total' => '100.00',
        'items' => $items,
        'customer_id' => $customer->id,
        'status' => true,
    ]);

    $this->getJson('/api/invoices')
        ->assertOk()
        ->assertJsonStructure([
            ['id', 'total', 'items', 'customer_id', 'status', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('0.id', $invoice->id)
        ->assertJsonPath('0.total', '100.00')
        ->assertJsonPath('0.items', $items)
        ->assertJsonPath('0.customer_id', $customer->id)
        ->assertJsonPath('0.status', true)
        ->assertJsonPath('0.created_at', $invoice->created_at->toJSON())
        ->assertJsonPath('0.updated_at', $invoice->updated_at->toJSON());
});

test('creates an invoice for an existing customer', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $items = [
        ['name' => 'Consulting', 'quantity' => 2, 'price' => '50.00'],
    ];

    $this->postJson('/api/invoices', [
        'total' => '100.00',
        'items' => $items,
        'customer_id' => $customer->id,
        'status' => true,
    ])
        ->assertCreated()
        ->assertJsonStructure(['id', 'total', 'items', 'customer_id', 'status', 'created_at', 'updated_at'])
        ->assertJsonPath('id', 1)
        ->assertJsonPath('total', '100.00')
        ->assertJsonPath('items', $items)
        ->assertJsonPath('customer_id', $customer->id)
        ->assertJsonPath('status', true);

    $this->assertDatabaseHas('invoices', [
        'id' => 1,
        'total' => '100.00',
        'customer_id' => $customer->id,
        'status' => true,
    ]);

    expect(Invoice::query()->findOrFail(1)->items)->toBe($items);
});

test('requires valid invoice fields when creating an invoice', function (array $data, string $field) {
    $this->postJson('/api/invoices', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    $this->assertDatabaseCount('invoices', 0);
})->with([
    'missing total' => [
        ['items' => [['name' => 'Consulting']], 'customer_id' => 1, 'status' => true],
        'total',
    ],
    'negative total' => [
        ['total' => '-1.00', 'items' => [['name' => 'Consulting']], 'customer_id' => 1, 'status' => true],
        'total',
    ],
    'total with more than two decimals' => [
        ['total' => '1.234', 'items' => [['name' => 'Consulting']], 'customer_id' => 1, 'status' => true],
        'total',
    ],
    'missing items' => [
        ['total' => '100.00', 'customer_id' => 1, 'status' => true],
        'items',
    ],
    'items must be a list' => [
        ['total' => '100.00', 'items' => ['service' => 'Consulting'], 'customer_id' => 1, 'status' => true],
        'items',
    ],
    'items cannot be empty' => [
        ['total' => '100.00', 'items' => [], 'customer_id' => 1, 'status' => true],
        'items',
    ],
    'missing customer' => [
        ['total' => '100.00', 'items' => [['name' => 'Consulting']], 'status' => true],
        'customer_id',
    ],
    'unknown customer' => [
        ['total' => '100.00', 'items' => [['name' => 'Consulting']], 'customer_id' => 999, 'status' => true],
        'customer_id',
    ],
    'missing status' => [
        ['total' => '100.00', 'items' => [['name' => 'Consulting']], 'customer_id' => 1],
        'status',
    ],
    'invalid status' => [
        ['total' => '100.00', 'items' => [['name' => 'Consulting']], 'customer_id' => 1, 'status' => 'paid'],
        'status',
    ],
]);

test('shows an invoice', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $items = [['name' => 'Consulting', 'quantity' => 2, 'price' => '50.00']];
    $invoice = Invoice::query()->create([
        'total' => '100.00',
        'items' => $items,
        'customer_id' => $customer->id,
        'status' => true,
    ]);

    $this->getJson("/api/invoices/{$invoice->id}")
        ->assertOk()
        ->assertJsonPath('id', $invoice->id)
        ->assertJsonPath('items', $items)
        ->assertJsonPath('status', true);
});

test('updates an invoice', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $invoice = Invoice::query()->create([
        'total' => '100.00',
        'items' => [['name' => 'Consulting', 'quantity' => 2, 'price' => '50.00']],
        'customer_id' => $customer->id,
        'status' => true,
    ]);
    $updatedItems = [['name' => 'Support', 'quantity' => 1, 'price' => '75.00']];

    $this->putJson("/api/invoices/{$invoice->id}", [
        'total' => '75.00',
        'items' => $updatedItems,
        'customer_id' => $customer->id,
        'status' => false,
    ])
        ->assertOk()
        ->assertJsonPath('total', '75.00')
        ->assertJsonPath('items', $updatedItems)
        ->assertJsonPath('status', false);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'total' => '75.00',
        'customer_id' => $customer->id,
        'status' => false,
    ]);

    expect($invoice->refresh()->items)->toBe($updatedItems);
});

test('returns not found for an invoice that does not exist', function () {
    $this->getJson('/api/invoices/999')
        ->assertNotFound();
});

test('deletes an invoice', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $invoice = Invoice::query()->create([
        'total' => '100.00',
        'items' => [['name' => 'Consulting', 'quantity' => 2, 'price' => '50.00']],
        'customer_id' => $customer->id,
        'status' => true,
    ]);

    $this->deleteJson("/api/invoices/{$invoice->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
});

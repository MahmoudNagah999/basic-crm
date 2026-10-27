<?php

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lists customers with timestamps', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);

    $this->getJson('/api/customers')
        ->assertOk()
        ->assertJsonStructure([
            ['id', 'name', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('0.id', $customer->id)
        ->assertJsonPath('0.name', 'Ada Lovelace')
        ->assertJsonPath('0.created_at', $customer->created_at->toJSON())
        ->assertJsonPath('0.updated_at', $customer->updated_at->toJSON());
});

test('creates a customer', function () {
    $response = $this->postJson('/api/customers', ['name' => 'Ada Lovelace'])
        ->assertCreated()
        ->assertJsonStructure(['id', 'name', 'created_at', 'updated_at'])
        ->assertJsonPath('id', 1)
        ->assertJsonPath('name', 'Ada Lovelace');

    expect($response->json('created_at'))->toBeString()
        ->and($response->json('updated_at'))->toBe($response->json('created_at'));

    $this->assertDatabaseHas('customers', [
        'id' => 1,
        'name' => 'Ada Lovelace',
    ]);
});

test('requires a valid name when creating a customer', function (array $data) {
    $this->postJson('/api/customers', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->assertDatabaseCount('customers', 0);
})->with([
    'missing name' => [[]],
    'non-string name' => [['name' => 123]],
    'name over 255 characters' => [['name' => str_repeat('a', 256)]],
]);

test('shows a customer', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);

    $this->getJson("/api/customers/{$customer->id}")
        ->assertOk()
        ->assertJsonStructure(['id', 'name', 'created_at', 'updated_at'])
        ->assertJsonPath('id', $customer->id)
        ->assertJsonPath('name', 'Ada Lovelace');
});

test('updates a customer', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);

    $this->putJson("/api/customers/{$customer->id}", ['name' => 'Grace Hopper'])
        ->assertOk()
        ->assertJsonStructure(['id', 'name', 'created_at', 'updated_at'])
        ->assertJsonPath('id', $customer->id)
        ->assertJsonPath('name', 'Grace Hopper');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Grace Hopper',
    ]);
});

test('returns not found for a customer that does not exist', function () {
    $this->getJson('/api/customers/999')
        ->assertNotFound();
});

test('deletes a customer', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);

    $this->deleteJson("/api/customers/{$customer->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
});

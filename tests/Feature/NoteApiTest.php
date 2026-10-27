<?php

use App\Models\Customer;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lists notes with customer ids and timestamps', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $note = Note::query()->create([
        'note' => 'Follow up next week.',
        'customer_id' => $customer->id,
    ]);

    $this->getJson('/api/notes')
        ->assertOk()
        ->assertJsonStructure([
            ['id', 'note', 'customer_id', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('0.id', $note->id)
        ->assertJsonPath('0.note', 'Follow up next week.')
        ->assertJsonPath('0.customer_id', $customer->id)
        ->assertJsonPath('0.created_at', $note->created_at->toJSON())
        ->assertJsonPath('0.updated_at', $note->updated_at->toJSON());
});

test('creates a note for an existing customer', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);

    $this->postJson('/api/notes', [
        'note' => 'Follow up next week.',
        'customer_id' => $customer->id,
    ])
        ->assertCreated()
        ->assertJsonStructure(['id', 'note', 'customer_id', 'created_at', 'updated_at'])
        ->assertJsonPath('id', 1)
        ->assertJsonPath('note', 'Follow up next week.')
        ->assertJsonPath('customer_id', $customer->id);

    $this->assertDatabaseHas('notes', [
        'id' => 1,
        'note' => 'Follow up next week.',
        'customer_id' => $customer->id,
    ]);
});

test('requires a note and an existing customer when creating a note', function (array $data, string $field) {
    $this->postJson('/api/notes', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    $this->assertDatabaseCount('notes', 0);
})->with([
    'missing note' => [
        ['customer_id' => 1],
        'note',
    ],
    'empty note' => [
        ['note' => '', 'customer_id' => 1],
        'note',
    ],
    'non-string note' => [
        ['note' => 123, 'customer_id' => 1],
        'note',
    ],
    'missing customer' => [
        ['note' => 'Follow up next week.'],
        'customer_id',
    ],
    'unknown customer' => [
        ['note' => 'Follow up next week.', 'customer_id' => 999],
        'customer_id',
    ],
]);

test('shows a note', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $note = Note::query()->create([
        'note' => 'Follow up next week.',
        'customer_id' => $customer->id,
    ]);

    $this->getJson("/api/notes/{$note->id}")
        ->assertOk()
        ->assertJsonPath('id', $note->id)
        ->assertJsonPath('note', 'Follow up next week.')
        ->assertJsonPath('customer_id', $customer->id);
});

test('updates a note', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $note = Note::query()->create([
        'note' => 'Follow up next week.',
        'customer_id' => $customer->id,
    ]);

    $this->putJson("/api/notes/{$note->id}", [
        'note' => 'Meeting scheduled.',
        'customer_id' => $customer->id,
    ])
        ->assertOk()
        ->assertJsonPath('note', 'Meeting scheduled.')
        ->assertJsonPath('customer_id', $customer->id);

    $this->assertDatabaseHas('notes', [
        'id' => $note->id,
        'note' => 'Meeting scheduled.',
        'customer_id' => $customer->id,
    ]);
});

test('returns not found for a note that does not exist', function () {
    $this->getJson('/api/notes/999')
        ->assertNotFound();
});

test('deletes a note', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $note = Note::query()->create([
        'note' => 'Follow up next week.',
        'customer_id' => $customer->id,
    ]);

    $this->deleteJson("/api/notes/{$note->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('notes', ['id' => $note->id]);
});

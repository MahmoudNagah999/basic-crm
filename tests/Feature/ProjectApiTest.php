<?php

use App\Models\Customer;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lists projects with their fields and timestamps', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $project = Project::query()->create([
        'project_name' => 'CRM',
        'status' => true,
        'customer_id' => $customer->id,
        'project_cost' => '1250.50',
    ]);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonStructure([
            ['id', 'project_name', 'status', 'customer_id', 'project_cost', 'created_at', 'updated_at'],
        ])
        ->assertJsonPath('0.id', $project->id)
        ->assertJsonPath('0.project_name', 'CRM')
        ->assertJsonPath('0.status', true)
        ->assertJsonPath('0.customer_id', $customer->id)
        ->assertJsonPath('0.project_cost', '1250.50');
});

test('creates a project for an existing customer', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);

    $this->postJson('/api/projects', [
        'project_name' => 'CRM',
        'status' => true,
        'customer_id' => $customer->id,
        'project_cost' => '1250.50',
    ])
        ->assertCreated()
        ->assertJsonStructure(['id', 'project_name', 'status', 'customer_id', 'project_cost', 'created_at', 'updated_at'])
        ->assertJsonPath('id', 1)
        ->assertJsonPath('project_name', 'CRM')
        ->assertJsonPath('status', true)
        ->assertJsonPath('customer_id', $customer->id)
        ->assertJsonPath('project_cost', '1250.50');

    $this->assertDatabaseHas('projects', [
        'id' => 1,
        'project_name' => 'CRM',
        'status' => true,
        'customer_id' => $customer->id,
        'project_cost' => '1250.50',
    ]);
});

test('requires valid project data when creating a project', function (array $data, string $field) {
    $this->postJson('/api/projects', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    $this->assertDatabaseCount('projects', 0);
})->with([
    'missing project name' => [
        ['status' => true, 'customer_id' => 1, 'project_cost' => '1250.50'],
        'project_name',
    ],
    'invalid status' => [
        ['project_name' => 'CRM', 'status' => 'active', 'customer_id' => 1, 'project_cost' => '1250.50'],
        'status',
    ],
    'missing customer' => [
        ['project_name' => 'CRM', 'status' => true, 'project_cost' => '1250.50'],
        'customer_id',
    ],
    'unknown customer' => [
        ['project_name' => 'CRM', 'status' => true, 'customer_id' => 999, 'project_cost' => '1250.50'],
        'customer_id',
    ],
    'negative cost' => [
        ['project_name' => 'CRM', 'status' => true, 'customer_id' => 1, 'project_cost' => '-1.00'],
        'project_cost',
    ],
    'cost with more than two decimals' => [
        ['project_name' => 'CRM', 'status' => true, 'customer_id' => 1, 'project_cost' => '1.234'],
        'project_cost',
    ],
]);

test('shows a project', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $project = Project::query()->create([
        'project_name' => 'CRM',
        'status' => true,
        'customer_id' => $customer->id,
        'project_cost' => '1250.50',
    ]);

    $this->getJson("/api/projects/{$project->id}")
        ->assertOk()
        ->assertJsonPath('id', $project->id)
        ->assertJsonPath('project_name', 'CRM')
        ->assertJsonPath('status', true)
        ->assertJsonPath('customer_id', $customer->id)
        ->assertJsonPath('project_cost', '1250.50');
});

test('updates a project', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $project = Project::query()->create([
        'project_name' => 'CRM',
        'status' => true,
        'customer_id' => $customer->id,
        'project_cost' => '1250.50',
    ]);

    $this->putJson("/api/projects/{$project->id}", [
        'project_name' => 'Customer Portal',
        'status' => false,
        'customer_id' => $customer->id,
        'project_cost' => '2000.00',
    ])
        ->assertOk()
        ->assertJsonPath('project_name', 'Customer Portal')
        ->assertJsonPath('status', false)
        ->assertJsonPath('project_cost', '2000.00');

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'project_name' => 'Customer Portal',
        'status' => false,
        'customer_id' => $customer->id,
        'project_cost' => '2000.00',
    ]);
});

test('returns not found for a project that does not exist', function () {
    $this->getJson('/api/projects/999')
        ->assertNotFound();
});

test('deletes a project', function () {
    $customer = Customer::query()->create(['name' => 'Ada Lovelace']);
    $project = Project::query()->create([
        'project_name' => 'CRM',
        'status' => true,
        'customer_id' => $customer->id,
        'project_cost' => '1250.50',
    ]);

    $this->deleteJson("/api/projects/{$project->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});

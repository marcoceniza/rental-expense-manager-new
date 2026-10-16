<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Models\Activity;

// ── Activity Logs ─────────────────────────────────────────────────────────────

test('logs return a paginated Inertia prop with a data array', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    $this->actingAs($admin)
        ->get('/admin/logs?per_page=3')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $page->component('Logs')
                ->has('logs.data')
                ->where('logs.per_page', 3)
                ->has('logs.total')
                ->has('logs.current_page')
                ->has('logs.last_page');
        });
});

test('logs response contains pagination metadata', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    activity()
        ->causedBy($admin)
        ->event('test_event')
        ->log('Test activity');

    $this->actingAs($admin)
        ->get('/admin/logs?per_page=3')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $page->component('Logs')
                ->where('logs.total', 1)
                ->where('logs.per_page', 3)
                ->where('logs.current_page', 1)
                ->where('logs.last_page', 1)
                ->has('logs.data', 1);
        });
});

test('logs paginate to the requested page with a stable newest-first order', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);
    $createdIds = [];

    foreach (range(1, 5) as $number) {
        activity()
            ->causedBy($admin)
            ->event('test_event')
            ->log("Test activity {$number}");

        $createdIds[] = Activity::query()->latest('id')->value('id');
    }

    $expectedPageIds = array_slice(array_reverse($createdIds), 2, 2);

    $this->actingAs($admin)
        ->get('/admin/logs?per_page=2&page=2')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($expectedPageIds) {
            $page->component('Logs')
                ->where('logs.current_page', 2)
                ->where('logs.last_page', 3)
                ->where('logs.per_page', 2)
                ->where('logs.total', 5)
                ->where('logs.data.0.id', $expectedPageIds[0])
                ->where('logs.data.1.id', $expectedPageIds[1]);
        });
});

test('log search paginates over all matching records rather than only the loaded page', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    foreach (range(1, 5) as $number) {
        activity()
            ->causedBy($admin)
            ->event('test_event')
            ->log($number % 2 === 0 ? "findme result {$number}" : "Unrelated activity {$number}");
    }

    $this->actingAs($admin)
        ->get('/admin/logs?search=findme&per_page=1&page=2')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) {
            $page->component('Logs')
                ->where('logs.total', 2)
                ->where('logs.per_page', 1)
                ->where('logs.current_page', 2)
                ->where('logs.last_page', 2)
                ->where('logs.data.0.description', 'findme result 2');
        });
});

test('each log item in data has the fields expected by the page', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);

    activity()
        ->causedBy($admin)
        ->event('test_event')
        ->log('Test activity');

    $this->actingAs($admin)
        ->get('/admin/logs?per_page=3')
        ->assertOk()
        ->assertInertia(function (AssertableInertia $page) use ($admin) {
            $page->component('Logs')
                ->has('logs.data', 1)
                ->has('logs.data.0.id')
                ->where('logs.data.0.user_id', $admin->id)
                ->where('logs.data.0.user_name', $admin->name)
                ->where('logs.data.0.user_email', $admin->email)
                ->where('logs.data.0.action', 'test_event')
                ->where('logs.data.0.description', 'Test activity')
                ->has('logs.data.0.ip_address')
                ->has('logs.data.0.created_at');
        });
});

test('transaction activity captures the request IP address and user agent', function () {
    $admin = User::factory()->create(['user_type' => 'admin']);
    $category = Category::factory()->create();
    $ipAddress = '203.0.113.42';
    $userAgent = 'Rental-Expense-Test/1.0';

    $this->withServerVariables(['REMOTE_ADDR' => $ipAddress])
        ->withHeaders(['User-Agent' => $userAgent])
        ->actingAs($admin)
        ->post(route('admin.transactions.store'), [
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 125.50,
            'transaction_date' => '2026-10-16',
            'description' => 'Request metadata test',
        ])
        ->assertRedirect();

    $activity = Activity::query()
        ->where('subject_type', Transaction::class)
        ->where('event', 'created')
        ->latest('id')
        ->firstOrFail();

    expect($activity->properties['ip_address'])->toBe($ipAddress)
        ->and($activity->properties['user_agent'])->toBe($userAgent);
});

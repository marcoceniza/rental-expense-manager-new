<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class LogsController extends Controller
{
    /**
     * Display a listing of logs
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        // Filter by action (activity event)
        if ($request->filled('action')) {
            $query->where('event', $request->action);
        }

        // Filter by user (causer)
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = '%'.trim($request->input('search')).'%';

            $query->where(function (Builder $query) use ($search) {
                $query->where('event', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhereHasMorph('causer', [User::class], function (Builder $query) use ($search) {
                        $query->where('name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $activities = $query->paginate($perPage);

        // Map Spatie activity records to the frontend Logs contract while
        // preserving the paginator structure (so `logs.data` remains an
        // array of records instead of losing the pagination wrapper).
        $logs = $activities->through(function (Activity $activity) {
            $causer = $activity->causer;

            return [
                'id' => $activity->id,
                'user_id' => $causer?->id,
                'user_name' => $causer?->name ?? 'System',
                'user_email' => $causer?->email,
                'action' => $activity->event,
                'description' => $activity->description,
                'loggable_type' => $activity->subject_type,
                'loggable_id' => $activity->subject_id,
                'ip_address' => $activity->properties['ip_address'] ?? null,
                'user_agent' => $activity->properties['user_agent'] ?? null,
                'payload' => $this->buildPayload($activity),
                'created_at' => $activity->created_at,
            ];
        });

        return Inertia::render('Logs', [
            'logs' => $logs,
            'filters' => [
                'actions' => Activity::whereNotNull('event')
                    ->distinct()
                    ->pluck('event')
                    ->toArray(),
                'users' => User::select('id', 'name', 'email')
                    ->get()
                    ->toArray(),
            ],
        ]);
    }

    /**
     * Assemble the human-readable payload from the activity's stored changes.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(Activity $activity): array
    {
        $changes = $activity->properties['changes'] ?? [];

        // Page-level events (login/logout/register) carry no changes; surface
        // the request details that the middleware stored in properties.
        if (! is_array($changes) || empty($changes)) {
            return [
                'ip_address' => $activity->properties['ip_address'] ?? null,
                'user_agent' => $activity->properties['user_agent'] ?? null,
            ];
        }

        return $changes;
    }
}

<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transactions\StoreRecurringRequest;
use App\Models\Recurring;

class RecurringController extends Controller
{
    /**
     * Store recurring transaction
     */
    public function store(StoreRecurringRequest $request)
    {
        Recurring::create($request->validated());

        return redirect()->route('recurring.index')
            ->with('success', 'Recurring created successfully.');
    }

    /**
     * Update recurring transaction
     */
    public function update(StoreRecurringRequest $request, Recurring $recurring)
    {
        $recurring->update($request->validated());

        return redirect()->route('recurring.index')
            ->with('success', 'Recurring updated successfully.');
    }

    /**
     * Delete recurring transaction
     */
    public function destroy(Recurring $recurring)
    {
        $recurring->delete();

        return redirect()->route('recurring.index')
            ->with('success', 'Recurring deleted successfully.');
    }
}

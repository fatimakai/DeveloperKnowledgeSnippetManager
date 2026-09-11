<?php

namespace App\Http\Controllers;

use App\Models\PromptCollection;
use App\Services\CollectionAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CollectionAuditLogController extends Controller
{
    public function index(Request $request, PromptCollection $collection, CollectionAuditLogger $audit): View
    {
        Gate::authorize('viewAudit', $collection);
        $audit->recordOnce($collection, $request->user(), 'audit.viewed');

        $logs = $collection->auditLogs()
            ->with(['actor', 'targetUser', 'prompt'])
            ->latest('created_at')
            ->paginate(30);

        return view('collections.audit', compact('collection', 'logs'));
    }
}

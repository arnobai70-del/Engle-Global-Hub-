<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityRecorder;
use App\Services\Integrations\ConnectionTester;
use App\Services\Integrations\IntegrationRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(IntegrationRegistry $integrations, ConnectionTester $tester): View
    {
        return view('admin.integrations.index', [
            'integrations' => collect($integrations->all())->groupBy('group'),
            'labels' => IntegrationRegistry::STATUS_LABELS,
            'testable' => $tester->testable(),
        ]);
    }

    public function test(Request $request, string $integration, ConnectionTester $tester, ActivityRecorder $activity): RedirectResponse
    {
        abort_unless(in_array($integration, $tester->testable(), true), 404);

        $result = $tester->test($integration);
        $activity->record($request->user(), 'integration.tested', null, ['integration' => $integration, 'ok' => $result['ok']]);

        return back()->with($result['ok'] ? 'status' : 'test_error', $result['message']);
    }
}

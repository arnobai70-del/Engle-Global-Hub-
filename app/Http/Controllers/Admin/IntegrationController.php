<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityRecorder;
use App\Services\Integrations\ConnectionTester;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Integrations\ProviderStatus;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(IntegrationRegistry $integrations, ConnectionTester $tester, ProviderStatus $status): View
    {
        return view('admin.integrations.index', [
            'integrations' => collect($integrations->all())->groupBy('group'),
            'labels' => IntegrationRegistry::STATUS_LABELS,
            'testable' => $tester->testable(),
            'publicStatuses' => collect(ProviderStatus::CAPABILITIES)->map(fn (array $capability, string $key): array => [
                'label' => $capability['label'],
                'state' => $state = $status->of($key),
                'state_label' => $status->label($state),
            ])->all(),
        ]);
    }

    public function test(Request $request, string $integration, ConnectionTester $tester, ActivityRecorder $activity, ProviderStatus $status, SettingService $settings): RedirectResponse
    {
        abort_unless(in_array($integration, $tester->testable(), true), 404);

        $result = $tester->test($integration);
        $activity->record($request->user(), 'integration.tested', null, ['integration' => $integration, 'ok' => $result['ok']]);

        // Only a passing test for the current configuration may make the public status "Live".
        $result['ok']
            ? $status->recordVerified($integration)
            : $settings->set('integrations', $integration.'_verified', '');

        return back()->with($result['ok'] ? 'status' : 'test_error', $result['message']);
    }
}

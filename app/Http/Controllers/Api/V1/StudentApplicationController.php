<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\StudentApplicationResource;
use App\Models\StudentApplication;
use App\Services\Consultancy\StudentPortal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudentApplicationController extends Controller
{
    public function __construct(private StudentPortal $portal) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $student = $this->portal->studentFor($request->user());

        return StudentApplicationResource::collection($student === null
            ? collect()
            : $student->applications()->with(['destinationCountry:id,name', 'institution:id,name'])->latest('updated_at')->get());
    }

    public function show(Request $request, StudentApplication $application): StudentApplicationResource
    {
        abort_unless($this->portal->owns($request->user(), $application), 404);

        return new StudentApplicationResource($application->load(['destinationCountry:id,name', 'institution:id,name', 'statusChanges', 'documents']));
    }
}

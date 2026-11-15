<?php

namespace App\Policies;

use App\Models\StudentApplication;
use App\Models\User;

/**
 * Staff see an application only when they may see all applications, or when
 * it is assigned to them. This keeps consultants and visa staff out of other
 * students' confidential files.
 */
class StudentApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('student-applications.view');
    }

    public function view(User $user, StudentApplication $application): bool
    {
        return $user->can('student-applications.view') && $this->canReach($user, $application);
    }

    public function update(User $user, StudentApplication $application): bool
    {
        return $user->can('student-applications.manage') && $this->canReach($user, $application);
    }

    public function verifyDocuments(User $user, StudentApplication $application): bool
    {
        return $user->can('student-documents.verify') && $this->canReach($user, $application);
    }

    private function canReach(User $user, StudentApplication $application): bool
    {
        return $user->can('student-applications.view-all')
            || (int) $application->assigned_consultant_id === (int) $user->getKey();
    }
}

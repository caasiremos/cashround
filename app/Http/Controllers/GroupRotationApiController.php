<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiSuccessResponse;
use App\Models\Group;
use App\Services\GroupRotationService;
use Illuminate\Http\Request;

class GroupRotationApiController extends Controller
{
    public function __construct(
        private GroupRotationService $groupRotationService,
    ) {
    }

    /**
     * Get rotation state for a group: current recipient, next recipient, completed circles.
     */
    public function getRotation(Group $group): ApiSuccessResponse
    {
        $state = $this->groupRotationService->getRotationState($group);

        $current = $state['current_member'];
        $next = $state['next_member'];

        $data = [
            'current_member' => $current ? [
                'id' => $current->id,
                'first_name' => $current->first_name,
                'last_name' => $current->last_name,
                'email' => $current->email,
            ] : null,
            'next_member' => $next ? [
                'id' => $next->id,
                'first_name' => $next->first_name,
                'last_name' => $next->last_name,
                'email' => $next->email,
            ] : null,
            'completed_circles' => $state['completed_circles'],
            'circle_complete' => $state['completed_circles'] > 0,
            'members_in_order' => $state['members_in_order']->map(fn ($m) => [
                'id' => $m->id,
                'first_name' => $m->first_name,
                'last_name' => $m->last_name,
                'email' => $m->email,
                'rotation_position' => (int) $m->pivot->rotation_position,
            ])->values()->all(),
        ];

        return new ApiSuccessResponse($data, 'Rotation state fetched successfully');
    }
}

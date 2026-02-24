<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiSuccessResponse;
use App\Models\Group;
use App\Repositories\WalletTransactionRepository;
use App\Services\GroupRotationService;
use Illuminate\Http\Request;

class GroupRotationApiController extends Controller
{
    public function __construct(
        private GroupRotationService $groupRotationService,
        private WalletTransactionRepository $walletTransactionRepository,
    ) {
    }

    /**
     * Get rotation state for a group: current recipient, next recipient, completed circles, and scheduled cashround dates.
     */
    public function getRotation(Group $group): ApiSuccessResponse
    {
        $data = $this->buildRotationData($group);

        return new ApiSuccessResponse($data, 'Rotation state fetched successfully');
    }

    /**
     * Set rotation order from client payload.
     * Body: [ {"member_id": 1, "rotation_position": 1}, {"member_id": 2, "rotation_position": 2}, ... ]
     * Or: { "members": [ ... ] }
     * Must include every group member exactly once.
     */
    public function updateRotationOrder(Request $request, Group $group): ApiSuccessResponse
    {
        $order = $request->input('members', $request->all());
        if (! is_array($order)) {
            $order = [];
        }

        $normalized = array_map(function ($item) {
            $item = is_array($item) ? $item : (array) $item;
            return [
                'member_id' => (int) ($item['member_id'] ?? $item['memberId'] ?? 0),
                'rotation_position' => (int) ($item['rotation_position'] ?? $item['rotationPosition'] ?? 0),
            ];
        }, $order);

        $validated = validator($normalized, [
            '*.member_id' => ['required', 'integer', 'min:1'],
            '*.rotation_position' => ['required', 'integer', 'min:0'],
        ])->validate();

        $this->groupRotationService->updateRotationOrder($group, array_values($validated));
        $group->refresh();

        return new ApiSuccessResponse(
            $this->buildRotationData($group),
            'Rotation order updated successfully'
        );
    }

    /**
     * Reschedule the current recipient to the end of the round (use when their cashround date has passed without payment).
     */
    public function rescheduleCurrentRecipient(Group $group): ApiSuccessResponse
    {
        if (! $this->groupRotationService->hasCurrentRecipientDatePassed($group)) {
            return new ApiSuccessResponse(
                $this->buildRotationData($group),
                'Current recipient date has not passed; no reschedule performed'
            );
        }

        $this->groupRotationService->rescheduleCurrentRecipientToEndOfRound($group);
        $group->refresh();

        return new ApiSuccessResponse(
            $this->buildRotationData($group),
            'Current recipient rescheduled to end of round; rotation advanced'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildRotationData(Group $group): array
    {
        $state = $this->groupRotationService->getRotationStateWithDates($group);
        $current = $state['current_member'];
        $next = $state['next_member'];

        return [
            'current_member' => $current ? [
                'id' => $current->id,
                'first_name' => $current->first_name,
                'last_name' => $current->last_name,
                'email' => $current->email,
                'scheduled_cashround_date' => $state['current_scheduled_date'] ?? null,
            ] : null,
            'next_member' => $next ? [
                'id' => $next->id,
                'first_name' => $next->first_name,
                'last_name' => $next->last_name,
                'email' => $next->email,
                'scheduled_cashround_date' => $state['next_scheduled_date'] ?? null,
            ] : null,
            'completed_circles' => $state['completed_circles'],
            'circle_complete' => $state['completed_circles'] > 0,
            'current_recipient_date_passed' => $this->groupRotationService->hasCurrentRecipientDatePassed($group),
            'members_in_order' => array_map(function ($entry) use ($group) {
                $m = $entry['member'];
                return [
                    'id' => $m->id,
                    'first_name' => $m->first_name,
                    'last_name' => $m->last_name,
                    'email' => $m->email,
                    'rotation_position' => (int) $m->pivot->rotation_position,
                    'scheduled_cashround_date' => $entry['scheduled_cashround_date'],
                    'has_contributed_in_current_rotation' => $this->walletTransactionRepository->hasMemberContributedInCurrentRotation($group, (int) $m->id),
                ];
            }, $state['members_with_dates']),
        ];
    }
}

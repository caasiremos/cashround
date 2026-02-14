<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Models\Group;
use App\Models\Member;
use App\Repositories\MemberRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MemberService
{
    public function __construct(
        protected MemberRepository $memberRepository,
    ) {
    }
    /**
     * Create a new member
     *
     * @param array $data
     * @return Member
     */
    public function createMember(array $data)
    {
        return $this->memberRepository->createMember($data);
    }

    /**
     * Confirm a verification code
     *
     * @param Request $request
     * @return Member
     */
    public function confirmVerificationCode(Request $request): Member
    {
        if(!$request->has('code')) {
            throw new ExpectedException('Verification code is required');
        }
        return $this->memberRepository->confirmVerificationCode($request);
    }

    /**
     * Get all members of a group
     *
     * @param Group $group
     * @return Collection
     */
    public function getGroupMembers(Group $group): Collection
    {
        return $this->memberRepository->getGroupMembers($group);
    }

    /**
     * Get a member by id
     *
     * @param int $id
     * @return Member
     */
    public function getMemberById(int $id): ?Member
    {
        return $this->memberRepository->getMemberById($id);
    }
}
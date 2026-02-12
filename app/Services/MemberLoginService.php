<?php

namespace App\Services;

use App\Exceptions\ExpectedException;
use App\Models\Member;
use App\Repositories\MemberRepository;
use Illuminate\Support\Facades\Hash;

class MemberLoginService
{
    public function __construct(
        protected MemberRepository $memberRepository,
    ) {
    }

    /**
     * Authenticate member and return login data (token + member info).
     *
     * @throws ExpectedException
     */
    public function login(string $email, string $password): array
    {
        $member = $this->memberRepository->findByEmail($email);

        if (!$member || !Hash::check($password, $member->password)) {
            throw new ExpectedException('Invalid credentials');
        }

        // if (!$member->hasVerifiedEmail()) {
        //     throw new ExpectedException('Please verify your email address before logging in.');
        // }

        $token = $member->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'member' => $member->only('id', 'first_name', 'last_name', 'email', 'phone_number'),
        ];
    }

    /**
     * Revoke the current access token for the authenticated member.
     */
    public function logout(Member $member): void
    {
        $member->currentAccessToken()->delete();
    }
}

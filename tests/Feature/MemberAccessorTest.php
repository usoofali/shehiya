<?php

use App\Models\Member;

test('member accessors return count directly from attributes without recursion', function () {
    $member = new Member;
    $member->setRawAttributes([
        'referrals_count' => 15,
        'verified_referrals_count' => 10,
    ]);

    expect($member->referrals_count)->toBe(15)
        ->and($member->verified_referrals_count)->toBe(10)
        ->and($member->referral_badge)->toBe('None');
});

test('member model serializes to array without recursion or memory exhaustion', function () {
    $member = new Member;
    $member->setRawAttributes([
        'referrals_count' => 5,
        'verified_referrals_count' => 2,
    ]);

    $array = $member->toArray();

    expect($array)->toHaveKey('referrals_count', 5)
        ->and($array)->toHaveKey('verified_referrals_count', 2)
        ->and($array)->toHaveKey('referral_badge', 'None');
});

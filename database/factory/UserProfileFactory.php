<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\UserProfile;
use Faker\Generator as Faker;

$factory->define(UserProfile::class, function (Faker $faker) {
    static $userId;

    return [
        'age'                    => 0,
        'user_id'                => $userId,
        'source'                 => "unknown",
        'introduction'           => '长得帅有错吗',
        'transaction_sum_amount' => 0,
        'visited_count'          => 0,
        'questions_count'        => 0,
        'curations_count'        => 0,
        'reports_count'          => 0,
        'reports_correct_count'  => 0,
        'answers_count'          => 0,
        'correct_count'          => 0,
        'total_contributes'      => 0,
        'question_pass_rate'     => 0,
        'curation_pass_rate'     => 0,
        'answer_correct_rate'    => 0,
        'question_curation_rate' => 0,
        'answers_count_today'    => 0,
        'version'                => 'unknow',
        'package'                => 'unknow',
        'password_change_count'  => 0,
        'keep_signin_days'       => 0,
        //'total_bonus_earnings'   => 0.00,
        //'followers_count'        => 0,
        'create_question_answer_correct_rate' => 0

    ];
});

<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker $faker) {
    static $password;

    return [
        'user_type_id' => rand(1, 5),
        // 'lab_id' => rand(1, 5),
        'lab_id' => 3,
        'surname' => $faker->lastName,
        'oname' => $faker->firstName,
        'email' => $faker->unique()->safeEmail,
        // 'password' => $password ?: $password = bcrypt('secret'),
        'password' => 'password',
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Batch::class, function (Faker $faker) {
    return [
        'received_by' => 1,
        'user_id' => 1,

        'input_complete' => 1,
        'lab_id' => rand(1, 20),
        'facility_id' => rand(1, 1000),
        'datereceived' => $faker->dateTimeThisYear($max = 'now'),
    ];
});

$factory->define(App\Sample::class, function (Faker $faker) {
    return [
        'patient_id' => rand(1, 100),
        'batch_id' => rand(1, 20),
        'receivedstatus' => 1,
        'age' => rand(1, 12),
        'pcrtype' => rand(1, 3),
        'regimen' => rand(1, 10),
        'mother_prophylaxis' => rand(1, 5),
        'feeding' => rand(1, 5),
        'spots' => rand(1, 5),
        'datecollected' => $faker->dateTimeThisYear($max = 'now'),
    ];
});

$factory->define(App\Patient::class, function (Faker $faker) {
    return [
        'patient' => $faker->bothify('#####-?????'),
        'mother_id' => rand(1, 100),
        'facility_id' => rand(1, 1000),
        'dob' => $faker->dateTimeBetween($startDate = '-2 years', $endDate = 'now'),
        'sex' => rand(1, 2),
    ];
});

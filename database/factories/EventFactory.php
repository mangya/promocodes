<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Event;
use Faker\Generator as Faker;

$factory->define(Event::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
        'event_code' => $faker->unique()->regexify('[A-Z0-9]{8}'),
        'longitude' => $faker->latitude($min = 13, $max = 4) ,
        'latitude' => $faker->longitude($min = 3, $max = 14),
        'start_date' => $faker->dateTimeBetween($startDate = '+5 days', $endDate = '+100 days', $timezone = null),
        'end_date' => $faker->dateTimeBetween($startDate = '+5 days', $endDate = '+100 days', $timezone = null),
        'is_active' => array_rand([0,1]),
    ];
});

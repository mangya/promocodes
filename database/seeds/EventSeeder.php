<?php
use App\Event;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;


class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Event::class, 5)->create();
    }
}

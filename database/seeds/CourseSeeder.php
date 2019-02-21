<?php

use App\Components\Course\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // generate random courses
        $courses = factory(Course::class,30)->create();
    }
}

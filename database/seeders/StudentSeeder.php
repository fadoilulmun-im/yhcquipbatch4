<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Faker\Factory as Faker;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('id_ID');
        $class = ['Regular', 'International'];
        DB::beginTransaction();
        try {
            foreach (range(0, 10) as $value) {
                Student::create([
                    'name' => $faker->name,
                    'major_id' => rand(1,4),
                    'class' => $class[rand(0,1)],
                    'batch_year' => rand(2017, 2022),
                ]);
            }

            $lastId = DB::table('students')->orderBy('id', 'desc')->first();
            if(!empty($lastId))
            {
                $newLastId = $lastId->id + 1;
                DB::update(DB::raw("ALTER SEQUENCE students_id_seq RESTART WITH {$newLastId}"));
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            echo $ex->getMessage();
        }
    }
}

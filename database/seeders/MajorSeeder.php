<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            $data = [
                [
                    'id' => 1,
                    'name' => 'Informatics Engineering',
                ],
                [
                    'id' => 2,
                    'name' => 'Electrical Engineering',
                ],
                [
                    'id' => 3,
                    'name' => 'Educational Administration',
                ],
                [
                    'id' => 4,
                    'name' => 'Agrotechnology',
                ],
            ];

            foreach ($data as $key => $value) {
                DB::table('majors')->updateOrInsert(['id' => $value['id']], [
                    'name' => $value['name'],
                ]);
            }

            $lastId = DB::table('majors')->orderBy('id', 'desc')->first();
            if(!empty($lastId))
            {
                $newLastId = $lastId->id + 1;
                DB::update(DB::raw("ALTER SEQUENCE majors_id_seq RESTART WITH {$newLastId}"));
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            echo $ex->getMessage();
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Position, Employee};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * @return void
     */
    public function run()
    {
        /* Создаем 10 администраторов */
        User::factory(10)->create();

        // Список вакансий
        $positions = [
            'Leading specialist of the Control Department',
            'Contextual advertising specialist',
            'Lead designer',
            'Frontend developer',
            'Backend developer',
        ];

        /* Записываем вакансии в базу */
        array_map(function ($position) {

            $adminCreatedId = User::inRandomOrder()->first()->id;

            Position::create([
                'name' => $position,
                'admin_created_id' => $adminCreatedId,
                'admin_updated_id' => $adminCreatedId
            ]);
        }, $positions);

        /* Создаем 50 000 сотрудников */
        $employeesIds = Employee::factory(50000)->create()->pluck('id')->toArray();

        foreach (Employee::all() as $employee) {

            // Руководитель
            $headEmployeeId = 0;
            while (true) {
                $headEmployeeId = $employeesIds[array_rand($employeesIds)];
                if ($headEmployeeId !== $employee->id) {
                    break;
                }
            }
            $employee->head_employee_id = $headEmployeeId;
            $employee->save();
        }
    }
}

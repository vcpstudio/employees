<?php

namespace Database\Factories;

use App\Models\{Employee, Position, User};
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Provider\PhoneNumber;
use Faker\Generator;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     * @return array
     */
    public function definition()
    {
        $phone = PhoneNumber::numerify(
            (new Generator())->parse(
                PhoneNumber::randomElement(
                    [
                        '+38 (050) ### ## ##',
                        '+38 (066) ### ## ##',
                        '+38 (068) ### ## ##',
                        '+38 (096) ### ## ##',
                        '+38 (067) ### ## ##',
                        '+38 (091) ### ## ##',
                        '+38 (092) ### ## ##',
                        '+38 (093) ### ## ##',
                        '+38 (094) ### ## ##',
                        '+38 (095) ### ## ##',
                        '+38 (096) ### ## ##',
                        '+38 (097) ### ## ##',
                        '+38 (098) ### ## ##',
                        '+38 (063) ### ## ##',
                        '+38 (099) ### ## ##',
                    ]
                )
            )
        );

        $employmentAt = function () {
            return date('Y-m-d', mt_rand(strtotime('-1 year'), strtotime('now')));
        };

        $adminCreatedId = User::inRandomOrder()->first()->id;

        return [
            'fullname' => $this->faker->name,
            'position_id' => Position::inRandomOrder()->first(),
            'employment_at' => $employmentAt,
            'phone' => $phone,
            'email' => $this->faker->email,
            'head_employee_id' => Employee::inRandomOrder()->first(),
            'salary' => (mt_rand(floor(100 / 50), floor(500 / 50)) * 50),
            'admin_created_id' => $adminCreatedId,
            'admin_updated_id' => $adminCreatedId
        ];
    }
}

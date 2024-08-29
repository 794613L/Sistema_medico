<?php

namespace Database\Factories\Patient;

use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PatientFactory extends Factory
{
    // Define el modelo que esta fábrica está asociada
    protected $model = Patient::class;
    /**
     * Define the model's default state.
     * Este método define el estado predeterminado de los atributos del modelo Patient.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
             // Genera un nombre aleatorio para el paciente
            "name" => $this->faker->name(),
            "surname" => $this->faker->lastName(),
            "mobile" => $this->faker->phoneNumber(),
            "email" => $this->faker->email(),
            "birth_date" => $this->faker->dateTimeBetween("1985-10-01 00:00:00", "2000-10-25 23:59:59"),
            "gender" => $this->faker->randomElement([1, 2]),
            "education" => $this->faker->word(),
            "address" => $this->faker->word(),
            "antecedent_family" => $this->faker->text($maxNbChars = 300),
            "antecedent_personal" => $this->faker->text($maxNbChars = 200),
            "antecedent_allergic" => $this->faker->text($maxNbChars = 150),
            "current_disease" => $this->faker->text($maxNbChars = 100),
            "n_document" => $this->faker->randomDigit(),
            "created_at" => $this->faker->dateTimeBetween("2024-01-01 00:00:00", "2024-12-25 23:59:59"),
        ];
    }
}

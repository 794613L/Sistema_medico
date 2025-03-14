<?php

namespace Database\Seeders;

use App\Models\Patient\Patient;
use Illuminate\Database\Seeder;
use App\Models\Patient\PatientPerson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Genera 100 registros de pacientes utilizando la factory de Patient
        Patient::factory()->count(100)->create()->each(function($p) {
            $faker = \Faker\Factory::create();
                        
            // Crea un registro en la tabla PatientPerson relacionado con el paciente creado
            PatientPerson::create([ 
                "patient_id" => $p->id,
                "name_companion" => $faker->name(),
                "surname_companion" => $faker->lastName(),
                "mobile_companion" => $faker->phoneNumber(),
                "relationship_companion" => $faker->randomElement(["Tio","Mama","Papa","Hermano"]),
                "name_responsible" => $faker->name(),
                "surname_responsible" => $faker->lastName(),
                "mobile_responsible" => $faker->phoneNumber(),
                "relationship_responsible" => $faker->randomElement(["Tio","Mama","Papa","Hermano"]),
            ]);
        });;
         // Comando para ejecutar este seeder: php artisan db:seed --class=PatientSeeder
    }
}

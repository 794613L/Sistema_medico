<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPay;
use App\Models\Appointment\AppointmentAttention;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este método se encarga de poblar la base de datos con datos de ejemplo para las citas, sus pagos y atenciones.

     */
    public function run(): void
    {
        // Genera 1000 registros de citas utilizando la factory de Appointment
        Appointment::factory()->count(1000)->create()->each(function($p) {
             // Crea una instancia de Faker para generar datos aleatorios
            $faker = \Faker\Factory::create();
            
            // Si el estado de la cita es 2, genera un registro de atención para la cita
            if($p->status == 2){
                AppointmentAttention::create([ 
                    "appointment_id" => $p->id,
                    "patient_id" => $p->patient_id,
                    "description" => $faker->text($maxNbChars = 300),
                    "recipe_medica" =>  json_encode([
                        [
                            "name_medical" => $faker->word(),
                            "uso" => $faker->word(),
                        ],
                    ])
                ]);
            }
            
            // Si el estado del pago es 2, genera un pago con un monto fijo de 50
            if($p->status_pay == 2){
                AppointmentPay::create([
                    "appointment_id" => $p->id,
                    "amount" => 50,
                    "method_payment" => $faker->randomElement(["EFECTIVO","TRANSFERENCIA","NEQUI","DAVIPLATA"]),
                ]);
            }else{

                 // Si el estado del pago no es 2, genera un pago con el monto especificado en la cita
                AppointmentPay::create([
                    "appointment_id" => $p->id,
                    "amount" => $p->amount,
                    "method_payment" => $faker->randomElement(["EFECTIVO","TRANSFERENCIA","NEQUI","DAVIPLATA"]),
                ]);
            }
        });
        // Comando para ejecutar este seeder: php artisan db:seed --class=AppointmentSeeder
    }
}

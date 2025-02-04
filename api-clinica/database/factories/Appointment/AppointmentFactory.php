<?php

namespace Database\Factories\Appointment;

use App\Models\User;
use App\Models\Patient\Patient;
use App\Models\Doctor\Specialitie;
use App\Models\Appointment\Appointment;
use App\Models\Doctor\DoctorScheduleDay;
use App\Models\Doctor\DoctorScheduleJoinHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment\Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;
    /**
     * Define the model's default state.
     * Este método define el estado predeterminado de los atributos del modelo Appointment.

     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Selecciona aleatoriamente un usuario con el rol de DOCTOR
        $doctor = User::whereHas("roles",function($q){
            $q->where("name","like","%DOCTOR%");
        })->inRandomOrder()->first();
        
        // Genera una fecha y hora aleatoria para la cita dentro de un rango específico
        $date_appointment = $this->faker->dateTimeBetween("2024-01-01 00:00:00", "2024-12-25 23:59:59");
        
        // Define aleatoriamente el estado de la cita (1 o 2)
        $status = $this->faker->randomElement([1, 2]);
        
        // Obtiene aleatoriamente un día de horario de un doctor específico
        $doctor_schedule_day =  DoctorScheduleDay::where("user_id",$doctor->id)->inRandomOrder()->first();
        
        // Obtiene aleatoriamente una hora de un horario de doctor específico
        $doctor_schedule_join_hour = DoctorScheduleJoinHour::where("doctor_schedule_day_id",$doctor_schedule_day->id)->inRandomOrder()->first();

        return [
                       
            // Asigna el ID del doctor seleccionado aleatoriamente
            "doctor_id" => $doctor->id,
            "patient_id" => Patient::inRandomOrder()->first()->id,
            "date_appointment" => $date_appointment,
            "specialitie_id" => Specialitie::all()->random()->id,
            "doctor_schedule_join_hour_id" => $doctor_schedule_join_hour->id,
            "user_id" => User::all()->random()->id,
            "amount" => $this->faker->randomElement([100,150,200,250,80,120,95,75,160,230,110]),
            "status" => $status,
            "status_pay" => $this->faker->randomElement([1, 2]),
            "date_attention" => $status == 2 ? $this->faker->dateTimeBetween($date_appointment, "2024-12-25 23:59:59") : NULL,
        ];
    }
}

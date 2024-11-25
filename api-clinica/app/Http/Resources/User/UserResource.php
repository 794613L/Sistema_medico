<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Se crea una colección vacía para almacenar los horarios
        $HOUR_SCHEDULES = collect([]);
        // Mapeo de los días de la semana a clases de Bootstrap para mostrar los días con colores
        $days_week = [];
        $days_week["Lunes"] = "table-primary";
        $days_week["Martes"] = "table-secondary";
        $days_week["Miercoles"] = "table-success";
        $days_week["Jueves"] = "table-warning";
        $days_week["Viernes"] = "table-info";

        // Variable para almacenar los días de la semana en los que el usuario tiene horarios asignados
        $days_name = "";
        // Recorre los días de la semana en el horario del usuario
        foreach ($this->resource->schedule_days as $key => $schedule_day) {
             // Agrega el nombre del día al string days_name
            $days_name .= ($schedule_day->day."-");
            // Recorre las horas programadas para cada día
            foreach ($schedule_day->schedules_hours as $schedules_hour) {
                // Por cada hora programada, se almacena un arreglo con la información relacionada al día y la hora
                $HOUR_SCHEDULES->push([
                    "day" => [
                        "day" => $schedule_day->day,// Nombre del día
                        "class" => $days_week[$schedule_day->day],
                    ],
                    "day_name" => $schedule_day->day,
                    "hours_day" => [
                        "hour" => $schedules_hour->doctor_schedule_hour->hour,// Hora exacta
                        "format_hour" => Carbon::parse(date("Y-m-d").' '.$schedules_hour->doctor_schedule_hour->hour.":00:00")->format("h:i A"),
                        "items" => [],
                    ],
                    "hour" => $schedules_hour->doctor_schedule_hour->hour,
                    "grupo" => "all",
                    "item" => [
                        "id" => $schedules_hour->doctor_schedule_hour->id,// ID de la hora programada
                        "hour_start" => $schedules_hour->doctor_schedule_hour->hour_start,// Hora de inicio
                        "hour_end" => $schedules_hour->doctor_schedule_hour->hour_end,// Hora de fin
                        "format_hour_start" => Carbon::parse(date("Y-m-d").' '.$schedules_hour->doctor_schedule_hour->hour_start)->format("h:i A"),// Hora de inicio en formato de 12 horas
                        "format_hour_end" => Carbon::parse(date("Y-m-d").' '.$schedules_hour->doctor_schedule_hour->hour_end)->format("h:i A"),// Hora de fin en formato de 12 horas
                        "hour" => $schedules_hour->doctor_schedule_hour->hour,
                    ],
                ]);
            }
        }
         // Devuelve los datos del usuario con la información transformada
        return [
            "id" => $this->resource->id,
            "name" => $this->resource->name,
            "surname" => $this->resource->surname,
            "full_name" => $this->resource->name . ' '. $this->resource->surname,
            "email" => $this->resource->email,
            "birth_date" => $this->resource->birth_date ? Carbon::parse($this->resource->birth_date)->format("Y/m/d") : NULL,
            "gender" => $this->resource->gender,
            "education" => $this->resource->education,
            "designation" => $this->resource->designation,
            "address" => $this->resource->address,
            "mobile" => $this->resource->mobile,
            "created_at" => $this->resource->created_at->format("Y/m/d"),
            "role" => $this->resource->roles->first(),
            "specialitie_id" => $this->resource->specialitie_id,
            "specialitie" => $this->resource->specialitie ? [
                "id" => $this->resource->specialitie->id,
                "name" => $this->resource->specialitie->name,
            ]: NULL,
            "avatar" => env("APP_URL")."storage/".$this->resource->avatar,
            "schedule_selecteds" => $HOUR_SCHEDULES,
            "days_name" => $days_name,
        ];
    }
}

<?php

namespace App\Models\Appointment;
// Importación de dependencias necesarias para gestionar relaciones, fechas y funcionalidades de la clase.
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Patient\Patient;
use App\Models\Doctor\Specialitie;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor\DoctorScheduleJoinHour;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        "doctor_id",
        "patient_id",
        "date_appointment",
        "specialitie_id",
        "doctor_schedule_join_hour_id",
        "user_id",
        "amount",
        "status_pay",
        "status",
        "date_attention",
    ];
    
    public function setCreatedAtAttribute($value)
    {
        // Establece la zona horaria para Bogotá y asigna la fecha actual como "created_at".
    	date_default_timezone_set('America/Bogota');
        $this->attributes["created_at"]= Carbon::now();
    }

    public function setUpdatedAtAttribute($value)
    {
    	date_default_timezone_set("America/Bogota");
        $this->attributes["updated_at"]= Carbon::now();
    }

    public function doctor() {
         // Define la relación de la cita con un doctor (un doctor puede tener muchas citas).
        return $this->belongsTo(User::class,"doctor_id");
    }

    public function user() {
         // Relación con el usuario que creó la cita.
        return $this->belongsTo(User::class);
    }

    public function patient() {
        // Relación con el paciente al que pertenece la cita.
        return $this->belongsTo(Patient::class);
    }

    public function specialitie() {
        // Relación con la especialidad médica asociada a la cita.
        return $this->belongsTo(Specialitie::class);
    }

    public function doctor_schedule_join_hour() {
        // Relación con el horario del doctor (una cita está vinculada a un horario específico).
        return $this->belongsTo(DoctorScheduleJoinHour::class);
    }

    public function payments() {
        // Una cita puede tener varios pagos asociados.
        return $this->hasMany(AppointmentPay::class);
    }

    public function attention() {
        // Una cita puede tener una atención asociada.
        return $this->hasOne(AppointmentAttention::class);
    }

    public function scopefilterAdvance($query,$specialitie_id,$name_doctor,$date,$user = null){

        if($user){
            if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
                $query->where("doctor_id",$user->id);
                // Filtra las citas para mostrar solo las asociadas al doctor actual.
            }
        } 

        if($specialitie_id){
            $query->where("specialitie_id",$specialitie_id);
            // Filtra por la especialidad médica.
        }

        if($name_doctor){
            $query->whereHas("doctor",function($q) use($name_doctor){
                $q->where("name","like","%".$name_doctor."%")
                ->orWhere("surname","like","%".$name_doctor."%");
                // Busca por nombre o apellido del doctor.
            });
        }

        if($date){
            $query->whereDate("date_appointment",Carbon::parse($date)->format("Y-m-d"));
             // Filtra las citas por una fecha específica.
        }

        return $query;
    }

    public function scopefilterAdvancePay($query,$specialitie_id,$search_doctor,$search_patient,$date_start,$date_end,$user = null){

        if($user){
            if(str_contains(Str::upper($user->roles->first()->name),'DOCTOR')){
                $query->where("doctor_id",$user->id);
                // Filtra por el doctor asociado si el usuario es un doctor.
            }
        } 
        
        if($specialitie_id){
            $query->where("specialitie_id",$specialitie_id);
            // Filtra por especialidad médica.
        }

        if($search_doctor){
            $query->whereHas("doctor",function($q) use($search_doctor){
                $q->where(DB::raw("CONCAT(users.name,' ',IFNULL(users.surname,''),' ',IFNULL(users.email,''))"),"like","%".$search_doctor."%");
                // ->orWhere("surname","like","%".$search_doctor."%");
                // Busca al doctor por nombre completo o correo electrónico.
            });
        }

        if($search_patient){
            $query->whereHas("patient",function($q) use($search_patient){
                $q->where(DB::raw("CONCAT(patients.name,' ',IFNULL(patients.surname,''),' ',IFNULL(patients.email,''))"),"like","%".$search_patient."%");
                // ->orWhere("surname","like","%".$search_patient."%");
                // Busca al paciente por nombre completo o correo electrónico.
            });
        }

        if($date_start && $date_end){
            $query->whereBetween("date_appointment",[Carbon::parse($date_start)->format("Y-m-d"),Carbon::parse($date_end)->format("Y-m-d")]);
             // Filtra las citas dentro de un rango de fechas.
        }

        return $query;
    }
}

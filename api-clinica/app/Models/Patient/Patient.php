<?php

namespace App\Models\Patient;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory;  // Habilita la creación de fábricas para el modelo
    use SoftDeletes; // Habilita el soft delete para el modelo (eliminación lógica)
    
    // Define los campos que pueden ser asignados en masa
    protected $fillable = [
        "name",
        "surname",
        "mobile",
        "email",
        "avatar",
        "birth_date",
        "gender",
        "education",
        "address",
        "antecedent_family",
        "antecedent_personal",
        "antecedent_allergic",
        "current_disease",
        "ta",           // Tensión arterial
        "temperature",  // Temperatura corporal
        "fc",           // Frecuencia cardiaca
        "fr",           // Frecuencia respiratoria
        "peso",         // Peso del paciente
        "n_document",
        // "created_at",
    ];
    
 // Setea la zona horaria y actualiza el atributo 'created_at' con la fecha y hora actuales
    public function setCreatedAtAttribute($value)
    {
    	date_default_timezone_set('America/Bogota'); // Establece la zona horaria a Bogotá
        $this->attributes["created_at"]= Carbon::now(); // Asigna la fecha y hora actuales
    }

    public function setUpdatedAtAttribute($value)
    {
    	date_default_timezone_set("America/Bogota");
        $this->attributes["updated_at"]= Carbon::now();
    }

    // Define la relación uno a uno con el modelo 'PatientPerson'
    public function person() {
        return $this->hasOne(PatientPerson::class,"patient_id");
    }
}

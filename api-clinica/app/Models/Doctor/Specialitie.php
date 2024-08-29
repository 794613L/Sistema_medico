<?php

namespace App\Models\Doctor;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Specialitie extends Model
{
    use HasFactory;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'state',
    ];
    // Configurar la zona horaria y establecer la fecha de creación
    public function setCreatedAtAttribute($value)
    {
    	date_default_timezone_set('America/Bogota');
        $this->attributes["created_at"]= Carbon::now();
    }
    // Configurar la zona horaria y establecer la fecha de actualización
    public function setUpdatedAtAttribute($value)
    {
    	date_default_timezone_set("America/Bogota");
        $this->attributes["updated_at"]= Carbon::now();
    }
}

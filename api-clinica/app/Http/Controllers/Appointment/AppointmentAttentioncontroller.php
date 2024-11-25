<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentAttention;
use Illuminate\Http\Request;

class AppointmentAttentioncontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {    // Busca la cita por el ID recibido en la solicitud.
        $appointment = Appointment::findOrFail($request->appointment_id);
        // Obtiene la atención de la cita, si existe.
        $appointment_attention = $appointment->attention;
        // Convierte el array de 'medical' en un JSON antes de almacenarlo.
        $request->request->add(["recipe_medica" => json_encode($request->medical)]);
        
        // Si ya existe una atención asociada a la cita...
        if($appointment_attention){
             // Autoriza la acción según los permisos definidos en el sistema.
            $this->authorize('view',$appointment_attention);
            // Si no tiene una fecha de atención, se asigna la fecha actual y se cambia el estado de la cita a 'atendida'.
            if(!$appointment->date_attention){
                $appointment->update(["status" => 2,
                "date_attention" => now()]);
            }
             // Actualiza la atención de la cita con los datos proporcionados en la solicitud.
            $appointment_attention->update($request->all());
        }else{
            // Si no existe una atención, autoriza la vista de la cita.
            $this->authorize('viewAppointment',$appointment);
             // Crea una nueva atención para la cita.
            AppointmentAttention::create($request->all());
            // Cambia el estado de la cita a 'atendida' y asigna la fecha de atención.
            date_default_timezone_set('America/Bogota');
            $appointment->update(["status" => 2,
            "date_attention" => now()]);
        }
        // Responde con un mensaje de éxito.
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {    // Busca la cita por su ID.
        $appointment = Appointment::findOrFail($id);
        // Obtiene la atención asociada a la cita.
        $appointment_attention = $appointment->attention;
         // Si existe una atención asociada, devuelve sus detalles.
        if($appointment_attention){
        // Autoriza la acción de ver la atención.
        $this->authorize('view',$appointment_attention);
        return response()->json([
            "appointment_attention" => [
                "id" => $appointment_attention->id,
                "description" => $appointment_attention->description,
                // Si la receta médica existe, la decodifica de JSON, de lo contrario, devuelve un array vacío.
                "recipe_medica" => $appointment_attention->recipe_medica ? json_decode($appointment_attention->recipe_medica) : [],
                // Formatea la fecha de creación de la atención.
                "created_at" => $appointment_attention->created_at->format("Y-m-d h:i A"),
            ]
        ]);

        }else{
             // Si no hay atención asociada, devuelve datos vacíos.
            return response()->json([
                "appointment_attention" => [
                "id" => NULL,
                "description" => NULL,
                "recipe_medica" => [],
                "created_at" => NULL,
            ]
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Appointment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Appointment\pay\AppointmentPayCollection;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentPay;

class AppointmentPayController extends Controller
{
    /**
     * Display a listing of the resource.
     * Método que lista los pagos de las citas médicas según varios filtros de búsqueda.
     */
    public function index(Request $request)
    {     // Verifica que el usuario tenga permisos para ver los pagos de citas
        $this->authorize('viewAny',AppointmentPay::class);
         // Recupera los parámetros de búsqueda de la solicitud
        $specialitie_id = $request->specialitie_id;
        $search_doctor = $request->search_doctor;
        $search_patient = $request->search_patient;
        $date_start = $request->date_start;
        $date_end = $request->date_end;
         // Obtiene el usuario autenticado
        $user = auth('api')->user();
        
        // Filtra las citas médicas por los criterios proporcionados y ordena por estado de pago
        $appointments = Appointment::filterAdvancePay($specialitie_id,$search_doctor,$search_patient,$date_start,$date_end,$user)
                                    ->orderBy("status_pay","desc")
                                    ->paginate(20);
        // Devuelve la respuesta con los pagos de citas
        return response()->json([
            "total" => $appointments->total(),
            "appointments" => AppointmentPayCollection::make($appointments),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Método para registrar un pago para una cita médica.
     */
    public function store(Request $request)
    {
      // Calcula el total de los pagos existentes para la cita
        $sum_total_pays = AppointmentPay::where("appointment_id",$request->appointment_id)->sum("amount");
        // if(($sum_total_pays + $request->amount) > $request->appointment_total){
        //     return response()->json([
        //         "message" => 403,
        //         "message_text" => "EL MONTO QUE SE QUIERE REGISTRAR SUPERA AL COSTO DE LA CITA MEDICA",
        //     ]);
        // }
        // Busca la cita médica asociada al pago
        $appointment = Appointment::findOrFail($request->appointment_id);
        
        // Verifica si el usuario tiene permisos para agregar el pago a la cita
        $this->authorize('addPayment',$appointment);
        // Registra el nuevo pago
        $appointment_pay = AppointmentPay::create([
            "appointment_id" =>  $request->appointment_id,
            "amount" => $request->amount,
            "method_payment" => $request->method_payment,
        ]);
        // Verifica si el pago es el total de la cita
        $is_total_payment = false;
        if(($appointment->amount) == ($sum_total_pays + $request->amount)){
            $appointment->update(["status_pay" => 1]);
            $is_total_payment = true;
        }
        // Devuelve la respuesta con el detalle del pago registrado
        return response()->json([
            "message" => 200,
            "appointment_pay" => [
                "is_total_payment" => $is_total_payment,
                "id" => $appointment_pay->id,
                "appointment_id"=> $appointment_pay->appointment_id,
                "amount"=> $appointment_pay->amount,
                "method_payment"=> $appointment_pay->method_payment,
                "created_at" => $appointment_pay->created_at->format("Y-m-d h:i A"),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     * Este método se deja vacío, ya que no se está utilizando en este controlador.
     */
    public function show(string $id)
    {
        //
    }

     /**
     * Update the specified resource in storage.
     * Método para actualizar el pago de una cita médica.
     */
    public function update(Request $request, string $id)
    {   // Calcula el total de los pagos existentes para la cita
        $sum_total_pays = AppointmentPay::where("appointment_id",$request->appointment_id)->sum("amount");
         // Encuentra el pago que se desea actualizar
        $appointment_pay = AppointmentPay::findOrFail($id);
        // Calcula el monto viejo y el nuevo monto
        $old_amount = $appointment_pay->amount;
        $new_amount = $request->amount;
        // Verifica si el usuario tiene permisos para ver el pago
        $this->authorize('view',$appointment_pay);
        
        // Verifica que la actualización del pago no supere el costo de la cita
        if((($sum_total_pays - $old_amount) + $new_amount) > $request->appointment_total){
            return response()->json([
                "message" => 403,
                "message_text" => "EL MONTO QUE SE QUIERE EDITAR SUPERA AL COSTO DE LA CITA MEDICA",
            ]);
        }
        // Actualiza el pago
        $appointment_pay->update([
            "amount" => $request->amount,
            "method_payment" => $request->method_payment,
        ]);
        // Verifica si la cita está completamente pagada después de la actualización
        $appointment = Appointment::findOrFail($request->appointment_id);
        $is_total_payment = false;
        if(($appointment->amount) == (($sum_total_pays - $old_amount) + $new_amount)){
            $appointment->update(["status_pay" => 1]);
            $is_total_payment = true;
        }else{
            $appointment->update(["status_pay" => 2]);
        }
        // Devuelve la respuesta con el detalle del pago actualizado
        return response()->json([
            "message" => 200,
            "appointment_pay" => [
                "is_total_payment" => $is_total_payment,
                "id" => $appointment_pay->id,
                "appointment_id"=> $appointment_pay->appointment_id,
                "amount"=> $appointment_pay->amount,
                "method_payment"=> $appointment_pay->method_payment,
                "created_at" => $appointment_pay->created_at->format("Y-m-d h:i A"),
            ],
        ]); 
    }

    /**
     * Remove the specified resource from storage.
     * Método para eliminar un pago de cita médica.
     */
    public function destroy(string $id)
    {    // Encuentra el pago que se desea eliminar
        $appointment_pay = AppointmentPay::findOrFail($id);
        // Verifica si el usuario tiene permisos para eliminar el pago
        $this->authorize('delete',$appointment_pay);
        // Encuentra la cita asociada al pago y actualiza su estado
        $appointment = Appointment::findOrFail($appointment_pay->appointment_id);
        $appointment->update(["status_pay" => 2]);
         // Elimina el pago
        $appointment_pay->delete();
        // Devuelve la respuesta indicando que el pago ha sido eliminado correctamente
        return response()->json([
            "message" => 200,
        ]);
    }
}

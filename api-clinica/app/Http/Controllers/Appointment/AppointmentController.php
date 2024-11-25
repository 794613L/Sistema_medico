<?php

namespace App\Http\Controllers\Appointment;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use App\Models\Doctor\Specialitie;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Patient\PatientPerson;
use App\Models\Appointment\Appointment;
use App\Models\Doctor\DoctorScheduleDay;
use App\Models\Appointment\AppointmentPay;
use App\Models\Doctor\DoctorScheduleJoinHour;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Appointment\AppointmentCollection;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny',Appointment::class);// Verifica permisos del usuario.
        
        // Obtiene parámetros de búsqueda desde la solicitud.
        $specialitie_id = $request->specialitie_id;
        $name_doctor = $request->search;
        $date = $request->date;
        $user = auth('api')->user();// Usuario autenticado

        // Consulta las citas con filtros aplicados y ordena por ID descendente.
        $appointments = Appointment::filterAdvance($specialitie_id,$name_doctor,$date,$user)->orderBy("id","desc")
                                    ->paginate(20);
        // Devuelve la respuesta con datos paginados.
        return response()->json([
            "total" => $appointments->total(),
            "appointments" => AppointmentCollection::make($appointments),
        ]);
    }
// Define un conjunto fijo de horas de consulta.
    public function config(){
        $hours = [
            [
                "id" => "08",
                "name" => "8:00 AM",
            ],
            [
                "id" => "09",
                "name" => "9:00 AM",
            ],
            [
                "id" => "10",
                "name" => "10:00 AM",
            ],
            [
                "id" => "11",
                "name" => "11:00 AM",
            ],
            [
                "id" => "12",
                "name" => "12:00 PM",
            ],
            [
                "id" => "13",
                "name" => "01:00 PM",
            ],
            [
                "id" => "14",
                "name" => "02:00 PM",
            ],
            [
                "id" => "15",
                "name" => "03:00 PM",
            ],
            [
                "id" => "16",
                "name" => "04:00 PM",
            ],
            [
                "id" => "17",
                "name" => "05:00 PM",
            ]
            
        ];
        $specialities = Specialitie::where("state",1)->get();// Obtiene todas las especialidades activas.
        return response()->json([// Devuelve las especialidades y horas configuradas.
            "specialities" => $specialities,
            "hours" => $hours,
        ]);
    }

    public function filter(Request $request) {// Verifica permisos.
        
        $this->authorize('filter',Appointment::class);
        // Obtiene parámetros de búsqueda.
        $date_appointment = $request->date_appointment;
        $hour = $request->hour;
        $specialitie_id = $request->specialitie_id;

         // Configura el idioma y la zona horaria.
        date_default_timezone_set('America/Bogota');
        Carbon::setLocale('es');
        DB::statement("SET lc_time_names = 'es_ES'");

        $name_day = Carbon::parse($date_appointment)->dayName;// Obtiene el nombre del día de la cita.
        // Busca doctores disponibles según el día, especialidad y horario.
        $doctor_query = DoctorScheduleDay::where("day","like","%".$name_day."%")
                                            ->whereHas("doctor",function($q) use($specialitie_id){
                                                $q->where("specialitie_id",$specialitie_id);
                                            })->whereHas("schedules_hours",function($q) use($hour){
                                                $q->whereHas("doctor_schedule_hour",function($qs) use($hour) {
                                                    $qs->where("hour",$hour);
                                                });
                                            })->get();
               // Procesa los doctores encontrados. 
              $doctors = collect([]);
              foreach ($doctor_query as $doctor_q) {
              
                    $segments = DoctorScheduleJoinHour::where("doctor_schedule_day_id",$doctor_q->id)
                                                        ->whereHas("doctor_schedule_hour",function($q) use($hour) {
                                                            $q->where("hour",$hour);
                                                        })->get();
                    // Construye el formato de respuesta con doctores y segmentos.                                    
                    $doctors->push([
                            "doctor" => [
                                "id" => $doctor_q->doctor->id,
                                "full_name" => $doctor_q->doctor->name .' '.$doctor_q->doctor->surname,
                                "specialitie" => [
                                    "id" => $doctor_q->doctor->specialitie->id,
                                    "name" => $doctor_q->doctor->specialitie->name,
                                ],
                            ],
                            "segments" => $segments->map(function($segment) use($date_appointment,$doctor_q){
                                $appointment = Appointment::whereHas("doctor_schedule_join_hour",function($q) use($segment,$doctor_q){
                                                            $q->where("doctor_schedule_hour_id",$segment->doctor_schedule_hour->id);
                                                        })
                                                            ->whereDate("date_appointment",Carbon::parse($date_appointment)->format("Y-m-d"))
                                                            ->where("doctor_id",$doctor_q->doctor->id)
                                                            ->first();
                                 // Devuelve información sobre el segmento.                           
                                return[ 
                                    "id" => $segment->id,
                                    "doctor_schedule_day_id" => $segment->doctor_schedule_day_id,
                                    "doctor_schedule_hour_id" => $segment->doctor_schedule_hour_id,
                                    "is_appointment" => $appointment ? true : false,
                                    "format_segment" => [
                                            "id" => $segment->doctor_schedule_hour->id,
                                            "hour_start" => $segment->doctor_schedule_hour->hour_start,
                                            "hour_end" => $segment->doctor_schedule_hour->hour_end,
                                            "format_hour_start" => Carbon::parse(date("Y-m-d").' '.$segment->doctor_schedule_hour->hour_start)->format("h:i A"),
                                            "format_hour_end" => Carbon::parse(date("Y-m-d").' '.$segment->doctor_schedule_hour->hour_end)->format("h:i A"),
                                            "hour" => $segment->doctor_schedule_hour->hour,
                                        ]
                                ];
                            })
                    ]);
              }                                
            //   dd($doctors);
               // Devuelve la lista de doctores disponibles.
              return response()->json([
                "doctors" => $doctors,
              ]);

    }

    public function calendar(Request $request){

        $specialitie_id = $request->specialitie_id;
        $search_doctor = $request->search_doctor;
        $search_patient = $request->search_patient;
        $user = auth('api')->user();
        // Filtra citas médicas según parámetros.
        $appointments = Appointment::filterAdvancePay($specialitie_id,$search_doctor,$search_patient,null,null,$user)
                                    ->orderBy("id","desc")
                                    ->get();
         // Formatea las citas para el calendario.
        return response()->json([
            "appointments" => $appointments->map(function($appointment) {
                return [
                    "id" => $appointment->id,
                    "title" => "CITA MEDICA - ".($appointment->doctor->name. ' '.$appointment->doctor->surname)." - ".$appointment->specialitie->name,
                    "start" => Carbon::parse($appointment->date_appointment)->format("Y-m-d")."T".$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start,
                    "end" => Carbon::parse($appointment->date_appointment)->format("Y-m-d")."T".$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end,
                ];
            })
        ]);
    }

    public function query_patient(Request $request){
        // Obtiene el número de documento del paciente desde la solicitud.
        $n_document = $request->get("n_document");
         // Busca al paciente en la base de datos.
        $patient = Patient::where("n_document",$n_document)->first();
        // Si no se encuentra al paciente, devuelve un código de error 403.
        if(!$patient){
            return response()->json([
                "message" => 403,
            ]);
        }
         // Si se encuentra al paciente, devuelve los datos relevantes del paciente.
        return response()->json([
            "message" => 200,
            "name" => $patient->name,
            "surname" => $patient->surname,
            "mobile" => $patient->mobile,
            "n_document" => $patient->n_document,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   // Autoriza la acción de crear una cita médica.
        $this->authorize('create',Appointment::class);
        // doctor_id
        // name
        // surname
        // mobile
        // n_document
        // name_companion
        // surname_companion
        // date_appointment
        // specialitie_id
        // doctor_schedule_join_hour_id
        // amount
        // amount_add
        // method_payment

         // Inicializa la variable $patient en null.
        $patient = null;
         // Intenta buscar al paciente por el número de documento.
        $patient = Patient::where("n_document",$request->n_document)->first();
         // Si el paciente no existe, se crea un nuevo registro de paciente.
        if(!$patient){
            $patient = Patient::create([
                "name" => $request->name,
                "surname" => $request->surname,
                "mobile" => $request->mobile,
                "n_document" => $request->n_document,
            ]);
            // También crea un registro de la persona acompañante del paciente.
            PatientPerson::create([
                "patient_id" => $request->id,
                "name_companion" => $request->name_companion,
                "surname_companion" => $request->surname_companion,
            ]);
            }else{
                  // Si el paciente ya existe, actualiza la información de la persona acompañante.
                $patient->person->update([
                "name_companion" => $request->name_companion,
                "surname_companion" => $request->surname_companion,
                ]);
            }
            // Crea una nueva cita médica en la base de datos.
        $appointment = Appointment::create([
            "doctor_id" => $request->doctor_id,
            "patient_id" => $patient->id,
            "date_appointment" => Carbon::parse($request->date_appointment)->format("Y-m-d h:i:s"),
            "specialitie_id" => $request->specialitie_id,
            "doctor_schedule_join_hour_id" => $request->doctor_schedule_join_hour_id,
            "user_id" => auth("api")->user()->id,
            "amount" => $request->amount,
            "status_pay" => $request->amount != $request->amount_add ? 2 : 1,
        ]);

        // Registra el pago de la cita médica.
        AppointmentPay::create([
            "appointment_id" => $appointment->id,
            "amount" => $request->amount_add,
            "method_payment" => $request->method_payment,
        ]);
         // Retorna un mensaje de éxito.
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {   // Busca la cita médica por su ID.
        $appointment = Appointment::findOrFail($id);
        // Autoriza la acción de ver la cita médica.
        $this->authorize('view',$appointment);
         // Devuelve los detalles de la cita médica en formato de recurso.
        return response()->json([
            "appointment" => AppointmentResource::make($appointment)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {   // Busca la cita médica por su ID.
        $appointment = Appointment::findOrFail($id);
        // Autoriza la acción de actualizar la cita médica.
        $this->authorize('update',$appointment);
        // Verifica que el monto de los pagos existentes no sea mayor al monto actualizado.
        if($appointment->payments->sum("amount") > $request->amount){
            return response()->json([
                "message" => 403,
                "message_text" => "LOS PAGOS INGRESADOS SUPERAN AL NUEVO MONTO QUE QUIERE GUARDAR"
            ]);
        }
         // Actualiza la cita médica con los nuevos valores.
        $appointment-> update([
            "doctor_id" => $request->doctor_id,
            "date_appointment" => Carbon::parse($request->date_appointment)->format("Y-m-d h:i:s"),
            "specialitie_id" => $request->specialitie_id,
            "doctor_schedule_join_hour_id" => $request->doctor_schedule_join_hour_id,
            "amount" => $request->amount,
            "status_pay" => $appointment->payments->sum("amount") != $request->amount ? 2 : 1,
        ]);
        // Retorna un mensaje de éxito.
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {   // Busca la cita médica por su ID.
        $appointment = Appointment::findOrFail($id);
        // Autoriza la acción de eliminar la cita médica.
        $this->authorize('delete',$appointment);
        // Elimina la cita médica de la base de datos.
        $appointment->delete();
        // Retorna un mensaje de éxito.
        return response()->json([
            "message" => 200,
        ]);
    }
}

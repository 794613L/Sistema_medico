<?php

namespace App\Http\Controllers\Patient;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Patient\Patient;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Patient\PatientPerson;
use App\Models\Appointment\Appointment;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Patient\PatientResource;
use App\Http\Resources\Patient\PatientCollection;
use App\Http\Resources\Appointment\AppointmentCollection;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     * Método para listar los pacientes con una opción de búsqueda.
     */

    public function index(Request $request)
    {
        // Recoge el término de búsqueda desde la solicitud
        $this->authorize('viewAny', Patient::class);
        $search = $request->search;

        // Realiza una consulta para buscar pacientes cuyo nombre, apellido o email coincidan con el término de búsqueda
        $patients = Patient::where(DB::raw("CONCAT(patients.name,' ',IFNULL(patients.surname,''),' ',patients.email)"), "like", "%" . $search . "%")
            ->orderBy("id", "desc") // Ordena los resultados por ID de manera descendente
            ->paginate(20); // Pagina los resultados de la consulta

        // Devuelve los pacientes como una colección JSON
        return response()->json([
            "total" => $patients->total(),
            "patients" => PatientCollection::make($patients),
        ]);
    }

    public function profile($id)
    {

        $patient = Patient::findOrFail($id);

        $num_appointment = Appointment::where("patient_id", $id)->count();
        $money_of_appointments = Appointment::where("patient_id", $id)->sum("amount");
        $num_appointment_pendings = Appointment::where("patient_id", $id)->where("status", 1)->count();

        $appointment_pendings = Appointment::where("patient_id", $id)->where("status", 1)->get();
        $appointments = Appointment::where("patient_id", $id)->get();
        return response()->json([
            "num_appointment" => $num_appointment,
            "money_of_appointments" => $money_of_appointments,
            "num_appointment_pendings" => $num_appointment_pendings,
            "patient" => PatientResource::make($patient),
            "appointment_pendings" => AppointmentCollection::make($appointment_pendings),
            "appointments" => $appointments->map(function ($appointment) {
                return [
                    "id" => $appointment->id,
                    "patient" => [
                        "id" => $appointment->patient->id,
                        "full_name" =>  $appointment->patient->name . ' ' . $appointment->patient->surname,
                        "avatar" => $appointment->patient->avatar ? env("APP_URL") . "storage/" . $appointment->patient->avatar : NULL,
                    ],
                    "doctor" => [
                        "id" => $appointment->doctor->id,
                        "full_name" =>  $appointment->doctor->name . ' ' . $appointment->doctor->surname,
                        "avatar" => $appointment->doctor->avatar ? env("APP_URL") . "storage/" . $appointment->doctor->avatar : NULL,
                    ],
                    "date_appointment" => $appointment->date_appointment,
                    "date_appointment_format" => Carbon::parse($appointment->date_appointment)->format("d M Y"),
                    "format_hour_start" => Carbon::parse(date("Y-m-d") . ' ' . $appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start)->format("h:i A"),
                    "format_hour_end" => Carbon::parse(date("Y-m-d") . ' ' . $appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end)->format("h:i A"),
                    "appointment_attention" => $appointment->attention ? [
                        "id" => $appointment->id,
                        "description" => $appointment->attention->description,
                        "recipe_medica" => $appointment->attention->recipe_medica ? json_decode($appointment->attention->recipe_medica) : [],
                        "created_at" => $appointment->attention->created_at->format("Y-m-d h:i A"),
                    ] : NULL,
                    "amount" => $appointment->amount,
                    "status_pay" => $appointment->status_pay,
                    "status" => $appointment->status,
                ];
            }),
        ]);
    }
    /**
     * Store a newly created resource in storage.
     * Método para almacenar un nuevo paciente en la base de datos.
     */
    public function store(Request $request)
    {

        // Verifica si ya existe un paciente con el mismo número de documento
        $this->authorize('create', Patient::class);
        $patient_is_valid = Patient::where("n_document", $request->n_document)->first();

        if ($patient_is_valid) {
            // Devuelve un mensaje de error si el paciente ya existe
            return response()->json([
                "message" => 403,
                "message_text" => "EL PACIENTE YA EXISTE EN EL SISTEMA."
            ]);
        }

        // Si la solicitud contiene una imagen, la guarda y añade la ruta al request
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("patients", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        // "Fri Oct 08 1993 00:00:00 GMT-0500 (hora estándar de Colombia)"
        // Eliminar la parte de la zona horaria (GMT-0500 y entre paréntesis)


        // Limpia y formatea la fecha de nacimiento para eliminar la zona horaria y el texto
        if ($request->birth_date) {
            $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);

            $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);
        }



        // Crea un nuevo registro de paciente en la base de datos
        $patient = Patient::create($request->all());

        // Añade el ID del paciente al request y crea un registro de la persona relacionada
        $request->request->add(["patient_id" => $patient->id]);
        PatientPerson::create($request->all());

        // Devuelve una respuesta de éxito
        return response()->json([
            "message" => 200
        ]);
    }

    /**
     * Display the specified resource.
     *  Muestra los detalles de un paciente específico.
     */
    public function show(string $id)
    {

        // Busca el paciente por su ID o lanza un error 404 si no se encuentra
        $this->authorize('view', Patient::class);
        $patient = Patient::findOrFail($id);

        // Devuelve los detalles del paciente como JSON
        return response()->json([
            "patient" => PatientResource::make($patient),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza los datos de un paciente específico.
     */
    public function update(Request $request, string $id)
    {

        // Verifica si otro paciente con el mismo número de documento ya existe
        $this->authorize('update', Patient::class);
        $patient_is_valid = Patient::where("id", "<>", $id)->where("n_document", $request->n_document)->first();

        if ($patient_is_valid) {
            // Devuelve un mensaje de error si el paciente ya existe
            return response()->json([
                "message" => 403,
                "message_text" => "EL PACIENTE YA EXISTE EN EL SISTEMA."
            ]);
        }

        // Busca el paciente por su ID o lanza un error 404 si no se encuentra
        $patient = Patient::findOrFail($id);

        // Si la solicitud contiene una nueva imagen, elimina la imagen anterior y guarda la nueva
        if ($request->hasFile("imagen")) {
            if ($patient->avatar) {
                Storage::delete($patient->avatar); // Elimina la imagen anterior
            }
            $path = Storage::putFile("patients", $request->file("imagen"));
            $request->request->add(["avatar" => $path]); // Añade la nueva ruta del avatar al request
        }

        // Limpia y formatea la fecha de nacimiento para eliminar la zona horaria y el texto
        if ($request->birth_date) {
            $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);

            $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);
        }

        // $request->request->add(["birth_date" => Carbon::parse($request->birth_date, 'GMT')->format("Y-m-d h:i:s")]);

        // Actualiza el registro del paciente en la base de datos con los nuevos datos
        $patient->update($request->all());

        // Si existe una persona asociada al paciente, actualiza sus datos también
        if ($patient->person) {
            $patient->person->update($request->all());
        }

        // Devuelve una respuesta de éxito
        return response()->json([
            "message" => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina un paciente específico de la base de datos.
     */
    public function destroy(string $id)
    {
        // Busca el paciente por su ID o lanza un error 404 si no se encuentra
        $this->authorize('delete', Patient::class);
        $patient = patient::findOrFail($id);
        // Elimina la imagen asociada al paciente si existe
        if ($patient->avatar) {
            Storage::delete($patient->avatar);
        }
        // Elimina el registro del paciente de la base de datos
        $patient->delete();

        // Devuelve una respuesta de éxito
        return response()->json([
            "message" => 200
        ]);
    }
}

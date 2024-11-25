<?php
// Importamos las clases necesarias para manejar las rutas y controladores.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\Rol\RolesController;
use App\Http\Controllers\Patient\PatientController;
use App\Http\Controllers\Admin\Staff\StaffsController;
use App\Http\Controllers\Admin\Doctor\DoctorsController;
use App\Http\Controllers\Dashboard\DashboardKpiController;
use App\Http\Controllers\Admin\Doctor\SpecialityController;
use App\Http\Controllers\Appointment\AppointmentController;
use App\Http\Controllers\Appointment\AppointmentPayController;
use App\Http\Controllers\Appointment\AppointmentAttentioncontroller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Este archivo registra todas las rutas de la API para la aplicación. Las 
| rutas aquí definidas son cargadas por el RouteServiceProvider y están 
| agrupadas bajo el middleware 'api' para garantizar seguridad y modularidad.
|
*/
// Ruta básica para obtener información del usuario autenticado.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Grupo de rutas relacionadas con la autenticación (registro, inicio de sesión, etc.
Route::group([

    // 'middleware' => 'auth:api',
    'prefix' => 'auth',
    // 'middleware' => ['role:admin','permission:publish articles'],
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/list', [AuthController::class, 'list']);
    Route::post('/reg', [AuthController::class, 'reg']);
});

Route::group([
    'middleware' => 'auth:api',
], function ($router) {
    // Gestión de roles (CRUD).
    Route::resource("roles", RolesController::class);

       // Rutas específicas para la configuración y actualización de personal.
    Route::get("staffs/config", [StaffsController::class, "config"]);
    Route::post("staffs/{id}", [StaffsController::class, "update"]);
    Route::resource("staffs", StaffsController::class);

    //    // Gestión de especialidades médicas.
    Route::resource("specialities", SpecialityController::class);

    // Rutas para gestionar los datos y el perfil de los doctores.
    Route::get("doctors/profile/{id}", [DoctorsController::class, "profile"]);
    Route::get("doctors/config", [DoctorsController::class, "config"]);
    Route::post("doctors/{id}", [DoctorsController::class, "update"]);
    Route::resource("doctors", DoctorsController::class);

    //  // Gestión de pacientes.
    Route::get("patients/profile/{id}", [PatientController::class, "profile"]);
    Route::post("patients/{id}", [PatientController::class, "update"]);
    Route::resource("patients", PatientController::class);

    // Rutas para la gestión de citas médicas.
    Route::get("appointment/config", [AppointmentController::class, "config"]);
    Route::get("appointment/patient", [AppointmentController::class, "query_patient"]);
    Route::post("appointment/filter", [AppointmentController::class, "filter"]);
    Route::post("appointment/calendar", [AppointmentController::class, "calendar"]);
    Route::resource("appointment", AppointmentController::class);

    // Gestión de pagos y atenciones de citas médicas.
    Route::resource("appointment-pay", AppointmentPayController::class);
    Route::resource("appointment-attention", AppointmentAttentioncontroller::class);

    //  Rutas del panel de control (Dashboard).
    Route::post("dashboard/admin", [DashboardKpiController::class,"dashboard_admin"]);
    Route::post("dashboard/admin-year", [DashboardKpiController::class,"dashboard_admin_year"]);
    Route::post("dashboard/doctor", [DashboardKpiController::class,"dashboard_doctor"]);
    Route::get("dashboard/config", [DashboardKpiController::class,"config"]);
    Route::post("dashboard/doctor-year", [DashboardKpiController::class,"dashboard_doctor_year"]);

});

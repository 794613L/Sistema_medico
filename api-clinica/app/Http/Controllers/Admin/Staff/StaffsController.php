<?php

namespace App\Http\Controllers\Admin\Staff;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use Illuminate\Support\Facades\DB;

class StaffsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny',User::class);

        $search = $request->search;

        $users = User::where(DB::raw("CONCAT(users.name,' ',IFNULL(users.surname,''),' ',users.email)"),"like","%".$search."%")
            // "name", "like", "%" . $search . "%"
            // ->orWhere("surname", "like", "%" . $search . "%")
            // ->orWhere("email", "like", "%" . $search . "%")
            ->orderBy("id", "desc")
            ->whereHas("roles",function($q){
                $q->where("name","not like","%DOCTOR%");
            })
            ->get();

        return response()->json([
            "users" => UserCollection::make($users),
        ]);
    }

    public function config()
    {
        // Obtener y devolver todos los roles
        $roles = Role::where("name","not like","%DOCTOR%")->get();

        return response()->json([
            "roles" => $roles
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar si el correo ya está registrado
        $this->authorize('create',User::class);
        $users_is_valid = User::where("email", $request->email)->first();

        if ($users_is_valid) {
            return response()->json([
                "message" => 403,
                "message_text" => "El CORREO INGRESADO YA ESTÁ REGISTRADO. INTENTA CON UNO DIFERENTE"
            ]);
        }

        // Guardar imagen de avatar si se proporciona
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("staffs", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        // Encriptar contraseña si se proporciona
        if ($request->password) {
            $request->request->add(["password" => bcrypt($request->password)]);
        }
        // "Fri Oct 08 1993 00:00:00 GMT-0500 (hora estándar de Colombia)"
        // Eliminar la parte de la zona horaria (GMT-0500 y entre paréntesis)

        // Limpiar y formatear fecha de nacimiento
        $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);
        $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);

        // Crear nuevo usuario
        $user = User::create($request->all());

        // Asignar rol al usuario
        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);
        return response()->json([
            "message" => 200
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Mostrar detalles de un usuario específico
        $this->authorize('view',User::class);
        $user = User::findOrFail($id);

        return response()->json([
            "user" => UserResource::make($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validar si el nuevo correo ya está registrado en otro usuario
        $this->authorize('update',User::class);
        $users_is_valid = User::where("id", "<>", $id)->where("email", $request->email)->first();

        if ($users_is_valid) {
            return response()->json([
                "message" => 403,
                "message_text" => "El CORREO INGRESADO YA ESTÁ REGISTRADO. INTENTA CON UNO DIFERENTE"
            ]);
        }

        // Obtener usuario a actualizar
        $user = User::findOrFail($id);

        // Actualizar imagen de avatar si se proporciona
        if ($request->hasFile("imagen")) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            $path = Storage::putFile("staffs", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);
        }

        // Encriptar contraseña si se proporciona
        if ($request->password) {
            $request->request->add(["password" => bcrypt($request->password)]);
        }

        if($request->birth_date){
            $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);
            $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);
        }

        // $request->request->add(["birth_date" => Carbon::parse($request->birth_date, 'GMT')->format("Y-m-d h:i:s")]);

        // Actualizar usuario con los nuevos datos
        $user->update($request->all());

        // Actualizar rol del usuario si ha cambiado
        if ($request->role_id && $request->role_id != $user->roles()->first()->id) {
            $role_old = Role::findOrFail($user->roles()->first()->id);
            $user->removeRole($role_old);

            $role_new = Role::findOrFail($request->role_id);
            $user->assignRole($role_new);
        }
        return response()->json([
            "message" => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Eliminar usuario y su avatar si existe
        $this->authorize('delete',User::class);
        $user = User::findOrFail($id);
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }
        $user->delete();
        return response()->json([
            "message" => 200
        ]);
    }
}

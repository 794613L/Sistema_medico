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
     * Muestra una lista de usuarios, filtrando por nombre, apellido o correo electrónico.
     */
    public function index(Request $request)
    {
         // Autoriza que el usuario actual pueda ver la lista de usuarios
        $this->authorize('viewAny',User::class);
        // Se obtiene el término de búsqueda desde el request
        $search = $request->search;
         // Se consulta la base de datos para encontrar usuarios que coincidan con el término de búsqueda
        $users = User::where(DB::raw("CONCAT(users.name,' ',IFNULL(users.surname,''),' ',users.email)"),"like","%".$search."%")
            // "name", "like", "%" . $search . "%"
            // ->orWhere("surname", "like", "%" . $search . "%")
            // ->orWhere("email", "like", "%" . $search . "%")
            ->orderBy("id", "desc") // Ordena los resultados por id de manera descendente
            ->whereHas("roles",function($q){
                // Excluye usuarios con roles que contienen "DOCTOR"
                $q->where("name","not like","%DOCTOR%");
            })
            ->get();
         // Se devuelve la lista de usuarios filtrados en formato JSON
        return response()->json([
            "users" => UserCollection::make($users),
        ]);
    }
    /**
     * Configuración de roles.
     * Devuelve todos los roles disponibles para asignar a un usuario, excluyendo "DOCTOR".
     */
    public function config()
    {
         // Se obtiene todos los roles que no contienen "DOCTOR"
        $roles = Role::where("name","not like","%DOCTOR%")->get();

        return response()->json([
            "roles" => $roles // Devuelve los roles en formato JSON
        ]);
    }
    /**
     * Store a newly created resource in storage.
     * Crea un nuevo usuario y asigna un rol.
     */
    public function store(Request $request)
    {
         // Autoriza que el usuario actual pueda crear un nuevo usuario
        $this->authorize('create',User::class);
        // Verifica si ya existe un usuario con el mismo correo electrónico
        $users_is_valid = User::where("email", $request->email)->first();
        
        if ($users_is_valid) {
            return response()->json([
                "message" => 403,
                "message_text" => "El CORREO INGRESADO YA ESTÁ REGISTRADO. INTENTA CON UNO DIFERENTE"
            ]);
        }

        // Si el usuario sube una imagen de avatar, se guarda en la carpeta "staffs"
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("staffs", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);// Se agrega el path de la imagen al request
        }

        // Si se proporciona una contraseña, se encripta antes de guardarla
        if ($request->password) {
            $request->request->add(["password" => bcrypt($request->password)]);
        }
        // "Fri Oct 08 1993 00:00:00 GMT-0500 (hora estándar de Colombia)"
        // Eliminar la parte de la zona horaria (GMT-0500 y entre paréntesis)

        // Limpia y formatea la fecha de nacimiento antes de guardarla
        $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);
        $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);

        // Se crea el nuevo usuario con los datos proporcionados
        $user = User::create($request->all());

         // Asigna un rol al nuevo usuario
        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);
        return response()->json([
            "message" => 200
        ]);
    }

     /**
     * Display the specified resource.
     * Muestra los detalles de un usuario específico.
     */
    public function show(string $id)
    {
        // Autoriza que el usuario actual pueda ver los detalles de un usuario
        $this->authorize('view',User::class);
        // Se obtiene el usuario por su ID
        $user = User::findOrFail($id);

        return response()->json([
            "user" => UserResource::make($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Actualiza los detalles de un usuario.
     */
    public function update(Request $request, string $id)
    {
        // Autoriza que el usuario actual pueda actualizar los detalles de un usuario
        $this->authorize('update',User::class);
        // Verifica si el nuevo correo electrónico ya está registrado por otro usuario
        $users_is_valid = User::where("id", "<>", $id)->where("email", $request->email)->first();

        if ($users_is_valid) {
            return response()->json([
                "message" => 403,
                "message_text" => "El CORREO INGRESADO YA ESTÁ REGISTRADO. INTENTA CON UNO DIFERENTE"
            ]);
        }

       // Se obtiene el usuario a actualizar por su ID
        $user = User::findOrFail($id);

         // Si se proporciona una nueva imagen de avatar, se elimina la antigua y se guarda la nueva
        if ($request->hasFile("imagen")) {
            if ($user->avatar) {
                Storage::delete($user->avatar);// Elimina la imagen anterior
            }
            $path = Storage::putFile("staffs", $request->file("imagen"));
            $request->request->add(["avatar" => $path]);// Se agrega el path de la nueva imagen
        }

         // Si se proporciona una nueva contraseña, se encripta antes de actualizarla
        if ($request->password) {
            $request->request->add(["password" => bcrypt($request->password)]);
        }
        // Si se proporciona una nueva fecha de nacimiento, se limpia y formatea
        if($request->birth_date){
            $date_clean = preg_replace('/\(.*\)|[A-Z]{3}-\d{4}/', '', $request->birth_date);
            $request->request->add(["birth_date" => Carbon::parse($date_clean)->format("Y-m-d h:i:s")]);
        }

        // $request->request->add(["birth_date" => Carbon::parse($request->birth_date, 'GMT')->format("Y-m-d h:i:s")]);

         // Actualiza el usuario con los nuevos datos
        $user->update($request->all());

         // Si se proporciona un nuevo rol, se cambia el rol del usuario
        if ($request->role_id && $request->role_id != $user->roles()->first()->id) {
            $role_old = Role::findOrFail($user->roles()->first()->id);
            $user->removeRole($role_old);// Elimina el rol antiguo

            $role_new = Role::findOrFail($request->role_id);
            $user->assignRole($role_new); // Asigna el nuevo rol
        }
        return response()->json([
            "message" => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Elimina un usuario del sistema.
     */
    public function destroy(string $id)
    {
        // Autoriza que el usuario actual pueda eliminar un usuario
        $this->authorize('delete',User::class);
        // Se obtiene el usuario a eliminar por su ID
        $user = User::findOrFail($id);
        // Si el usuario tiene un avatar, se elimina de la carpeta de almacenamiento
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }
        // Elimina el usuario de la base de datos
        $user->delete();
        return response()->json([
            "message" => 200
        ]);
    }
}

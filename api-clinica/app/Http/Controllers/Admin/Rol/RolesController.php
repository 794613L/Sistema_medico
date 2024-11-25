<?php

namespace App\Http\Controllers\Admin\Rol;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class RolesController extends Controller
{
   /**
     * Display a listing of the resource.
     * 
     * Aquí se obtiene la lista de roles filtrados por nombre, si el usuario tiene el permiso adecuado para listar roles.
     * Si el usuario no tiene permiso, se devuelve un error 403.
     */
    public function index(Request $request)
    {
         // Verificar que el usuario tiene el permiso 'list_rol' antes de proceder
        if(!auth('api')->user()->can('list_rol')){
            return response()->json(["message" => "EL USUARIO NO ESTA AUTORIZADO"],403);
        }
        // Obtener el filtro de búsqueda por nombre de rol
        $name = $request->search;
        // Filtrar los roles por el nombre y ordenarlos por id de manera descendente
        $roles = Role::where("name","like","%".$name."%")->orderBy("id","desc")->get();
         // Formatear y retornar los roles con los permisos asociados
        return response()->json([
            "roles" => $roles->map(function($rol) {
                return [
                    "id" => $rol->id,
                    "name" => $rol->name,
                    "permision" => $rol->permissions,
                    "permision_pluck" => $rol->permissions->pluck("name"),
                    "created_at" => $rol->created_at->format("Y-m-d h:i:s")
                ];
            }),
        ]);
    }

     /**
     * Store a newly created resource in storage.
     * 
     * Este método crea un nuevo rol, asegurándose de que el nombre del rol no exista previamente.
     * Luego, asigna los permisos especificados al rol.
     */
    public function store(Request $request)
    {
        // Verificar que el usuario tiene el permiso 'register_rol' para crear un rol
        if(!auth('api')->user()->can('register_rol')){
            return response()->json(["message" => "EL USUARIO NO ESTA AUTORIZADO"],403);
        }
        // Comprobar si ya existe un rol con el mismo nombre
        $is_role = Role::where("name",$request->name)->first();

        if($is_role){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DEL ROL YA EXISTE"
            ]);
        }
        // Crear el nuevo rol
        $role = Role::create([
            'guard_name' => 'api',
            'name' => $request->name,
        ]);
          // Asignar los permisos al nuevo rol
        // ["register_rol","edit_rol","register_paciente"];
        foreach ($request->permisions as $key => $permision) {
            $role->givePermissionTo($permision);
        }
         // Devolver respuesta indicando éxito
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     * 
     * Este método muestra los detalles de un rol específico, incluyendo sus permisos.
     * Verifica que el usuario tiene el permiso adecuado ('edit_rol') para visualizar el rol.
     */
    public function show(string $id)
    {
         // Verificar que el usuario tiene el permiso 'edit_rol' para visualizar el rol
        if(!auth('api')->user()->can('edit_rol')){
            return response()->json(["message" => "EL USUARIO NO ESTA AUTORIZADO"],403);
        }
          // Obtener el rol por su ID
        $role = Role::findOrFail($id);
        // Devolver los detalles del rol
        return response()->json([
            "id" => $role->id,
            "name" => $role->name,
            "permision" => $role->permissions,
            "permision_pluck" => $role->permissions->pluck("name"),
            "created_at" => $role->created_at->format("Y-m-d h:i:s")
        ]);
        
    }

    /**
     * Update the specified resource in storage.
     * 
     * Este método actualiza los detalles de un rol existente, asegurándose de que el nombre no se repita.
     * También actualiza los permisos asignados al rol.
     */
    public function update(Request $request, string $id)
    {
        // Verificar que el usuario tiene el permiso 'edit_rol' para editar el rol
        if(!auth('api')->user()->can('edit_rol')){
            return response()->json(["message" => "EL USUARIO NO ESTA AUTORIZADO"],403);
        }
        // Verificar si el nombre del rol ya existe
        $is_role = Role::where("id","<>",$id)->where("name",$request->name)->first();

        if($is_role){
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DEL ROL YA EXISTE"
            ]);
        }
        // Obtener el rol y actualizar sus detalles
        $role = Role::findOrFail($id);
        // Actualizar el rol
        $role->update($request->all());
        // ["register_rol","edit_rol","register_paciente"];
          // Sincronizar los permisos del rol
        $role->syncPermissions($request->permisions);
         // Devolver respuesta indicando éxito
        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * Este método elimina un rol, pero solo si no tiene usuarios asociados.
     * Verifica que el usuario tenga el permiso 'delete_rol' para eliminar el rol.
     */
    public function destroy(string $id)
    {
        // Verificar que el usuario tiene el permiso 'delete_rol' para eliminar el rol
        if(!auth('api')->user()->can('delete_rol')){
            return response()->json(["message" => "EL USUARIO NO ESTA AUTORIZADO"],403);
        }

        // Obtener el rol
        $role = Role::findOrFail($id);
         // Verificar si el rol tiene usuarios asociados
        if($role->users->count() > 0){
            return response()->json([
                "message" => 403,
                "message_text" => "EL ROL SELECCIONADO NO SE PUEDE ELIMINAR POR MOTIVOS QUE YA TIENE USUARIOS RELACIONADOS"
            ]);
        }

        // Eliminar el rol
        $role->delete();
        // Devolver respuesta indicando éxito
        return response()->json([
            "message" => 200,
        ]);
    }
}

import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { URL_SERVICIOS } from 'src/app/config/config';
import { AuthService } from 'src/app/shared/auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class StaffService {

  constructor(
    public http: HttpClient,
    public authService: AuthService,
  ) { }

  // Obtener la lista de usuarios
  listUsers(){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/staffs";
    return this.http.get(URL,{headers: headers});
  }

  // Obtener la configuración de roles y otras configuraciones relacionadas
  listConfig(){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/staffs/config";
    return this.http.get(URL,{headers: headers});
  }

  // Registrar un nuevo usuario
  registerUser(data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/staffs";
    return this.http.post(URL,data,{headers: headers});
  }

   // Mostrar detalles de un usuario específico
  showUser(staff_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/staffs/"+staff_id;
    return this.http.get(URL,{headers: headers});
  }

  // Actualizar información de un usuario específico
  updateUser(staff_id:string,data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/staffs/"+staff_id;
    return this.http.post(URL,data,{headers: headers});
  }

   // Eliminar un usuario específico
  deleteUser(staff_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/staffs/"+staff_id;
    return this.http.delete(URL,{headers: headers});
  }
}

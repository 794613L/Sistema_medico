import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { URL_SERVICIOS } from 'src/app/config/config';
import { AuthService } from 'src/app/shared/auth/auth.service';

@Injectable({
  providedIn: 'root' // Hace que el servicio esté disponible en toda la aplicación
})
export class PatientMService {

  constructor(
    public http: HttpClient,  // Servicio para realizar peticiones HTTP
    public authService: AuthService, // Servicio de autenticación para obtener el token
  ) { }

  // Método para listar todos los pacientes
  listPatients(page:number=1,search:string=''){
     // Configura los encabezados de la solicitud con el token de autenticación
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/patients?page="+page+"&search="+search; // URL para la solicitud
    return this.http.get(URL,{headers: headers}); // Realiza una solicitud GET
  }

  // Método para registrar un nuevo paciente
  registerPatient(data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/patients";
    return this.http.post(URL,data,{headers: headers});
  }

   // Método para obtener los detalles de un paciente específico
  showPatient(staff_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/patients/"+staff_id;  // URL con el ID del paciente
    return this.http.get(URL,{headers: headers});
  }

  // Método para actualizar los datos de un paciente
  updatePatient(staff_id:string,data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/patients/"+staff_id;
    return this.http.post(URL,data,{headers: headers});
  }

  // Método para eliminar un paciente
  deletePatient(staff_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/patients/"+staff_id;
    return this.http.delete(URL,{headers: headers});
  }

  profilePatient(staff_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/patients/profile/"+staff_id;  // URL con el ID del paciente
    return this.http.get(URL,{headers: headers});
  }
}

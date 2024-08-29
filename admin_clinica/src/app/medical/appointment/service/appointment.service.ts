import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { URL_SERVICIOS } from 'src/app/config/config';
import { AuthService } from 'src/app/shared/auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AppointmentService {

  constructor(
    public http: HttpClient,
    public authService: AuthService,
  ) { }

  listAppointments(page:number=1,search:string = '', specialitie_id:string = '',date:any = null){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let LINK = "";
    if(search){
      LINK+="&search="+search;
    }
    if(specialitie_id){
      LINK+="&specialitie_id="+specialitie_id;
    }
    if(date){
      LINK+="&date="+date;
    }
    let URL = URL_SERVICIOS+"/appointment?page="+page+LINK;
    return this.http.get(URL,{headers: headers});
  }

  listConfig(){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/config";
    return this.http.get(URL,{headers: headers});
  }

  listPatient(n_document:string = ''){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/patient?n_document="+n_document;
    return this.http.get(URL,{headers: headers});
  }

  registerAppointment(data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment";
    return this.http.post(URL,data,{headers: headers});
  }

  listFilter(data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/filter";
    return this.http.post(URL,data,{headers: headers});
  }

  showAppointment(appointment_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/"+appointment_id;
    return this.http.get(URL,{headers: headers});
  }

  updateAppointment(appointment_id:string,data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/"+appointment_id;
    return this.http.put(URL,data,{headers: headers});
  }

  deleteAppointment(appointment_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/"+appointment_id;
    return this.http.delete(URL,{headers: headers});
  }

  registerAttention(data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment-attention";
    return this.http.post(URL,data,{headers: headers});
  }

  showAppointmentAttention(appointment_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment-attention/"+appointment_id;
    return this.http.get(URL,{headers: headers});
  }
}

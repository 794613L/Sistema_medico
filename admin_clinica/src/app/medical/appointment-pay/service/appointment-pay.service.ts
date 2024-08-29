import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { URL_SERVICIOS } from 'src/app/config/config';
import { AuthService } from 'src/app/shared/auth/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AppointmentPayService {

  constructor(
    public http: HttpClient,
    public authService: AuthService,
  ) { }

  listAppointmentPays(page:number=1,search_doctor:string = '',search_patient:string = '',specialitie_id:string = '',date_start:any = null,date_end:any = null){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let LINK = "";
    if(search_doctor){
      LINK+="&search_doctor="+search_doctor;
    }
    if(search_patient){
      LINK+="&search_patient="+search_patient;
    }
    if(specialitie_id){
      LINK+="&specialitie_id="+specialitie_id;
    }
    if(date_start){
      LINK+="&date_start="+date_start;
    }
    if(date_end){
      LINK+="&date_end="+date_end;
    }
    let URL = URL_SERVICIOS+"/appointment-pay?page="+page+LINK;
    return this.http.get(URL,{headers: headers});
  }

  listConfig(){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment/config";
    return this.http.get(URL,{headers: headers});
  }

  registerAppointmentPay(data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment-pay";
    return this.http.post(URL,data,{headers: headers});
  }

  updateAppointmentPay(appointment_pay_id:string,data:any){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment-pay/"+appointment_pay_id;
    return this.http.put(URL,data,{headers: headers});
  }

  deleteAppointmentPay(appointment_pay_id:string){
    let headers = new HttpHeaders({'Authorization': 'Bearer '+this.authService.token});
    let URL = URL_SERVICIOS+"/appointment-pay/"+appointment_pay_id;
    return this.http.delete(URL,{headers: headers});
  }
}

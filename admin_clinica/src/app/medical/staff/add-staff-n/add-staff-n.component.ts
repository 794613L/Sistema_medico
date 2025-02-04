import { Component } from '@angular/core';
import { StaffService } from '../service/staff.service';

@Component({
  selector: 'app-add-staff-n',
  templateUrl: './add-staff-n.component.html',
  styleUrls: ['./add-staff-n.component.scss']
})
export class AddStaffNComponent {
  
  // Variables públicas para enlazar con el formulario
  public selectedValue !: string ;
  public name:string = '';
  public surname:string = '';
  public mobile:string = '';
  public email:string = '';
  public password:string = '';
  public password_confirmation:string = '';
  
  public birth_date:string = '';
  public gender:number = 1;
  public education:string = '';
  public designation:string = '';
  public address:string = '';

  public roles:any = [];
  
  public FILE_AVATAR:any;
  public IMAGEN_PREVIZUALIZA:any = 'assets/img/user-06.jpg';

  public text_success:string = '';
  public text_validation:string = '';
  constructor(
    public staffService: StaffService,
  ){

  }
  ngOnInit(): void {
    //Called after the constructor, initializing input properties, and the first call to ngOnChanges.
    //Add 'implements OnInit' to the class.
       
    // Inicializar componente y cargar roles desde el servicio
    this.staffService.listConfig().subscribe((resp:any) => {
      console.log(resp);
      this.roles = resp.roles;
    })
  }

  save(){
    // Validación de campos obligatorios y coincidencia de contraseñas
    this.text_validation = '';
    if(!this.name || !this.email || !this.surname || !this.FILE_AVATAR || !this.password){
      this.text_validation = "¡ALTO AHÍ! ASEGÚRATE DE COMPLETAR TU (NAME,SURNAME,EMAIL,AVATAR)";
      return;
    }

    if(this.password != this.password_confirmation){
      this.text_validation = "LAS CONTRASEÑAS NO COINCIDEN. ¡INTENTALO DE NUEVO!";
      return;
    }
    console.log(this.selectedValue);
    
    // Crear objeto FormData con los datos del formulario
    let formData = new FormData();
    formData.append("name",this.name);
    formData.append("surname",this.surname);
    formData.append("email",this.email);
    formData.append("mobile",this.mobile);
    formData.append("birth_date",this.birth_date);
    formData.append("gender",this.gender+"");
    formData.append("education",this.education);
    formData.append("designation",this.designation);
    formData.append("address",this.address);
    formData.append("password",this.password);
    formData.append("role_id",this.selectedValue);
    formData.append("imagen",this.FILE_AVATAR);

    // Llamar al servicio para registrar el usuario
    this.staffService.registerUser(formData).subscribe((resp:any) => {
      console.log(resp);
      
      // Manejo de la respuesta del servicio
      if(resp.message == 403){
        this.text_validation = resp.message_text;
      }else{
        this.text_success = 'TE HAS REGISTRADO CON ÉXITO';
        
        // Resetear los campos del formulario
        this.name = '';
        this.surname = '';
        this.email = '';
        this.mobile = '';
        this.birth_date = '';
        this.gender = 1;
        this.education = '';
        this.designation = '';
        this.address = '';
        this.password = '';
        this.password_confirmation = '';
        this.selectedValue = '';
        this.FILE_AVATAR = null;
        this.IMAGEN_PREVIZUALIZA = null;
      }
    
    })
  }

  // Manejar la carga de archivos y previsualizar la imagen
  loadFile($event:any){
    if($event.target.files[0].type.indexOf("image") < 0){
      // alert("SOLAMENTE PUEDEN SER ARCHIVOS DE TIPO IMAGEN");
      this.text_validation = "SOLAMENTE PUEDEN SER ARCHIVOS DE TIPO IMAGEN";
      return;
    }
    this.text_validation = '';
    this.FILE_AVATAR = $event.target.files[0];
    let reader = new FileReader();
    reader.readAsDataURL(this.FILE_AVATAR);
    reader.onloadend = () => this.IMAGEN_PREVIZUALIZA = reader.result;
  }
}

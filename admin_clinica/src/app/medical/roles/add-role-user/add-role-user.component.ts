import { Component } from '@angular/core';
import { DataService } from 'src/app/shared/data/data.service';
import { RolesService } from '../service/roles.service';

@Component({
  selector: 'app-add-role-user',
  templateUrl: './add-role-user.component.html',
  styleUrls: ['./add-role-user.component.scss']
})
export class AddRoleUserComponent {

  sideBar:any = [];
  name:string = '';
  permissions:any = [];
  valid_form: boolean = false;
  valid_form_success:boolean = false;
  text_validation:any = null;
  constructor(
    public DataService: DataService,
    public RoleService: RolesService,
  ) {

  }
  ngOnInit(): void {
    //Called after the constructor, initializing input properties, and the first call to ngOnChanges.
    //Add 'implements OnInit' to the class.
    
    // Inicializa el sidebar con el menú del DataService
    this.sideBar = this.DataService.sideBar[0].menu;
  }

  addPermission(subMenu:any){
    // Añade o elimina permisos del array permissions
    
    if(subMenu.permision){
      let INDEX = this.permissions.findIndex((item:any) => item == subMenu.permision);
      if(INDEX != -1){
        this.permissions.splice(INDEX,1); // Elimina el permiso si ya existe
      }else{
        this.permissions.push(subMenu.permision); // Añade el permiso si no existe
      }
      console.log(this.permissions); // Muestra los permisos actuales en la consola
    }
  }

  save(){
    this.valid_form = false;

     // Verifica si el nombre y los permisos están completos
    if(!this.name || this.permissions.length ==0 ){
      this.valid_form = true;
      return;
    }

    // Prepara los datos para enviar al servicio
    let data = {
      name: this.name,
      permisions:this.permissions,
    };
    this.valid_form_success = false;
    this.text_validation = null;

    // Llama al servicio para guardar el rol
    this.RoleService.storeRoles(data).subscribe((resp:any) => {
      console.log(resp);
      if(resp.message == 403){
        this.text_validation = resp.message_text; // Muestra el mensaje de error
      }else{

         // Limpia el formulario y muestra éxito
        this.name = '';
        this.permissions = [];
        this.valid_form_success = true;
  
        // Refresca el sidebar
        let SIDE_BAR = this.sideBar;
        this.sideBar = [];
        setTimeout(() =>{
          this.sideBar = SIDE_BAR;
        },50);    
      }
    })
  }
}

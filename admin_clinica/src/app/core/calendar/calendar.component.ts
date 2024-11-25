import { Component } from '@angular/core';

import { routes } from 'src/app/shared/routes/routes';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import dayGridPlugin from '@fullcalendar/daygrid';
import { DataService } from 'src/app/shared/data/data.service';

@Component({
  selector: 'app-calendar',
  templateUrl: './calendar.component.html',
  styleUrls: ['./calendar.component.scss'],
})
export class CalendarComponent {
  public routes = routes;// Establece las rutas para la navegación
  // Propiedad para las opciones del calendario (FullCalendar)
  // Se usa 'any' porque los datos pueden tener diferentes tipos en FullCalenda
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  options: any;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
    // Array para almacenar los eventos a mostrar en el calendario
  // Se usa 'any[]' porque los datos de eventos pueden variar
  events: any[] = [];

  constructor(private data: DataService) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    this.data.getEvents().subscribe((events: any) => {
      this.events = events;
      this.options = { ...this.options, ...{ events: events.data } };
    });
    // Configura las opciones iniciales del calendario
    this.options = {
      plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
      initialDate: new Date(),// La fecha inicial será la fecha actual
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
      },
      initialView: 'dayGridMonth',// Vista inicial del calendario será de mes
      editable: true,// Permite editar eventos en el calendario
      selectable: true,// Permite seleccionar rangos de fechas
      selectMirror: true,// Espejo de selección (cuando se selecciona un rango, se muestra un espejo)
      dayMaxEvents: true, // Limita el número de eventos visibles en un día
      events: [
        { title: 'Meeting', start: new Date() }// Evento predeterminado (por defecto, una reunión con la fecha actual)
      ]
    };
  }
}

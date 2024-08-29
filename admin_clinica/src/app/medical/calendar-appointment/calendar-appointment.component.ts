import { Component } from '@angular/core';

@Component({
  selector: 'app-calendar-appointment',
  templateUrl: './calendar-appointment.component.html',
  styleUrls: ['./calendar-appointment.component.scss']
})
export class CalendarAppointmentComponent {
  calendarAppointment(data: { specialitie_id: string; search_doctor: string; search_patient: string; }) {
    throw new Error('Method not implemented.');
  }

}

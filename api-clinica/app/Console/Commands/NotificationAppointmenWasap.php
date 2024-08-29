<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Appointment\Appointment;

class NotificationAppointmenWasap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-appointment-wasap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'NOTIFICAR AL PACIENTE 1 HORA ANTES DE SU CITA MEDICA, POR MEDIO DE WHATSAP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        date_default_timezone_set('America/Bogota');
        $simulet_hour_number = date("2024-08-12 09:30:35");//strtotime(date("2024-08-12 09:30:35"));
        $appointments = Appointment::whereDate("date_appointment","2024-08-12")//now()->format("Y-m-d"))
                                    ->where("status",1)
                                    ->get();

        $now_time_number = strtotime($simulet_hour_number);//format("Y-m-d h:i:s")
        $patients = collect([]);
        foreach ($appointments as $key => $appointment) {
            $hour_start = $appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start;
            $hour_end = $appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end;
            
            // 2024-10-25 08:30:00 -> 2024-10-25 07:30:00
            $hour_start = strtotime(Carbon::parse(date("Y-m-d")." ".$hour_start)->subHour());
            $hour_end = strtotime(Carbon::parse(date("Y-m-d")." ".$hour_end)->subHour());

            if($hour_start <= $now_time_number && $hour_end >= $now_time_number){
                $patients->push([
                    "name" => $appointment->patient->name,
                    "surname" => $appointment->patient->surname,
                    "avatar" => $appointment->avatar ? env("APP_URL")."storage/".$appointment->avatar : NULL,
                    "email" => $appointment->patient->email,
                    "mobile" => $appointment->patient->mobile,
                    "doctor_full_name" => $appointment->doctor->name.' '.$appointment->doctor->surname,
                    "specialitie_name" => $appointment->specialitie->name,
                    "n_document" => $appointment->patient->n_document,
                    "hour_start_format" => Carbon::parse(date("Y-m-d")." ".$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_start)->format("h:i A"),
                    "hour_end_format" => Carbon::parse(date("Y-m-d")." ".$appointment->doctor_schedule_join_hour->doctor_schedule_hour->hour_end)->format("h:i A"),
            ]);
        }
      }

      foreach ($patients as $key => $patient) {
        $accessToken = 'EAAMR3W3bUvoBO5PiDjRbtzkOONjKliR55KxNTFOxN4hSXJJ6VGAH1QxkVv3s3c2dDev4sdnnnIujCB1BpZAHyPU6R95Myit5xzO2P1WWpkpAZClbgrht1ZCOubID3QIyo7UE9kiZBMKrjcRx9J8wj77GVRudO1QonNgstzIZAJjTZBGFvzq499U1OjMQarXuZB6HWZCM0XMr3UobEoZAcn8oZD';
         
        $fbApiUrl = 'https://graph.facebook.com/v17.0/XXXXXXXXXXXXXXXXX/messages';
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => 'xxxxxxxxxxxxxxx',
            'type' => 'template',
            'template' => [
                'name' => 'recordatorio',
                'language' => [
                    'code' => 'es_MX',
                ],
                "components"=>  [
                    [
                        "type" =>  "header",
                        "parameters"=>  [
                            [
                                "type"=>  "text",
                                "text"=>  $patient["name"].' '.$patient["surname"],
                            ]
                        ]
                    ],
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type"=> "text",
                                "text"=>  $patient["hour_start_format"].' '.$patient["hour_end_format"],
                            ],
                            [
                                "type"=> "text",
                                "text"=>  $patient["doctor_full_name"]
                            ],
                        ] 
                    ],
                ],
            ],
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        
        $ch = curl_init($fbApiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        echo "HTTP Code: $httpCode\n";
        echo "Response:\n$response\n";
        }

          dd($patients);
    }
}

<?php
namespace Database\Seeders;

use App\Models\Room;
use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stationDatas = [
            [
                'name' => 'Register',
                'code' => 'register',
            ],
            [
                'name' => 'Vitalsign',
                'code' => 'vs',
            ],
            [
                'name' => 'Lab',
                'code' => 'lab',
            ],
            [
                'name' => 'EKG',
                'code' => 'ekg',
            ],
            [
                'name' => 'ABI',
                'code' => 'abi',
            ],
            [
                'name' => 'EST|ECHO',
                'code' => 'echo',
            ],
            [
                'name' => 'X-RAY',
                'code' => 'xray',
            ],
            [
                'name' => 'Ultrasound',
                'code' => 'ultrasound',
            ],
            [
                'name' => 'Mammogram',
                'code' => 'mammogram',
            ],
            [
                'name' => 'Bone Density',
                'code' => 'bonedensity',
            ],
            [
                'name' => 'Obstetrician-Gynecologist',
                'code' => 'obs',
            ],
            [
                'name' => 'Doctor',
                'code' => 'doctor',
            ],
        ];

        foreach ($stationDatas as $index => $stationData) {
            Station::create($stationData);
            switch ($stationData['code']) {
                case 'register':
                    $room = 11;
                    break;
                case 'vs':
                    $room = 5;
                    break;
                case 'lab':
                    $room = 5;
                    break;
                case 'ekg':
                    $room = 2;
                    break;
                case 'abi':
                    $room = 1;
                    break;
                case 'echo':
                    $room = 1;
                    break;
                case 'xray':
                    $room = 2;
                    break;
                case 'ultrasound':
                    $room = 6;
                    break;
                case 'mammogram':
                    $room = 2;
                    break;
                case 'bonedensity':
                    $room = 1;
                    break;
                case 'obs':
                    $room = 3;
                    break;
                case 'doctor':
                    $room = 5;
                    break;
            }

            for ($i = 1; $i <= $room; $i++) {
                Room::create([
                    'station_id' => $index + 1,
                    'name'       => $i,
                ]);
            }
        }
    }
}

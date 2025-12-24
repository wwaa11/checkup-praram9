<?php
namespace App\Jobs;

use App\Models\Number;
use App\Models\PatientPreVN;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateNumber implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public function uniqueId(): string
    {
        return 'generate-number';
    }

    public function handle(): void
    {
        $number = Number::firstOrCreate([
            'date' => date('Y-m-d'),
        ]);

        $preVns = PatientPreVN::where('date', date('Y-m-d'))
            ->whereNotNull('checkin')
            ->whereNull('number')
            ->orderBy('checkin', 'asc')
            ->get();

        foreach ($preVns as $prevn) {
            $type         = $prevn->type;
            $set_number   = $number->$type + 1;
            $queue_number = $type . str_pad($set_number, 3, '0', STR_PAD_LEFT);

            $number->$type = $set_number;
            $number->save();

            $prevn->number = $queue_number;
            $prevn->save();
        }

        GenerateNumber::dispatch()->onQueue('generate-number')->delay(1);
    }
}

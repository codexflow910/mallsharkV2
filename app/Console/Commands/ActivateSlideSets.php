<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SlideSet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivateSlideSets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan slides:set-activate
     */
    protected $signature = 'slides:set-activate';

    /**
     * The console command description.
     */
    protected $description = 'Activate slide sets whose start_date is today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();

        DB::transaction(function () use ($today) {
            // All sets that start today (could be multiple)
            $todaySets = SlideSet::whereDate('start_date', $today)->get();

            if ($todaySets->isNotEmpty()) {
                // 1) Expire all sets before today (only if not already expired)
                SlideSet::whereDate('start_date', '<', $today)
                    ->where('status', '!=', 'expired')
                    ->update(['status' => 'expired']);

                // 2) Activate all sets that start today
                SlideSet::whereDate('start_date', $today)
                    ->update(['status' => 'active']);

                SlideSet::whereDate('start_date', '>', $today)
                    ->update(['status' => 'inactive']);

                $this->info("Activated {$todaySets->count()} set(s) for today ($today) and expired previous sets.");

            } else {
                $lastSet = SlideSet::whereDate('start_date', '<', $today)
                    ->orderByDesc('start_date')
                    ->first();
                    SlideSet::whereDate('start_date', '>', $today)
                    ->update(['status' => 'inactive']);
                if ($lastSet && $lastSet->status !== 'active') {
                    $lastSet->status = 'active';
                    $lastSet->save();

                    $this->info("Fallback activated previous slide set ID: {$lastSet->id} ({$lastSet->name})");
                }

                if ($lastSet) {
                    // Expire all earlier sets except the last one
                    SlideSet::whereDate('start_date', '<', $today)
                        ->where('id', '!=', $lastSet->id)
                        ->where('status', '!=', 'expired')
                        ->update(['status' => 'expired']);

                    // Ensure the last one is active
                    $lastSet->update(['status' => 'active']);

                    $this->info("No set for today. Kept last set ID {$lastSet->id} ({$lastSet->name}) active and expired older sets.");
                } else {
                    // No previous sets at all
                    $this->info("No slide sets scheduled for today and no previous sets found.");
                }
            }
        }, 3); // retries if deadlock

        return 0;
    }
}
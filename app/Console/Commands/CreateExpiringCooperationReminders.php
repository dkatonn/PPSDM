<?php

namespace App\Console\Commands;

use App\Models\Kerjasama;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class CreateExpiringCooperationReminders extends Command
{
    protected $signature = 'sipena:remind-expiring';
    protected $description = 'Create in-app reminders for cooperation agreements approaching their end date.';

    public function handle(): int
    {
        $months = max(1, (int) config('sipena.reminder_months', 6));
        $reminderDate = today()->addMonthsNoOverflow($months);
        $reminderDateValue = $reminderDate->toDateString();
        $agreements = Kerjasama::with('ptn')->where('status', 'aktif')->whereDate('ends_at', $reminderDate)->get();
        $users = User::where('is_active', true)->get();
        $created = 0;

        foreach ($agreements as $agreement) {
            foreach ($users as $user) {
                $exists = Notifikasi::query()->where('user_id', $user->id)->where('kerjasama_id', $agreement->id)->whereDate('reminder_on', $reminderDateValue)->exists();
                if ($exists) continue;

                try {
                    Notifikasi::create([
                        'user_id' => $user->id,
                        'kerjasama_id' => $agreement->id,
                        'reminder_on' => $reminderDateValue,
                        'title' => 'Kerja sama segera berakhir',
                        'message' => $agreement->title.' dengan '.$agreement->ptn->name.' berakhir pada '.$agreement->ends_at->format('d/m/Y').'.',
                    ]);
                    $created++;
                } catch (QueryException $exception) {
                    if (! Notifikasi::query()->where('user_id', $user->id)->where('kerjasama_id', $agreement->id)->whereDate('reminder_on', $reminderDateValue)->exists()) throw $exception;
                }
            }
        }

        $this->info("Processed {$agreements->count()} agreements; checked {$created} user reminders.");
        return self::SUCCESS;
    }
}

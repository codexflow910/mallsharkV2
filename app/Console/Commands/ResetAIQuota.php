<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetAIQuota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:reset-quota';
    protected $description = 'Reset AI token usage for all stores';

    public function handle()
    {
        Store::where('ai_enabled', true)->update([
            'ai_tokens_used' => 0,
            'ai_tokens_remaining' => 5000,
            'ai_quota_reset_at' => now()
        ]);

        $this->info('AI quotas have been reset.');
    }
}

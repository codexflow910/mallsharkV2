<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\MailgunController;


class SendOrderEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        try {
            $mailgun = new MailgunController();
            
            $mailgun->SendMailgunMail($this->params);

        } catch (\Throwable $e) {
            throw $e;
        }
    }
}

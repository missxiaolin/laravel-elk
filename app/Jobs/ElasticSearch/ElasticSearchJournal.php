<?php

namespace App\Jobs\ElasticSearch;

use App\Support\ElasticSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ElasticSearchJournal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $logName;

    protected $context;

    /**
     * Create a new job instance.
     * @param $loggerName
     * @param $body
     *
     * @return void
     */
    public function __construct($loggerName, $body)
    {
        $this->logName = $loggerName;
        $this->context = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $el = new ElasticSearch($this->logName);
        $el->sync($this->context);
    }
}

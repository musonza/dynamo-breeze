<?php

namespace Musonza\DynamoBreeze\Commands;

use Illuminate\Console\Command;

class DynamoAccessPatterns extends Command
{
    protected $signature = 'dynamo-breeze:patterns';
    protected $description = 'Print out all DynamoDB access patterns for debugging purposes.';

    public function handle()
    {
        $config = config('dynamo-breeze.tables');
    
        foreach ($config as $table => $settings) {
            $this->info("Table: $table");
    
            $headers = ['Pattern', 'Details'];
    
            $rows = [];
            foreach ($settings['access_patterns'] as $pattern => $details) {
                $rows[] = [$pattern, json_encode($details, JSON_PRETTY_PRINT)];
            }
    
            $this->table($headers, $rows);
        }
    }
}

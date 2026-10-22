<?php

use App\Services\AgriculturalAiService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ai:gemini-check', function (AgriculturalAiService $assistant) {
    $result = $assistant->checkConnection();
    $status = $result['status'] === null ? 'unavailable' : 'HTTP '.$result['status'];
    $this->line('Gemini check: '.$status.' — '.$result['outcome']);

    return $result['outcome'] === 'success' ? self::SUCCESS : self::FAILURE;
})->purpose('Test Gemini connectivity without displaying credentials');

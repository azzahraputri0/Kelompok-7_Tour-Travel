<?php

use App\Entities\Pemesanan_Entity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('travel:expire-bookings', function () {
    $this->info('Expired: '.app(Pemesanan_Entity::class)->expire());
});
Schedule::command('travel:expire-bookings')->everyMinute()->withoutOverlapping();

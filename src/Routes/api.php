<?php

Route::group(['prefix' => 'api',  'middleware' => 'api'], function() {
    Route::post('cryptomus/webhook', '\Kristof202\Cryptomus\Http\Controllers\WebhookController')->name('cryptomus-webhook');
});

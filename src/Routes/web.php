<?php

Route::group(
    ['middleware' => ['web', 'system']], function () {  
        // Route::group(['middleware' => ['audit-analytics']], function () {
        Route::prefix('audit')->group(
            function () {

                Route::group(
                    ['as' => 'audit.'], function () {
                        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
                    }
                );
        
            }
        );
    }
);
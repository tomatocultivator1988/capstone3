<?php

/**
 * Application Routes Configuration
 * 
 * This file defines all the routes for the Exam Management System
 * using the Controller@method format for proper MVC routing.
 */

return [
    'GET' => [
        '/' => 'App\Controller\AuthController@showLogin',
        '/login' => 'App\Controller\AuthController@showLogin',
        '/dashboard' => 'App\Controller\DashboardController@showDashboard',
        '/logout' => 'App\Controller\AuthController@logout',
    ],
    
    'POST' => [
        '/api/auth/login' => 'App\Controller\AuthController@login',
        '/api/auth/logout' => 'App\Controller\AuthController@logout',
    ]
];
?>
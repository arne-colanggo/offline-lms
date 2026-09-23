<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Api\AuthController::loginform');
// app/Config/Routes.php

$routes->group('admin', function ($routes) {

    // =========================
    // PUBLIC ROUTES
    // =========================
    $routes->post('login', 'Api\AuthController::loginhandler', ['as' => 'admin.login.handler']);
    $routes->get('unauthorized', 'Api\AuthController::unauthorized');

    // =========================
    // AUTHENTICATED ROUTES
    // =========================
    $routes->group('', ['filter' => 'auth'], function ($routes) {

        $routes->get('profile', 'Api\UserController::userProfile', ['as' => 'user.profile']);
        $routes->get('logout', 'Api\AuthController::logout', ['as' => 'admin.logout']);
        $routes->get('dashboard', 'Home::index', ['as' => 'admin.dashboard']);

        // Users
        $routes->get('users', 'Api\UserController::index');
        $routes->get('users/(:num)', 'Api\UserController::show/$1');

        // Courses
        $routes->get('courses', 'Api\CourseController::index');

        // Lessons
        $routes->get('lessons', 'Api\LessonController::index');

        // Quizzes
        $routes->get('quizzes', 'Api\QuizController::index');
    });
});

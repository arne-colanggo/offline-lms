<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');
// app/Config/Routes.php

$routes->group('api', function ($routes) {

    // =========================
    // PUBLIC ROUTES
    // =========================
    $routes->post('login', 'Api\AuthController::login');


    // =========================
    // AUTHENTICATED ROUTES
    // =========================
    $routes->group('', ['filter' => 'jwt'], function ($routes) {

        $routes->get('profile', 'Api\AuthController::profile');
        $routes->post('logout', 'Api\AuthController::logout');

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

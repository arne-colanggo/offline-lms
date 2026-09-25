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
        $routes->post('post-update-profile', 'Api\UserController::updateProfile', ['as' => 'post.update.profile']);
        $routes->post('post-update-profile-picture', 'Api\UserController::updateProfilePicture', ['as' => 'post.update.profilepicture']);
        $routes->post('change-credentials', 'Api\UserController::changeCredentials', ['as' => 'post.user.change.credentials']);

        $routes->get('settings', 'Api\UserController::settings', ['as' => 'settings']);

        // Address Controller

        $routes->get('regions', 'Api\AddressController::regions', ['as' => 'get.regions']);
        $routes->get('provinces/(:num)', 'Api\AddressController::provinces/$1');
        $routes->get('municipalities/(:num)', 'Api\AddressController::municipalities/$1');
        $routes->get('barangays/(:num)', 'Api\AddressController::barangays/$1');

        // Courses
        $routes->get('courses', 'Api\CourseController::index');

        // Lessons
        $routes->get('lessons', 'Api\LessonController::index');

        // Quizzes
        $routes->get('quizzes', 'Api\QuizController::index');
    });
});

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
        $routes->post('post-update-settings', 'Api\AdminController::updateGeneralSettings', ['as' => 'post.update.settings']);
        $routes->post('update-logo', 'Api\AdminController::updateLogo', ['as' => 'update.logo']);
        $routes->post('update-favicon', 'Api\AdminController::updateFavicon', ['as' => 'update.favicon']);
        $routes->get('student-list', 'Api\StudentController::studentsList', ['as' => 'student.list']);
        $routes->get('student', 'Api\StudentController::viewStudent', ['as' => 'student']);
        $routes->get('student-profile', 'Api\StudentController::student', ['as' => 'student.profile']);

        // Student

        $routes->get('student-profile', 'Api\StudentController::student', ['as' => 'student.profile']);
        $routes->post('post-student-profile', 'Api\StudentController::postStudent', ['as' => 'post.student.profile']);
        $routes->get('upload-sf1', 'Api\StudentController::uploadSF1', ['as' => 'upload.sf1']);
        $routes->post('post-upload-sf1', 'Api\StudentController::postUploadSF1', ['as' => 'post.upload.sf1']);
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

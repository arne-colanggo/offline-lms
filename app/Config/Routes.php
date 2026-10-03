<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Api\AuthController::loginform');
$routes->get('unauthorized', 'Api\AuthController::unauthorized');

// app/Config/Routes.php

$routes->group('admin', function ($routes) {

    // =========================
    // PUBLIC ROUTES
    // =========================
    $routes->post('login', 'Api\AuthController::loginhandler', ['as' => 'admin.login.handler']);

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
        //School Year ---
        $routes->post('post-school-year', 'Api\AdminController::postSchoolYear', ['as' => 'post.schoolyear']);
        $routes->get('get-cboschoolyear', 'Api\AdminController::getcboSchoolYear', ['as' => 'get.cboschoolyear']);
        $routes->get('get-schoolyear', 'Api\AdminController::getSchoolYear', ['as' => 'get.schoolyear']);
        $routes->get('schoolyear', 'Api\AdminController::schoolyear', ['as' => 'schoolyear']);
        $routes->get('schoolyears', 'Api\AdminController::getSchoolYears', ['as' => 'get.schoolyears']);
        $routes->post('update-schoolyear', 'Api\AdminController::updateSchoolYear', ['as' => 'update.schoolyear']);
        $routes->get('delete-schoolyear', 'Api\AdminController::deleteSchoolYear', ['as' => 'delete.schoolyear']);
        //Grade Level ---
        $routes->get('gradelevel', 'Api\AdminController::gradelevel', ['as' => 'gradelevel']);
        $routes->get('get-gradelevels', 'Api\AdminController::getGradeLevels', ['as' => 'get.gradelevels']);
        $routes->post('post-gradelevels', 'Api\AdminController::postGradeLevel', ['as' => 'post.gradelevel']);
        $routes->get('get-gradelevel', 'Api\AdminController::getGradeLevel', ['as' => 'get.gradelevel']);
        $routes->post('update-gradelevel', 'Api\AdminController::updateGradeLevel', ['as' => 'update.gradelevel']);
        $routes->get('delete-gradelevel', 'Api\AdminController::deleteGradeLevel', ['as' => 'delete.gradelevel']);

        //Section ---



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

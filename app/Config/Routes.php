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
        $routes->get('dashboard', 'Api\AdminController::index', ['as' => 'admin.dashboard']);

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
        $routes->get('get-parent-gradelevel', 'Api\AdminController::getParentGradeLevel', ['as' => 'get.parent.gradelevel']);
        $routes->post('update-section', 'Api\AdminController::updateSection', ['as' => 'update.section']);
        $routes->get('delete-section', 'Api\AdminController::deleteSection', ['as' => 'delete.section']);

        //Section ---
        $routes->get('section', 'Api\AdminController::section', ['as' => 'section']);
        $routes->get('get-sections', 'Api\AdminController::getSections', ['as' => 'get.sections']);
        $routes->post('post-section', 'Api\AdminController::postSection', ['as' => 'post.section']);
        $routes->get('get-section', 'Api\AdminController::getSection', ['as' => 'get.section']);
        $routes->get('view-enrolled-students', 'Api\AdminController::viewEnrolledStudents', ['as' => 'view.enrolled.students']);
        $routes->post('post-single-enrollment', 'Api\StudentController::postSingleEnrollment', ['as' => 'post.single.enrollment']);
        $routes->get('get-enrolled-students', 'Api\StudentController::getEnrolledStudents', ['as' => 'get.enrolled.students']);
        $routes->get('section-dashboard', 'Api\AdminController::sectionDashboard', ['as' => 'section.dashboard']);
        //Subject
        $routes->get('subject', 'Api\AdminController::subject', ['as' => 'subject']);
        $routes->get('get-subjects', 'Api\AdminController::getSubjects', ['as' => 'get.subjects']);
        $routes->post('post-subject', 'Api\AdminController::postSubject', ['as' => 'post.subject']);
        $routes->get('get-subject', 'Api\AdminController::getSubject', ['as' => 'get.subject']);
        $routes->post('update-subject', 'Api\AdminController::updateSubject', ['as' => 'update.subject']);
        // Student

        $routes->get('student-list', 'Api\StudentController::studentsList', ['as' => 'student.list']);
        $routes->get('student', 'Api\StudentController::viewStudent', ['as' => 'student']);
        $routes->get('student-profile', 'Api\StudentController::student', ['as' => 'student.profile']);
        $routes->post('post-student-profile', 'Api\StudentController::postStudent', ['as' => 'post.student.profile']);
        $routes->get('upload-sf1', 'Api\StudentController::uploadSF1', ['as' => 'upload.sf1']);
        $routes->post('post-upload-sf1', 'Api\StudentController::postUploadSF1', ['as' => 'post.upload.sf1']);
        $routes->get('batch-enroll-student-section', 'Api\StudentController::batchEnrollStudentSection', ['as' => 'batch.enroll.student.section']);
        $routes->post('post-batch-enroll-student-section', 'Api\StudentController::postBatchEnrollStudentSections', ['as' => 'post.batch.enroll.student.section']);
        $routes->get('upload-student-attendance-section', 'Api\StudentController::uploadAttendanceBySection', ['as' => 'upload.student.attendance.section']);
        $routes->post('upload-attendance', 'Api\AdminController::uploadAttendance', ['as' => 'post.upload.attendance']);
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

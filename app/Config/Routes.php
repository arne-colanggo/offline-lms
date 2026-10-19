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
        $routes->get('teachers', 'Api\AdminController::teachersList', ['as' => 'teachers.list']);


        //Subject
        $routes->get('subject', 'Api\AdminController::subject', ['as' => 'subject']);
        $routes->get('get-subjects', 'Api\AdminController::getSubjects', ['as' => 'get.subjects']);
        $routes->post('post-subject', 'Api\AdminController::postSubject', ['as' => 'post.subject']);
        $routes->get('get-subject', 'Api\AdminController::getSubject', ['as' => 'get.subject']);
        $routes->post('update-subject', 'Api\AdminController::updateSubject', ['as' => 'update.subject']);
        // Student

        $routes->get('student-list', 'Api\StudentController::studentsList', ['as' => 'student.list']);
        $routes->get('student', 'Api\StudentController::viewStudent', ['as' => 'students']);
        $routes->get('student-profile', 'Api\StudentController::student', ['as' => 'student.profile']);
        $routes->post('post-student-profile', 'Api\StudentController::postStudent', ['as' => 'post.student.profile']);
        $routes->get('upload-sf1', 'Api\StudentController::uploadSF1', ['as' => 'upload.sf1']);
        $routes->post('post-upload-sf1', 'Api\StudentController::postUploadSF1', ['as' => 'post.upload.sf1']);
        $routes->get('batch-enroll-student-section', 'Api\StudentController::batchEnrollStudentSection', ['as' => 'batch.enroll.student.section']);
        $routes->post('post-batch-enroll-student-section', 'Api\StudentController::postBatchEnrollStudentSections', ['as' => 'post.batch.enroll.student.section']);
        $routes->get('upload-student-attendance-section', 'Api\StudentController::uploadAttendanceBySection', ['as' => 'upload.student.attendance.section']);
        $routes->post('upload-attendance', 'Api\AdminController::uploadAttendance', ['as' => 'post.upload.attendance']);

        //Class Controller
        $routes->get('classes', 'Api\ClassController::index', ['as' => 'classes']);
        $routes->get('grade-level-subjects', 'Api\ClassController::gradelevelSubjects', ['as' => 'grade.level.subjects']);
        $routes->post('class-store', 'Api\ClassController::store', ['as' => 'class.store']);
        $routes->post('class-sync/(:num)', 'Api\ClassController::sync/$1');
        $routes->get('view-class-details', 'Api\ClassController::viewClassDetails', ['as' => 'view.class.details']);
        $routes->post('update-class', 'Api\ClassController::postUpdateClass', ['as' => 'update.class']);
        $routes->get('export-eclass', 'Api\ClassController::exportEClass', ['as' => 'export.eclass']);
        $routes->get('wheelofname', 'Api\ClassController::wheelOfNames', ['as' => 'wheel.names']);
        // Address Controller
        $routes->get('regions', 'Api\AddressController::regions', ['as' => 'get.regions']);
        $routes->get('provinces/(:num)', 'Api\AddressController::provinces/$1');
        $routes->get('municipalities/(:num)', 'Api\AddressController::municipalities/$1');
        $routes->get('barangays/(:num)', 'Api\AddressController::barangays/$1');


        //Budget of Works
        $routes->get('budget-of-work', 'Api\BowController::index', ['as' => 'budgetofwork']);
        $routes->get('budget-of-work/view', 'Api\BowController::view', ['as' => 'bow.view']);
        $routes->post('budget-of-work/save', 'Api\BowController::save', ['as' => 'post.bow']);
        $routes->post('budget-of-work/delete', 'Api\BowController::delete', ['as' => 'delete.bow']);

        $routes->post('bow-detail/save', 'Api\BowController::saveDetail', ['as' => 'post.bow.detail']);
        $routes->post('bow-detail/delete', 'Api\BowController::deleteDetail', ['as' => 'delete.bow.detail']);

        $routes->post('bow-resource/upload', 'Api\BowController::uploadResource', ['as' => 'post.bow.resource']);
        $routes->post('bow-resource/delete', 'Api\BowController::deleteResource', ['as' => 'delete.bow.resource']);
        $routes->get('bow-resource/download', 'Api\BowController::download', ['as' => 'bow.resource.download']);


        //Performance Task==================
        $routes->get('rubrics', 'Api\PerformanceTaskController::rubrics', ['as' => 'get.rubrics']);
        $routes->get('rubric', 'Api\PerformanceTaskController::rubricForm', ['as' => 'get.rubric.new']);
        $routes->get('rubric/(:num)', 'Api\PerformanceTaskController::rubricForm/$1', ['as' => 'get.rubric.edit']);
        $routes->post('rubric', 'Api\PerformanceTaskController::saveRubric', ['as' => 'post.rubric']);
        $routes->post('rubric/(:num)/delete', 'Api\PerformanceTaskController::deleteRubric/$1', ['as' => 'post.rubric.delete']);

        // Performance tasks
        $routes->get('performance-tasks', 'Api\PerformanceTaskController::index', ['as' => 'get.performancetasks']);
        $routes->get('performance-task', 'Api\PerformanceTaskController::taskForm', ['as' => 'get.performancetask.new']);
        $routes->get('performance-task/(:num)', 'Api\PerformanceTaskController::taskForm/$1', ['as' => 'get.performancetask.edit']);
        $routes->post('performance-task', 'Api\PerformanceTaskController::saveTask', ['as' => 'post.performancetask']);
        $routes->post('performance-task/(:num)/delete', 'Api\PerformanceTaskController::deleteTask/$1', ['as' => 'post.performancetask.delete']);

        // Scoring
        $routes->get('performance-task/(:num)/score', 'Api\PerformanceTaskController::scoreForm/$1', ['as' => 'get.performancetask.score']);
        $routes->get('performance-task/(:num)/student/(:segment)', 'Api\PerformanceTaskController::studentScore/$1/$2', ['as' => 'get.performancetask.student']);
        $routes->post('performance-task/(:num)/score', 'Api\PerformanceTaskController::saveScore/$1', ['as' => 'post.performancetask.score']);

        // Courses
        $routes->get('courses', 'Api\CourseController::index');

        // Lessons
        $routes->get('lessons', 'Api\LessonController::index');

        //Teacher
        $routes->get('teacher-home', 'Api\TeacherController::index', ['as' => 'teacher.home']);

        // $routes->group('quiz-module', static function ($routes) {

        //     /* ---------- Class-scoped (admin + the teacher assigned to the class) ---------- */
        //     $routes->get('class/(:num)/questions', 'Api\ClassQuizController::classQuestions/$1', ['as' => 'get.class.questions']);
        //     $routes->get('class/(:num)/questions/list', 'Api\ClassQuizController::classQuestionList/$1', ['as' => 'get.class.questions.list']);
        //     $routes->get('class/(:num)/question/(:num)', 'Api\ClassQuizController::classQuestionGet/$1/$2', ['as' => 'get.class.question']);
        //     $routes->post('class/(:num)/question/save', 'Api\ClassQuizController::classQuestionSave/$1', ['as' => 'post.class.question']);
        //     $routes->post('class/(:num)/question/delete/(:num)', 'Api\ClassQuizController::classQuestionDelete/$1/$2', ['as' => 'delete.class.question']);

        //     $routes->get('class/(:num)/quizzes', 'Api\ClassQuizController::classQuizzes/$1', ['as' => 'get.class.quizzes']);
        //     $routes->post('class/(:num)/quiz/save', 'Api\ClassQuizController::classQuizSave/$1', ['as' => 'post.class.quiz']);
        //     $routes->get('class/(:num)/quiz/(:num)/builder', 'Api\ClassQuizController::classBuilder/$1/$2', ['as' => 'get.class.quiz.builder']);
        //     $routes->post('class/(:num)/quiz/(:num)/sync', 'Api\ClassQuizController::classQuizSync/$1/$2', ['as' => 'post.class.quiz.sync']);
        //     $routes->post('class/(:num)/quiz/delete/(:num)', 'Api\ClassQuizController::classQuizDelete/$1/$2', ['as' => 'delete.class.quiz']);

        //     /* ---------- Admin-wide Question Bank / Quizzes (admin only) ---------- */
        //     $routes->get('questions', 'Api\QuizController::questions', ['as' => 'get.questions']);
        //     $routes->get('questions/list', 'Api\QuizController::questionList', ['as' => 'get.questions.list']);
        //     $routes->get('question/(:num)', 'Api\QuizController::questionGet/$1', ['as' => 'get.question']);
        //     $routes->post('question/save', 'Api\QuizController::questionSave', ['as' => 'post.question']);
        //     $routes->post('question/delete/(:num)', 'Api\QuizController::questionDelete/$1', ['as' => 'delete.question']);

        //     $routes->get('quizzes', 'Api\QuizController::quizzes', ['as' => 'get.quizzes']);
        //     $routes->post('quiz/save', 'Api\QuizController::quizSave', ['as' => 'post.quiz']);
        //     $routes->get('quiz/(:num)/builder', 'Api\QuizController::builder/$1', ['as' => 'get.quiz.builder']);
        //     $routes->post('quiz/(:num)/sync', 'Api\QuizController::quizSync/$1', ['as' => 'post.quiz.sync']);
        //     $routes->post('quiz/delete/(:num)', 'Api\QuizController::quizDelete/$1', ['as' => 'delete.quiz']);
        // });
        $routes->group('quiz-module', static function ($routes) {

            /* ---------- Class-scoped (admin + the teacher assigned to the class) ---------- */
            $routes->get('class/(:num)/questions', 'Api\ClassQuizController::classQuestions/$1', ['as' => 'get.class.questions']);
            $routes->get('class/(:num)/questions/list', 'Api\ClassQuizController::classQuestionList/$1', ['as' => 'get.class.questions.list']);
            $routes->get('class/(:num)/question/(:num)', 'Api\ClassQuizController::classQuestionGet/$1/$2', ['as' => 'get.class.question']);
            $routes->post('class/(:num)/question/save', 'Api\ClassQuizController::classQuestionSave/$1', ['as' => 'post.class.question']);
            $routes->post('class/(:num)/question/delete/(:num)', 'Api\ClassQuizController::classQuestionDelete/$1/$2', ['as' => 'delete.class.question']);

            $routes->get('class/(:num)/quizzes', 'Api\ClassQuizController::classQuizzes/$1', ['as' => 'get.class.quizzes']);
            $routes->post('class/(:num)/quiz/save', 'Api\ClassQuizController::classQuizSave/$1', ['as' => 'post.class.quiz']);
            $routes->get('class/(:num)/quiz/(:num)/builder', 'Api\ClassQuizController::classBuilder/$1/$2', ['as' => 'get.class.quiz.builder']);
            $routes->post('class/(:num)/quiz/(:num)/sync', 'Api\ClassQuizController::classQuizSync/$1/$2', ['as' => 'post.class.quiz.sync']);
            $routes->post('class/(:num)/quiz/delete/(:num)', 'Api\ClassQuizController::classQuizDelete/$1/$2', ['as' => 'delete.class.quiz']);

            /* ---------- Live monitoring (teacher of the class / admin) ---------- */
            $routes->get('class/(:num)/quiz/(:num)/monitor', 'Api\ClassQuizController::classMonitor/$1/$2', ['as' => 'get.class.quiz.monitor']);
            $routes->get('class/(:num)/quiz/(:num)/monitor/data', 'Api\ClassQuizController::classMonitorData/$1/$2', ['as' => 'get.class.quiz.monitor.data']);
            $routes->get('class/(:num)/quiz/(:num)/attempt/(:num)', 'Api\ClassQuizController::classAttempt/$1/$2/$3', ['as' => 'get.class.quiz.attempt']);
            $routes->post('class/(:num)/quiz/(:num)/attempt/(:num)/reset', 'Api\ClassQuizController::classAttemptReset/$1/$2/$3', ['as' => 'post.class.quiz.attempt.reset']);
            $routes->post('class/(:num)/quiz/(:num)/attempt/(:num)/submit', 'Api\ClassQuizController::classAttemptSubmit/$1/$2/$3', ['as' => 'post.class.quiz.attempt.submit']);
            $routes->post('class/(:num)/quiz/(:num)/attempt/(:num)/grade', 'Api\ClassQuizController::classAttemptGrade/$1/$2/$3', ['as' => 'post.class.quiz.attempt.grade']);

            /* ---------- Admin-wide Question Bank / Quizzes (admin only) ---------- */
            $routes->get('questions', 'Api\QuizController::questions', ['as' => 'get.questions']);
            $routes->get('questions/list', 'Api\QuizController::questionList', ['as' => 'get.questions.list']);
            $routes->get('question/(:num)', 'Api\QuizController::questionGet/$1', ['as' => 'get.question']);
            $routes->post('question/save', 'Api\QuizController::questionSave', ['as' => 'post.question']);
            $routes->post('question/delete/(:num)', 'Api\QuizController::questionDelete/$1', ['as' => 'delete.question']);

            $routes->get('quizzes', 'Api\QuizController::quizzes', ['as' => 'get.quizzes']);
            $routes->post('quiz/save', 'Api\QuizController::quizSave', ['as' => 'post.quiz']);
            $routes->get('quiz/(:num)/builder', 'Api\QuizController::builder/$1', ['as' => 'get.quiz.builder']);
            $routes->post('quiz/(:num)/sync', 'Api\QuizController::quizSync/$1', ['as' => 'post.quiz.sync']);
            $routes->post('quiz/delete/(:num)', 'Api\QuizController::quizDelete/$1', ['as' => 'delete.quiz']);
        });


    });
});

$routes->group('student', static function ($routes) {
    $routes->group('', ['filter' => 'auth'], static function ($routes) {
        $routes->get('student', 'Api\StudentController::index', ['as' => 'student.home']);
        $routes->get('current-enrolled-classes', 'Api\StudentController::currentEnrolledClasses', ['as' => 'current-enrolled-classese']);
        $routes->get('get-current-class', 'Api\StudentController::getCurrentClass', ['as' => 'get-current-class']);
        $routes->get('active-exams', 'Api\StudentController::activeExams', ['as' => 'active-exams']);
        $routes->get('get-active-exams', 'Api\StudentController::getActiveExams', ['as' => 'get-active-exams']);
        $routes->get('get-topics', 'Api\StudentController::getTopics', ['as' => 'get-topics']);
        $routes->get('view-topics', 'Api\StudentController::viewTopics', ['as' => 'view-topics']);

        $routes->get('take-exams', 'Api\StudentController::takeExams', ['as' => 'take-exams']);
        $routes->post('post-answer-on-quizzes', 'Api\StudentController::postAnswerOnQuizzes', ['as' => 'post-answer-on-quizzes']);
        $routes->get('submit-all-and-finished', 'Api\StudentController::quizSubmit', ['as' => 'submit-all-and-finished']);
        $routes->post('post-submit-exam', 'Api\StudentController::submitExamAnswers', ['as' => 'post-submit-exam']);
        $routes->get('get-options-exam', 'Api\AdminController::profile', ['as' => 'get-options-exam']);
        $routes->get('profile', 'Api\AdminController::studentProfile', ['as' => 'student.profile']);
        $routes->get('update-student-password', 'Api\AdminController::updateStudentPassword', ['as' => 'update-student-password']);
        $routes->get('classrooms', 'Api\AdminController::classroom', ['as' => 'classrooms']);
        $routes->get('view-learning-resources', 'Api\StudentController::viewlearningResources', ['as' => 'view-learning-resources']);

        $routes->get('view-my-performance-task', 'Api\StudentController::viewPerformanceTask', ['as' => 'view-my-performance-task']);

        $routes->get('get-my-performance-task', 'Api\StudentController::getMyPerformanceTask', ['as' => 'get-my-performance-task']);
        $routes->get('view-my-performance-tast-details', 'Api\StudentController::viewMyPerformanceTaskDetails', ['as' => 'view-my-performance-tast-details']);

        $routes->get('view-learning-materials', 'Api\StudentController::viewLearningMaterials', ['as' => 'view-learning-materials']);

        $routes->get('get-learning-materials', 'Api\StudentController::getLearningMaterials', ['as' => 'get-learning-materials']);

        $routes->get('submit-violation', 'Api\StudentController::submitViolation', ['as' => 'submit-violation']);
        $routes->get('get-violation-status', 'Api\StudentController::getViolationStatus', ['as' => 'get-violation-status']);
        $routes->get('get-student-answer-on-question', 'Api\StudentController::getStudentAnswerOnQuestion', ['as' => 'get-student-answer-on-question']);
        $routes->get('check-internet-connection', 'Api\StudentController::checkInternetConnection', ['as' => 'check-internet-connection']);
        $routes->get('checktimeremaining', 'Api\StudentController::getTimeRemaining', ['as' => 'checktimeremaining']);

        /* ---------- Students taking quizzes (role: student) ---------- */
        $routes->group('student-quiz', static function ($routes) {
            $routes->get('list', 'Api\StudentQuizController::index', ['as' => 'student.quizzes']);
            $routes->get('(:num)', 'Api\StudentQuizController::intro/$1', ['as' => 'student.quiz.intro']);
            $routes->get('(:num)/take', 'Api\StudentQuizController::take/$1', ['as' => 'student.quiz.take']);
            $routes->post('(:num)/start', 'Api\StudentQuizController::start/$1', ['as' => 'student.quiz.start']);
            $routes->get('(:num)/question/(:num)', 'Api\StudentQuizController::question/$1/$2', ['as' => 'student.quiz.question']);
            $routes->post('(:num)/answer', 'Api\StudentQuizController::answer/$1', ['as' => 'student.quiz.answer']);
            $routes->post('(:num)/event', 'Api\StudentQuizController::event/$1', ['as' => 'student.quiz.event']);
            $routes->post('(:num)/heartbeat', 'Api\StudentQuizController::heartbeat/$1', ['as' => 'student.quiz.heartbeat']);
            $routes->post('(:num)/submit', 'Api\StudentQuizController::submit/$1', ['as' => 'student.quiz.submit']);
        });
    });
});

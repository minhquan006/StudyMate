<?php
// Synthetic fixtures only. Never run on a shared/demo/production database.
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/database/seed.php';
if (PHP_SAPI !== 'cli' || !str_ends_with($config['db_name'], '_test')) exit("Requires a disposable _test database.\n");
$student = one('SELECT * FROM users WHERE email=?', ['student1@studymate.test']);
$instructor = one('SELECT * FROM users WHERE email=?', ['instructor1@studymate.test']);
if (val("SELECT COUNT(*) FROM courses WHERE title LIKE 'AN QA %'")) exit("Fixture already exists; use a fresh project for a fresh acceptance run.\n");
transaction(function () use ($student, $instructor) {
    $stamp = ['created_at' => now(), 'updated_at' => now()];
    for ($i = 1; $i <= 13; $i++) {
        $id = insertRow('courses', ['instructor_id' => $instructor['id'], 'category_id' => 1,
            'title' => 'AN QA ' . $i . ' ' . str_repeat('long-content-', 7),
            'description' => 'Synthetic pagination and responsive fixture; not course acceptance content.',
            'cover_key' => 'web', 'status' => 'published'] + $stamp);
        insertRow('enrollments', ['user_id' => $student['id'], 'course_id' => $id,
            'last_lesson_id' => null, 'completed_at' => $i === 1 ? now() : null] + $stamp);
        $uid = insertRow('users', ['name' => 'AN QA Student ' . $i, 'email' => "an-ui-$i@studymate.test",
            'password' => $student['password'], 'role' => 'student', 'is_active' => 1, 'auth_version' => 1] + $stamp);
        insertRow('enrollments', ['user_id' => $uid, 'course_id' => 2, 'last_lesson_id' => null, 'completed_at' => null] + $stamp);
    }
});
echo json_encode(['users' => val('SELECT COUNT(*) FROM users'), 'courses' => val('SELECT COUNT(*) FROM courses'),
    'studentCourses' => val('SELECT COUNT(*) FROM enrollments WHERE user_id=?', [$student['id']]),
    'instructorCourses' => val('SELECT COUNT(*) FROM courses WHERE instructor_id=?', [$instructor['id']])]) . "\n";

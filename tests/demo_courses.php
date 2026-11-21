<?php
// Run after setup and tests/run.php on a disposable database only.
if (PHP_SAPI !== 'cli' || getenv('STUDYMATE_DISPOSABLE_DB') !== '1') {
    fwrite(STDERR, "Cần CLI và STUDYMATE_DISPOSABLE_DB=1 trên DB kiểm thử riêng.\n");
    exit(2);
}

require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/database/seed.php';
require dirname(__DIR__).'/database/seed_demo_courses.php';
require dirname(__DIR__).'/app/services.php';

$checks = 0;
function demoCheck(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL: '.$message);
    }
    $checks++;
    echo 'PASS: '.$message.PHP_EOL;
}

function demoSnapshot(): array
{
    $snapshot = [];
    foreach (['users', 'categories', 'courses', 'chapters', 'lessons', 'quizzes', 'questions', 'answers', 'enrollments', 'lesson_progress', 'quiz_attempts', 'quiz_answers'] as $table) {
        $snapshot[$table] = all('SELECT * FROM '.$table.' ORDER BY id');
    }
    return $snapshot;
}

demoCheck((int)val('SELECT COUNT(*) FROM courses') === 3, 'starts with the original three-course fixture');
demoCheck((int)val('SELECT COUNT(*) FROM quiz_attempts') > 0, 'fixture includes existing quiz history');
$before = demoSnapshot();

// Fail after inserting a course and chapter to verify atomic recovery.
if ($config['db_driver'] === 'mysql') {
    run("CREATE TRIGGER demo_content_failure BEFORE INSERT ON lessons FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'demo test failure'");
} else {
    run("CREATE TRIGGER demo_content_failure BEFORE INSERT ON lessons BEGIN SELECT RAISE(ABORT, 'demo test failure'); END");
}
$failed = false;
try {
    seedDemoCourses();
} catch (PDOException $error) {
    $failed = str_contains($error->getMessage(), 'demo test failure');
} finally {
    run('DROP TRIGGER demo_content_failure');
}
demoCheck($failed && demoSnapshot() === $before, 'mid-import failure rolls back every inserted row across all 12 tables');

$result = seedDemoCourses();
demoCheck(count($result) === 3 && count(array_filter($result, fn($course) => $course['created'])) === 3, 'retry creates exactly three new courses');
foreach ($before as $table => $rows) {
    foreach ($rows as $row) {
        demoCheck(one('SELECT * FROM '.$table.' WHERE id=?', [$row['id']]) === $row, 'preserves '.$table.' #'.$row['id']);
    }
}

$_SESSION = ['user_id' => 1, 'auth_version' => 1];
$content = require dirname(__DIR__).'/database/demo_courses.php';
$stamp = ['created_at' => now(), 'updated_at' => now()];
foreach ($result as $index => $course) {
    $id = $course['id'];
    $stored = getCourse($id);
    demoCheck($stored['status'] === 'published' && $stored['description'] === $content[$index]['description'], 'new course is public with its audience, prerequisites and outcomes');
    $chapters = all('SELECT * FROM chapters WHERE course_id=? ORDER BY position,id', [$id]);
    demoCheck(count($chapters) === 2 && array_column($chapters, 'position') == [1, 2], 'course has two ordered chapters');
    foreach ($chapters as $chapter) {
        $lessons = all('SELECT * FROM lessons WHERE chapter_id=? ORDER BY position,id', [$chapter['id']]);
        demoCheck(count($lessons) === 2 && array_column($lessons, 'position') == [1, 2], 'chapter has two ordered lessons');
        foreach ($lessons as $lesson) {
            demoCheck(length($lesson['body']) >= 500 && length($lesson['body']) <= 12000, 'lesson contains substantive content within the supported length');
            foreach (['## Mục tiêu', '## Kiến thức', '## Ví dụ chạy được', '## Kết quả và tự kiểm', '## Bài tập nhỏ'] as $section) {
                demoCheck(str_contains($lesson['body'], $section), 'lesson includes '.$section);
            }
        }
    }

    $questions = questionsFor($id);
    demoCheck(count($questions) === 6, 'course has six quiz questions');
    $answers = [];
    foreach ($questions as $position => $question) {
        demoCheck(count($question['answers']) === 4, 'quiz question has four choices');
        $correct = array_values(array_filter($question['answers'], fn($answer) => (int)$answer['is_correct'] === 1));
        demoCheck(count($correct) === 1 && $correct[0]['text'] === $content[$index]['quiz'][$position]['answers'][$content[$index]['quiz'][$position]['correct']], 'stored answer key matches the reviewed content');
        demoCheck(length($question['prompt']) <= 500 && length($question['explanation']) > 30 && length($question['explanation']) <= 1000, 'question includes a supported explanation');
        foreach ($question['answers'] as $answer) {
            demoCheck(length($answer['text']) > 0 && length($answer['text']) <= 250, 'answer fits the supported limit');
        }
        $answers[$question['id']] = $correct[0]['id'];
    }

    insertRow('enrollments', ['user_id' => 1, 'course_id' => $id, 'last_lesson_id' => null, 'completed_at' => null] + $stamp);
    foreach (courseLessons($id) as $lesson) {
        insertRow('lesson_progress', ['user_id' => 1, 'lesson_id' => $lesson['id'], 'completed_at' => now()] + $stamp);
    }
    $attemptId = storeAttempt($id, 1, uuid(), $answers);
    $attempt = one('SELECT * FROM quiz_attempts WHERE id=?', [$attemptId]);
    demoCheck((float)$attempt['score_percent'] === 100.0 && (int)$attempt['total_questions'] === 6, 'server grades all six correct answers as 100%');
    demoCheck((bool)one('SELECT completed_at FROM enrollments WHERE user_id=1 AND course_id=?', [$id])['completed_at'], 'new course completion is recorded');
}

// A manual lesson edit and new learning history must also survive a repeated import.
$lesson = courseLessons($result[0]['id'])[0];
run('UPDATE lessons SET body=? WHERE id=?', [$lesson['body']."\n\nGhi chú riêng của giảng viên.", $lesson['id']]);
$after = demoSnapshot();
$repeat = seedDemoCourses();
demoCheck(array_column($repeat, 'id') === array_column($result, 'id'), 'repeat keeps the same course IDs');
demoCheck(count(array_filter($repeat, fn($course) => $course['created'])) === 0, 'repeat skips all existing demo courses');
demoCheck(demoSnapshot() === $after, 'repeat preserves every row including manual content edits and all quiz history');

echo $checks.' demo course checks passed. Driver: '.$config['db_driver'].PHP_EOL;

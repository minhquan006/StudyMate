<?php

/** Append demo courses while preserving existing courses and learning records. */
function seedDemoCourses(): array
{
    global $config, $storage;

    $lock = fopen($storage.'/demo-courses.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('Không mở được khóa import nội dung demo.');
    }

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new RuntimeException('Không khóa được lượt import nội dung demo.');
        }

        return transaction(function () use ($config) {
            // The instructor row also serializes imports from separate MySQL hosts.
            $suffix = $config['db_driver'] === 'mysql' ? ' FOR UPDATE' : '';
            $instructor = one('SELECT id,role FROM users WHERE email=?'.$suffix, ['instructor1@studymate.test']);
            if (!$instructor || $instructor['role'] !== 'instructor') {
                throw new RuntimeException('Cần tài khoản instructor1@studymate.test có role instructor. Chạy setup trên DB demo trước.');
            }

            $courses = require __DIR__.'/demo_courses.php';
            $stamp = ['created_at' => now(), 'updated_at' => now()];
            $result = [];

            foreach ($courses as $course) {
                $existing = one('SELECT id FROM courses WHERE instructor_id=? AND title=?', [$instructor['id'], $course['title']]);
                if ($existing) {
                    $result[] = ['id' => (int)$existing['id'], 'title' => $course['title'], 'created' => false];
                    continue;
                }

                $category = one('SELECT id FROM categories WHERE name=?', [$course['category']]);
                $categoryId = $category ? (int)$category['id'] : insertRow('categories', ['name' => $course['category']] + $stamp);
                $courseId = insertRow('courses', [
                    'instructor_id' => (int)$instructor['id'],
                    'category_id' => $categoryId,
                    'title' => $course['title'],
                    'description' => $course['description'],
                    'cover_key' => $course['cover_key'],
                    'status' => 'published',
                ] + $stamp);

                foreach ($course['chapters'] as $chapterPosition => $chapter) {
                    $chapterId = insertRow('chapters', [
                        'course_id' => $courseId,
                        'title' => $chapter['title'],
                        'position' => $chapterPosition + 1,
                    ] + $stamp);

                    foreach ($chapter['lessons'] as $lessonPosition => $lesson) {
                        insertRow('lessons', [
                            'chapter_id' => $chapterId,
                            'title' => $lesson['title'],
                            'body' => $lesson['body'],
                            'youtube_video_id' => null,
                            'position' => $lessonPosition + 1,
                        ] + $stamp);
                    }
                }

                $quizId = insertRow('quizzes', ['course_id' => $courseId] + $stamp);
                foreach ($course['quiz'] as $questionPosition => $question) {
                    $questionId = insertRow('questions', [
                        'quiz_id' => $quizId,
                        'prompt' => $question['prompt'],
                        'explanation' => $question['explanation'],
                        'position' => $questionPosition + 1,
                    ] + $stamp);

                    foreach ($question['answers'] as $answerPosition => $answer) {
                        insertRow('answers', [
                            'question_id' => $questionId,
                            'text' => $answer,
                            'is_correct' => (int)($answerPosition === $question['correct']),
                            'position' => $answerPosition + 1,
                        ] + $stamp);
                    }
                }

                $result[] = ['id' => $courseId, 'title' => $course['title'], 'created' => true];
            }

            return $result;
        });
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

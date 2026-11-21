<?php
// Run from the configured demo checkout after tools/setup.php.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/database/seed.php';
require dirname(__DIR__).'/database/seed_demo_courses.php';

try {
    foreach (seedDemoCourses() as $course) {
        echo ($course['created'] ? 'Đã thêm' : 'Đã có, giữ nguyên').' #'.$course['id'].' '.$course['title'].PHP_EOL;
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Import nội dung demo thất bại: '.$error->getMessage().PHP_EOL);
    exit(1);
}

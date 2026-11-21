<?php
require dirname(__DIR__).'/app/bootstrap.php';
try { serveProfileMedia(); }
catch (Throwable $error) { error_log('StudyMate image unavailable'); http_response_code(404); }

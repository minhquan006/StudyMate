<?php
return [
 // Target final database: mysql. sqlite is an optional local demo/test mode.
 'db_driver'=>'mysql',
 'db_host'=>'127.0.0.1', 'db_port'=>3306,
 'db_name'=>'studymate', 'db_user'=>'root', 'db_password'=>'',
 'sqlite_path'=>__DIR__.'/storage/studymate.sqlite',
 'demo_password'=>'StudyMate2026!',
 'app_env'=>'local', 'secure_cookie'=>false,
 'ai_enabled'=>false, 'gemini_api_key'=>'',
 'ai_model'=>'gemini-3.5-flash-lite',
];

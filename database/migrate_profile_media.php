<?php
function profileMediaColumns(): array {
    return [
        'users'=>['birth_date'=>'DATE NULL','bio'=>'TEXT NULL','qualifications'=>'TEXT NULL','avatar_path'=>'VARCHAR(40) NULL'],
        'courses'=>['image_path'=>'VARCHAR(40) NULL'],
        'categories'=>['image_path'=>'VARCHAR(40) NULL'],
    ];
}

/** Add nullable columns to existing databases without changing their records. */
function migrateProfileMedia(): int {
    global $config, $storage;
    $file = fopen($storage.'/profile-media-migration.lock', 'c');
    if (!$file || !flock($file, LOCK_EX)) throw new RuntimeException('Cannot lock migration');
    $mysql = $config['db_driver'] === 'mysql';
    $name = 'studymate-profile-media-'.substr(hash('sha256', $config['db_name']), 0, 24);
    $locked = false;
    try {
        if ($mysql) {
            $locked = (int)val('SELECT GET_LOCK(?,15)', [$name]) === 1;
            if (!$locked) throw new RuntimeException('Cannot lock MySQL migration');
        }
        $added = 0;
        foreach (profileMediaColumns() as $table=>$columns) {
            $existing = $mysql
                ? array_column(all('SHOW COLUMNS FROM '.$table), 'Field')
                : array_column(all('PRAGMA table_info('.$table.')'), 'name');
            if (!$existing) throw new RuntimeException('Run setup before migration: '.$table);
            foreach ($columns as $column=>$definition) {
                if (!in_array($column, $existing, true)) {
                    run('ALTER TABLE '.$table.' ADD COLUMN '.$column.' '.$definition);
                    $added++;
                }
            }
        }
        return $added;
    } finally {
        if ($locked) val('SELECT RELEASE_LOCK(?)', [$name]);
        flock($file, LOCK_UN);
        fclose($file);
    }
}

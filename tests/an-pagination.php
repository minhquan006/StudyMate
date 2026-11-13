<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/database/seed.php';
require dirname(__DIR__) . '/app/services.php';
require dirname(__DIR__) . '/app/views.php';
if (PHP_SAPI !== 'cli' || !str_ends_with($config['db_name'], '_test')) {
    exit("Use a dedicated database ending _test.\n");
}
$checks = 0;
function expectPage(bool $condition, string $label): void {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    echo 'PASS: ' . $label . "\n";
    $checks++;
}
db()->beginTransaction();
try {
    $prefix = 'AN-' . bin2hex(random_bytes(6));
    for ($i = 1; $i <= 25; $i++) insertRow('categories', ['name' => $prefix . '-' . $i, 'created_at' => now(), 'updated_at' => now()]);
    $select = 'SELECT * FROM categories WHERE name LIKE ? ORDER BY id';
    $count = 'SELECT COUNT(*) FROM categories WHERE name LIKE ?';
    $_GET['p'] = 2;
    $page = pageQuery($count, $select, [$prefix . '%']);
    expectPage(count($page['rows']) === 12 && $page['total'] === 25, 'total covers all pages');
    expectPage($page['page'] === 2 && $page['last'] === 3, 'middle page');
    $_GET['p'] = 999;
    $last = pageQuery($count, $select, [$prefix . '%']);
    expectPage($last['page'] === 3 && count($last['rows']) === 1, 'clamp to last page');
    run('DELETE FROM categories WHERE id=?', [$last['rows'][0]['id']]);
    $shrunk = pageQuery($count, $select, [$prefix . '%']);
    expectPage($shrunk['page'] === 2 && count($shrunk['rows']) === 12, 'last item removed clamps page');
    $empty = pageQuery($count, $select, [$prefix . '-absent']);
    expectPage($empty['total'] === 0 && $empty['page'] === 1 && !$empty['rows'], 'empty filter safe page');
    $_GET['p'] = ['invalid'];
    expectPage(pageQuery($count, $select, [$prefix . '%'])['page'] === 1, 'array page input safe');
    $_GET['p'] = -10;
    expectPage(pageQuery($count, $select, [$prefix . '%'])['page'] === 1, 'negative page safe');
    ob_start();
    $_GET = ['page' => 'admin', 'p' => 2, 'q' => '<test>&', 'tab' => 'users'];
    pagination($page);
    $html = ob_get_clean();
    expectPage(str_contains($html, 'tab=users') && str_contains($html, 'q=%3Ctest%3E%26') && str_contains($html, 'p=3'), 'links preserve and escape filters');
    echo "$checks pagination checks passed; driver " . $config['db_driver'] . ".\n";
} finally {
    db()->rollBack();
}

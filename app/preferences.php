<?php
declare(strict_types=1);

// Display preferences belong to the browser, independently of login sessions.
function initializeDisplayPreferences(): void {
    $choices = ['language' => ['vi', 'en'], 'theme' => ['system', 'light', 'dark']];
    $defaults = ['language' => 'vi', 'theme' => 'system'];
    $GLOBALS['display_preferences'] = [];
    foreach ($choices as $name => $allowed) {
        $cookie = 'studymate_'.$name;
        $value = $_COOKIE[$cookie] ?? null;
        $value = is_string($value) && in_array($value, $allowed, true) ? $value : $defaults[$name];
        $query = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requested = $query === 'GET' ? ($_GET['ui_'.$name] ?? null) : null;
        if (is_string($requested) && in_array($requested, $allowed, true)) {
            $value = $requested;
            if (PHP_SAPI !== 'cli') {
                setcookie($cookie, $value, [
                    'expires' => time() + 31536000,
                    'path' => '/',
                    'secure' => (bool)($GLOBALS['config']['secure_cookie'] ?? false),
                    'httponly' => $name === 'language',
                    'samesite' => 'Lax',
                ]);
            }
        }
        $GLOBALS['display_preferences'][$name] = $value;
    }
    if (PHP_SAPI !== 'cli') header('Content-Language: '.uiLanguage());
}

function uiLanguage(): string {
    return $GLOBALS['display_preferences']['language'] ?? 'vi';
}

function uiTheme(): string {
    return $GLOBALS['display_preferences']['theme'] ?? 'system';
}

// Return plain text; escape with h() at the HTML boundary, just like other labels.
function t(string $message, array $values = []): string {
    static $english;
    if (uiLanguage() === 'en') {
        $english ??= array_replace(require __DIR__.'/lang/en.php', require __DIR__.'/lang/glass.en.php', require __DIR__.'/lang/profile.en.php');
        $message = $english[$message] ?? $message;
    }
    $replacements = [];
    foreach ($values as $key => $value) $replacements['{'.$key.'}'] = (string)$value;
    return strtr($message, $replacements);
}

function displayMessage(string $message): string {
    if (preg_match('/\ATrường (\w+) cần (\d+)–(\d+) ký tự\.\z/u', $message, $m)) {
        return t('Trường {name} cần {min}–{max} ký tự.', ['name' => $m[1], 'min' => $m[2], 'max' => $m[3]]);
    }
    if (preg_match('/\A(Thiếu ID hợp lệ|ID không hợp lệ): (\w+)\.\z/u', $message, $m)) {
        return t($m[1].': {name}.', ['name' => $m[2]]);
    }
    return t($message);
}

// Singularise display units after translation; authored text never passes here.
function countedLabel(string $message, int|float $count): string {
    $label=t($message);
    if(uiLanguage()!=='en' || (float)$count!==1.0) return $label;
    $singular=['accounts'=>'account','courses'=>'course','lessons'=>'lesson','chapters'=>'chapter','attempts'=>'attempt','enrollments'=>'enrollment','results'=>'result'];
    return preg_replace_callback('/\b(accounts|courses|lessons|chapters|attempts|enrollments|results)\b/',fn($m)=>$singular[$m[1]],$label);
}

function preferenceFields(string $page, int $id): string {
    $params = ['page' => $page];
    if ($id > 0) $params['id'] = (string)$id;
    foreach (['tab', 'lesson', 'q', 'category', 'p', 'count', 'subject'] as $key) {
        if (isset($_GET[$key]) && is_scalar($_GET[$key])) $params[$key] = (string)$_GET[$key];
    }
    $html = '';
    foreach ($params as $key => $value) $html .= hidden($key, $value);
    return $html;
}

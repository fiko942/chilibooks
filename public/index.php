<?php

// Check PHP version.
$minPhpVersion = '8.0'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    exit($message);
}

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

require FCPATH . '../app/Config/Paths.php';

$paths = new Config\Paths();

require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Some hosting providers disable `putenv()` via php.ini (`disable_functions`).
// CodeIgniter's DotEnv calls `putenv()` in the `CodeIgniter\\Config` namespace.
// This shim prevents a fatal error by safely no-op'ing when `putenv()` is unavailable.
require_once APPPATH . 'Config/PutenvShim.php';

require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();

if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}

$app = Config\Services::codeigniter();
$app->initialize();
$app->setContext(is_cli() ? 'php-cli' : 'web');
$app->run();

exit(EXIT_SUCCESS);

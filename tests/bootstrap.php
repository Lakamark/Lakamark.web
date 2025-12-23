<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// clean the cache
(new Symfony\Component\Filesystem\Filesystem())->remove(__DIR__.'/../var/cache/test');

// Change the kernel environment
$kernel = new App\Kernel('test', true);
$kernel->boot();
$application = new Application($kernel);
$application->setAutoExit(false);
$databaseDoesNotExists = $application->run(new StringInput('doctrine:run-sql "SELECT username FROM user;"'), new NullOutput());
if ($databaseDoesNotExists) {
    $application->run(new StringInput('doctrine:database:drop --if-exists --force -q'));
    $application->run(new StringInput('doctrine:database:create -q'));
    $application->run(new StringInput('doctrine:schema:update --force -q'));
}
$kernel->shutdown();

<?
# * * * * * cd /var/www/clients/client1/web3/web && /usr/bin/php8.3 /var/www/clients/client1/web3/web/cron/scheduler.php 1>> /dev/null 2>&1

require_once __DIR__ . '/../autoconf.php';
include_once __ROOT__ . '/vendor/autoload.php';

//require_once __ROOT__ . '/assets/php/libLocale.php';
//require_once __ROOT__ . '/assets/php/generalFunctions.php';
require_once __ROOT__ . '/adm/cronjobs/modCronjobs.php';

use GO\Scheduler;

// Create a new scheduler
$scheduler = new Scheduler();

$scheduler->clearJobs();

$objCron = new cronjobs($_MYSQLI_);
$jobs = $objCron->select()['data'];
foreach ($jobs as $job) {
    $scheduler->php(__ROOT__ . $job['script'])->at($job['schedule']);
}

$scheduler->run();
echo 'done';
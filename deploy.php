<?php

namespace Deployer;

//require_once 'recipe/common.php';
//require 'recipe/yii2-app-advanced.php';
require 'recipe/yii.php';

set('bin/php', function () {
    return '/usr/bin/php8.3';
});


set('php_executable', 'php8.3');

// Project name
set('application', 'FreelancerBilling');

// Project repository
set('repository', 'github:thevikas/freelancer-billing.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);
set('keep_releases', 5);

// Shared files/dirs between deploys
add('shared_files', [
    '.env',
    'config/db.php',
    'config/client-projects.json'
]);

//default runtime dirs are shared across diff versions
add('shared_dirs', [
    'web/assets',
    'web/m',
    'data'
]);

// Writable dirs by web server
add('writable_dirs', [
    #NO NEED 'web/assets', 
    'runtime']);

// Hosts

host('linode2')
    ->set('hostname', 'linode2')
    ->set('stage', 'prod')
    ->set('branch', 'master')
    ->set('composer_options', ' --verbose --no-interaction --ignore-platform-reqs -W')
    ->set('deploy_path', '~/deploy/prod/{{application}}');

task('ls', function ()
{
    $result = runLocally('ls -l');
    writeln($result);
});

task('migrate', function ()
{
    //todo run("{{release_path}}/yii migrate --interactive=0");
});

task('download_db', function ()
{
    $ts = date('YmdHis');
    run("cd {{deploy_path}};sudo mysqldump --opt tb_dev|bzip2 - >$ts.sql.bz2");
    download("{{deploy_path}}/$ts.sql.bz2", __DIR__);
});

task('fulldeploy', [
    'deploy',
    'endingtasks',
]);

task('copyfiles', function ()
{
    $sharedPath = "{{deploy_path}}/shared";
    run("cp $sharedPath/web/index.php {{release_path}}/web");
});

after('deploy:vendors', 'copyfiles');
after('deploy:vendors', 'migrate');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

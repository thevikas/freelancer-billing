<?php

namespace Deployer;

//require_once 'recipe/common.php';
//require 'recipe/yii2-app-advanced.php';
require 'recipe/yii.php';

// Project name
set('application', 'FreelancerBilling');

// Project repository
set('repository', 'github:thevikas/freelancer-billing.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);
set('keep_releases', 5);

// Shared files/dirs between deploys
add('shared_files', [
    
]);

//default runtime dirs are shared across diff versions
add('shared_dirs', [
]);

// Writable dirs by web server
add('writable_dirs', ['backend/web/assets', 'frontend/web/assets']);

// Hosts

host('xx')
    ->set('hostname', 'tb')
    ->set('user', 'xx')
    ->set('stage', 'dev')
    ->set('composer_options', ' --verbose --no-interaction')
    ->set('deploy_path', '~/deploy/dev/{{application}}');

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
    run("cp $sharedPath/frontend/web/index.php {{release_path}}/frontend/web");
    run("cp $sharedPath/yii {{release_path}}");
});

after('deploy:vendors', 'copyfiles');
after('deploy:vendors', 'migrate');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

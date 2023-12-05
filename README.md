<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Freelancer Time Tracking and Billing</h1>
    <br>
</p>

The primary objective is it is very private. You can host on your own infra that you can only access. You control where you keep the PDFs. There is no common web server. It uses the same file format as gtimelog. No changes.

## Done

1. Prints Invoice PDFs
2. Prints Timesheets
3. Provides Estimates
4. Sends Emails

## Todo

1. Allows web based task time tracking
2. Android app for time tracking
3. Grant customers access to monitor weekly timesheets
4. Append task times to Jira/Gitlab issues/etc


## Background

[Gtimelog](https://github.com/gtimelog/gtimelog) has been my oldest and most simple time tracking app. Originally this project started with just a command line tool for using gtimelog.

Later had setup a git based timelog.txt sync system for the many dev stations I use. This helps keep my timelog.txt same in all machines. Like a desktop in one place and my mac on another.

Later using other PCs where all setup was not done or if it was Windows. I still need to do time tracking. So this is now this is gradually also progressing towards web based time tracking. This is just for cases when command line linux-like env is not setup to run php/gtimelog/git/ssh.

Already a setup of git for storing `timelog.txt` helps in web migration cause it always has latest copy of the timelog.

The timelog itself is a private repo. :)

## Getting Started

2. clone your timelog repository
1. clone this repository
3. do a composer install
3. make a copy of `.env` from `.env.default`
4. edit the `.env` file based on the current system settings
5. add this cloned path into your local $PATH env variable

EXAMPLES
------------

To add a new entry:
`gt "project2: doing something"`

To repeat the last entry
`gt last`

To add a away ** between work and then repeat the last entry
`gt away`

It does not do anything else like make reports,etc (though I could). For that you can use the GUI gtimelog itself.


REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 8.1.


INSTALLATION
------------


CONFIGURATION
-------------


TESTING
-------


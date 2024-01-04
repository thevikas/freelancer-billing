A lighweight time tracking and billing system for freelancers and small business.
==

The primary objective is it is very private. You can host on your own infra that you can only access. You control where you keep the PDFs. There is no common web server. It uses the same file format as gtimelog. No changes.

1. Prints Invoice PDFs
2. Prints Timesheets
3. Provides Estimates
4. Sends Emails

## Todo

1. Allows web based task time tracking
2. Android app for time tracking
3. Grant customers access to monitor weekly timesheets
4. Append task times to Jira/Gitlab issues/etc

## Commands

1. Generate Report `gt -r -m this_month`
2. Save Report - `gt -c -r -m this_month`
3. Bill to PDF `php gt.php --bill -p aurum`
4. Make all active bills together `php gt.php --bill -a`
5. Remake new PDF `clear;XDEBUG_MODE=debug php gt.php --bill -p pwx -d 2023-10-01` 

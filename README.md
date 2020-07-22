# gtimelog-php-cli

[Gtimelog](https://github.com/gtimelog/gtimelog) has been my fav time tracking app. Mostly cause its very simple and small. The data format is simplest possible, just a text file.

A quick client for gtimelog to log work easily.
It simple appends lines into `~/.gtimelog/timelog.txt` file in the same format as gtimelog does.


Examples
==

To add a new entry:
`gt "project2: doing something"`

To repeat the last entry
`gt last`

To add a away ** between work and then repeat the last entry
`gt away`

It does not do anything else like make reports,etc (though I could). For that you can use the GUI gtimelog itself.

Config
==

Its first line as a Timezone, which be changed as needed in your timezone.

This command can be `alias` in bash config so you can just call `gt`

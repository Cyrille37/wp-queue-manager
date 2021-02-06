# WP Queue Manager 

Managing asynchronous or/and long running jobs in Wordpress.

## Features

* can be managed with supervisord or system cron or php.exec()
* manage a queue with job locking to avoid concurent launch of same job
* don't call WP with http request because it's depends on http request time limit,
  * really load WP in job runner
  * make it possible to distinguish between tasks requiring WP plugins and those that do not need them (optimization)

## Inspiration

A big thank you to the designers of the projects [wp-background-processing](https://github.com/deliciousbrains/wp-background-processing) and [10up/WP-Minions](https://github.com/10up/WP-Minions) where I found much knowledge and inspiration.

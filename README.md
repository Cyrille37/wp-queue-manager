# WP Queue Manager 

Managing asynchronous or/and long running jobs in Wordpress.

## Features

* can be managed with supervisord or system cron or php.exec()
* manage a queue with job locking to avoid concurent launch of same job
* can request a job progress via a wp_ajax action, nice for long running jobs (big file import)
* don't call WP with http request because it's depends on http request time limit,
  * really load WP in job runner
  * make it possible to distinguish between tasks requiring WP plugins and those that do not need them (optimization)
    * not possible, we need at least database configuration ans loading wp-config will load many stuff like activated plugins.

In a later time, it should be nice to implements the runner for message queue service, but there is already [10up/WP-Minions](https://github.com/10up/WP-Minions) for RabbitMQ and Gearman with forks for SQS and AWS service. 

## Inspiration

A big thank you to the designers of the projects [wp-background-processing](https://github.com/deliciousbrains/wp-background-processing) and [10up/WP-Minions](https://github.com/10up/WP-Minions) where I found much knowledge and inspiration.

I should fork WP-Minions to add a SystemCall "minion" but I prefer to not have `php-amqplib` dependency.

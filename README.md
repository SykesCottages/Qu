# Qu

[![Build Status](https://travis-ci.org/SykesCottages/Qu.svg?branch=master)](https://travis-ci.org/SykesCottages/Qu) 
[![Coverage Status](https://coveralls.io/repos/github/SykesCottages/Qu/badge.svg?branch=master)](https://coveralls.io/github/SykesCottages/Qu?branch=master)
[![Latest Stable Version](https://poser.pugx.org/sykescottages/qu/v/stable)](https://packagist.org/packages/sykescottages/qu) 
[![Total Downloads](https://poser.pugx.org/sykescottages/qu/downloads)](https://packagist.org/packages/sykescottages/qu) 
[![Latest Unstable Version](https://poser.pugx.org/sykescottages/qu/v/unstable)](https://packagist.org/packages/sykescottages/qu) 

This package has been designed to switch out queue providers using a Queue & Consumer interface. You can use this package with
RabbitMQ & SQS to seamlessly switch between the two. 

## Requirements
* PHP >= 7.3

## Install
Install via Composer:

`composer require sykescottages/qu`

## Examples
In the `examples` folder you can see how to use this with both providers. The interfaces are the same and you only need 
to update the user credentials to match your environment. 

We've bundled the queues in docker so you should be able to run examples locally if you clone this repository.

`docker-compose up -d` will start the RabbitMQ and SQS containers and you can run the examples locally. 

`docker-compose run php composer install` will install all the composer dependencies in the docker container.

## Supported Queues

### RabbitMQ

#### Links
*These links are only available when the docker container is running*

| Name | Link |
|---|---|
| Queue | http://localhost:48888 |
| Management Console | http://localhost:29852 |

#### Options

| Option | Description | Default | Values |
|---|---|---|---|
| blockingConsumer  | Whether the consume method should block execution or only process 1 message | true  | true/false |
| prefetchSize  | Get the limit of messages that can be consumed by the channel |  null | any numeric value |
| prefetchCount  | The limit of messages that we fetch from the queue | 1 | any numeric value |
| consumerTag  | A tag for the consumer | default.consumer.tag | any value |

### SQS

#### Links
*These links are only available when the docker container is running*

| Name | Link |
|---|---|
| Queue | http://localhost:41662 |

#### Options

| Option | Description | Default | Values |
|---|---|---|---|
| blockingConsumer  | Whether the consume method should block execution or only process 1 message | true | true/false|
| pollTime  | Set the consumer to do short polling or long polling | 20 | 0-20|
| maxNumberOfMessagesPerConsume  | The amount of messages each poll/consume will retrieve from the queue | 1 | 1-10|

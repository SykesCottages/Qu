# Qu

This package has been designed to switch out queue providers using a Queue & Consumer interface. You can use this package with
RabbitMQ & SQS to seamlessly switch between the two. 

## Install
Install via Composer:

`composer require sykescottages/qu`

## Examples
In the `examples` folder you can see how to use this with both providers. The interfaces are the same and you only need 
to update the user credentials to match your environment. 

We've bundled the queues in docker so you should be able to run examples locally if you clone this repository.

`docker-compose up -d` will start the RabbitMQ and SQS containers and you can run the examples locally. 

## Supported Queues

### RabbitMQ

#### Links
*This links are only available when the docker container is running*

| Name | Link |
|---|---|
| Queue | http://localhost:48888 |
| Management Console | http://localhost:29852 |

#### Options

| Option | Description | Default | Values   |
|---|---|---|---|
| blockingConsumer  | Whether the consume method should block execution or only process 1 message | true  | true/false |
| prefetchSize  | Get the limit of messages that can be consumed by the channel |  null | any numeric value |
| prefetchCount  | The limit of messages that we fetch from the queue | 1 | any numeric value|

### SQS

#### Links
*This links are only available when the docker container is running*

| Name | Link |
|---|---|
| Queue | http://localhost:41662 |

#### Options

| Option | Description | Default | Values |
|---|---|---|---|
| blockingConsumer  | Whether the consume method should block execution or only process 1 message | true | true/false|
| pollTime  | Set the consumer to do short polling or long polling | 20 | 0-20|

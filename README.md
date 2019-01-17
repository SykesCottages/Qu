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

`docker-compose up -d` will start the PHP, RabbitMQ, SQS containers and you can run the examples locally. 

## Queues

### RabbitMQ

#### Options
| Option  | Description  | Values   |
|---|---|---|
| blockingConsumer  | Whether the consume method should block execution or only process 1 message | Default: true (true/false)|
| prefetchSize  | Get the limit of messages that can be consumed by the channel | Default: null (any numeric value)|
| prefetchCount  | The limit of messages that we fetch from the queue | Default: 1 (any numeric value)|

### SQS

#### Options

| Option  | Description  | Values   |
|---|---|---|
| blockingConsumer  | Whether the consume method should block execution or only process 1 message | Default: true (true/false)|
| pollTime  | Set the consumer to do short polling or long polling | Default: 20 (0-20)|


## Available Queues
### RabbitMQ

Management: http://localhost:29852
Queue http://localhost:48888

### SQS
Queue http://localhost:41662

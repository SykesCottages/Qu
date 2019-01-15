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

## Available Queues
### RabbitMQ

Management: http://localhost:29852
Queue http://localhost:48888

### SQS
Queue http://localhost:41662

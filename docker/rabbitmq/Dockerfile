FROM rabbitmq:3.7-management-alpine

ENV TZ=Europe/London
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY config/definitions.json /etc/rabbitmq/
COPY config/rabbitmq.conf /etc/rabbitmq/

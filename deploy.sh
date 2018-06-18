#!/usr/bin/env bash
docker build -t egorburykin/executor -f Dockerfile_executor .
docker build -t egorburykin/scheduler -f Dockerfile_scheduler .
docker swarm init
docker stack deploy -c docker/docker-compose.yml smsservice
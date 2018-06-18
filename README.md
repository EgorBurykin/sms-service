docker build -t egorburykin/executor -f Dockerfile_executor .
docker build -t egorburykin/scheduler -f Dockerfile_scheduler .
docker stack deploy -c docker/docker-compose.yml smsservice

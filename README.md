### Description
This code is for assignment and therefore simplified. 

This solution can send plain (C)SMS messages using MessageBird API.
To send messages you should POST JSON representation of your SMS to end-point.
JSON object should contain fields:
 * `originator` - E.164 number or "alnum" string up to 11 characters long.
 * `recipients` - array of E.164 numbers. Can contain up to 50 values.
 * `body` - Body of message. Should be less than or equal 39015 bytes.

### How to use
Pre:
* Docker is installed
* Bash

To set your API key, please edit App/Config.php.
To deploy service, please run:

```
chmod +x deploy.sh
./deploy.sh
docker stack services smsservice 
# after few seconds there should be 4 healthy servces
# nginx, scheduler, rabbitmq, executor
```
To test:
```bash
# Send SMS
curl -d '{"body":"Lorem ipsum dolor sit amet.", "originator":"test", "recipients":["79651537263"]}' -H "Content-Type: application/json" -X POST http://localhost:8001
# Send CSMS
curl -d '{"body":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum lobortis justo eros, eget pretium erat scelerisque at. Etiam aliquet ligula sed diam facilisis, eu blandit nunc aliquam. In rutrum ligula in euismod tristique. Proin eget mauris non tortor convallis rutrum. Donec ultricies volutpat.", "originator":"test", "recipients":["79651537263"]}' -H "Content-Type: application/json" -X POST http://localhost:8001
```

### Notes
There is three services:
* *Scheduler*. This service accepts HTTP request with SMS and schedules it for delivery.
* *Message Queue*. This service stores incoming messages and allows executor to send
them.
* *Executor*. This service send messages using MessageBird API.

There is no monitoring service, and also you are blind about message delivery.

### TODO
As it is simplified solution there are still a lot to do:
* Detach configuration properly.
* Handle connection failures in Executor and Scheduler to avoid cascade failure.
* Use appropriate DIC. Consider moving this solution to Symfony.
* Configure restart policies and organize monitoring.
* Use Alpine Linux as container OS.


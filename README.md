This is code for assignment and therefore it is simplified.
It is able right now no send plain (C)SMS messages. Originator can be E.164 number or 
"alnum" string up to 11 characters long. Recipients are array of E.164 numbers containing up to 50 values. 

### How to use

To deloy service please run deploy.sh script.

To send SMS message you need to POST to localhost:8001 JSON object with following fields:
```json
{
  "body": "Test SMS Message",
  "originator": "Test",
  "recipients": ["1234567890"]
}
```

### Solution
There is three modules:
* *Scheduler*. This service accepts HTTP request with SMS and schedules it for delivery.
* *Message Queue*. This service stores incoming messages and allows executor to send
them.
* *Executor*. This service send messages using MessageBird API.

This solution was accepted as it is scalable and flexible.


### TODO
As it is simplified solution there are still a lot to do:
* Handle connection failures in Executor and Scheduler to avoid cascade failure.
* Detach configuration and use appropriate DIC. Consider moving this solution to Symfony.
* Configure restart policies and organize monitoring.


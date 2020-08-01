# Website availability checker (Alarmist)
Ping website by HTTP every 10 min. and send an alarm to Telegram ([Telegram Bot](https://core.telegram.org/bots#3-how-do-i-create-a-bot) is necessary)

*(you can create own availability checkers for you needs through adding new classes of components)*

# Get started
- Install [Docker & Docker Compose](https://docs.docker.com/engine/install/) (e.g. `sudo apt-get install docker-ce docker-ce-cli containerd.io docker-compose`)
- Run in container `docker-compose up`

## [optional]
- Rebuild container `docker-compose up --build`
- Start in background `docker-compose up -d`
    

**Based on [Telegram API](https://github.com/tg-bot-api/bot-api-base)**

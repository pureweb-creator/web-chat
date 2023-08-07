# Web Chat

![Screenshot](https://i.ibb.co/2jVpnKD/image-2023-08-05-19-06-55.png)

# Description
This web application provides online web messenger widget made as a websocket experiment.

Used backend stack is 🐘[PHP](https://www.php.net/) with Workerman for websoket server.\
The frontend was built with ⚡️ [Vue](https://vuejs.org/).

_Other libraries, tools and packages can be found in source code._

# How to build
Nothing special if you are using Docker.\
Ensure that you have .env file filled in accordance with the .env-example one\
Then run following commands:\
```$ docker compose up -d```\
Then go into the php container:\
```$ docker compose exec php bash``` and after that \
run migrations  ```$ php vendor/bin/phinx migrate``` \
run seeds ```$ php vendor/bin/phinx seed:run``` \
Then start a websocket server with
```$ php app/Services/websocket.php start -d``` command in the same directory.

Finally, you have access to
- your website - http://your-domain-name.com
- phpMyAdmin - http://127.0.0.1:8080/
___
_The code has NOT been polished and is provided "as is". There are a lot of code that are redundant and there are lot of improvements that can be made._
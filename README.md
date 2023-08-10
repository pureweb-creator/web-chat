# Web Chat

![Screenshot](https://i.ibb.co/mTg6rKJ/Screenshot-7.png)

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
Then get into the php container:\
```$ docker compose exec php bash``` and after that
- run migrations (only on the 1st start)  ```$ php vendor/bin/phinx migrate```
- run seeds (optionally) ```$ php vendor/bin/phinx seed:run```
- Then start a websocket server with
```$ nohup php app/Services/websocket.php start &``` command in the same directory.\

Finally, you have access to
- your website - http://web-chat.loc
- phpMyAdmin - http://127.0.0.1:8080/

There is one extra step you need to take for this to work\
Add the following line to your hosts file and save it:\
```127.0.0.1 web-chat.loc```

On UNIX-based systems (essentially Linux distributions and macOS), it is located at ```/etc/hosts```. On Windows, it should be located at ```c:\windows\system32\drivers\etc\hosts```. You will need to edit it as administrator

To stop project, run:\
```$ docker compose down```
___
_The code has NOT been polished and is provided "as is". There are a lot of code that are redundant and there are lot of improvements that can be made._

# Web Chat

![Screenshot](https://i.ibb.co/stTdpSy/image-2023-08-02-02-26-53.png)

# Description
This web application provides online web messenger widget made as a websocket experiment

Used backend stack is 🐘[PHP](https://www.php.net/) with Workerman for websoket server\
The frontend was built with ⚡️ [Vue](https://vuejs.org/)

_Other libraries, tools and packages can be found in source code._

# How to build without Docker
Install all required dependencies using ```composer install```.\
Create a MySql database\
Create ```.env``` file that based on ```.env-example``` one and fill with your credentials. \
Open ```phinx.php``` file and fill database credentials in ```development``` section with your own. That is needed to run migrations.\
Run migrations using ```php vendor/bin/phinx migrate``` command\
Seed database using ```php vendor/bin/phinx seed:run``` command

More info about phinx migrations you can read [here](https://book.cakephp.org/phinx/0/en/index.html).

**Important info**\
The project authorization system is performed by e-mail. So to be ready to accept e-mails locally (in windows) you have to have some utility like [this one](https://toolheap.com/test-mail-server-tool/).\
Nothing special, just run that program and forget about it. All e-mails will appear in your desktop.
___
_The code has NOT been polished and is provided "as is". There are a lot of code that are redundant and there are tons of improvements that can be made._
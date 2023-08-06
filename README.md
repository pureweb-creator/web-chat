# Web Chat

![Screenshot](https://i.ibb.co/2jVpnKD/image-2023-08-05-19-06-55.png)

# Description
This web application provides online web messenger widget made as a websocket experiment

Used backend stack is 🐘[PHP](https://www.php.net/) with Workerman for websoket server\
The frontend was built with ⚡️ [Vue](https://vuejs.org/)

_Other libraries, tools and packages can be found in source code._

# How to build
Install all required dependencies using ```composer install```.\
Create a MySql database\
Create ```.env``` file that based on ```.env-example``` one and fill with your credentials. \
Open ```phinx.php``` file and fill database credentials in ```development``` section with your own. That is needed to run migrations.\
Run migrations using ```php vendor/bin/phinx migrate``` command\
Seed database using ```php vendor/bin/phinx seed:run``` command

More info about phinx migrations you can read [here](https://book.cakephp.org/phinx/0/en/index.html).

After all you need to start a websocket server.\
That can be done with ```php app/Services/websocket.php``` command.
___
_The code has NOT been polished and is provided "as is". There are a lot of code that are redundant and there are tons of improvements that can be made._
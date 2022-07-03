# Web Chat Widget

Just a simple online chat with simple auth.

| ![Second screenshot](https://i.ibb.co/hMQHtK0/Screenshot-10.png)  | ![Second screenshot](https://i.ibb.co/93MBW2w/Screenshot-1.png) |
| ------------- | ------------- |

## Technologies:
- PHP
- Vue.js
- Workerman (socket)
- MVC model

>**This is just my own implementation of MVC model in PHP. It is not an example or best practices**

## Before you start
Make sure that you have PHP and localserver such as openserver, wamp, mamp, xampp, etc..<br>
Make sure that you have installed composer to install packages.<br>
After this, install required packages with command
<br>```composer require```</br> in the root of the project.

## How to launch

1. To start **socket server**, open your terminal in this directory:
<br>```web/Commands``` <br>
And run
<br>```php WebSocket.php```<br>

2. To change a **database connection info**, you have jump to 
<br>```web/Kernel/config.php```</br>
and place your connection data into this file.

## Short about directory structure
- *db* - database migrations
- *static* - all static files such as css,js, and twig temlpates (in views folder)
- *web* - main source code folder
    - *web/Commands* - have command files, like WebSocket.php
    - *web/Controllers* - controller classes, trairs and other
    - *web/Kernel* - core folder with configurations.  
        - *web/Kernel/router.php* - router
    - *web/Models* - model classes
    - *web/Views* - view class
    - *web/bootstrap.php* - assembles all files in this folder
    - *index.php* - entry point
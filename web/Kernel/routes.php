<?php

use Web\Controllers\Controller;

$request = key($_GET);

$page = new Controller(CONFIG);

switch ($request) {
    case 'home':
    case '':
        echo $page->home_view();
        break;
    case 'signup':
        echo $page->signup_view();
        break;
    case 'redirect':
        echo $page->redirect();
        break;
    case 'login':
        echo $page->login_view();
        break;
    case 'action/login':
        echo $page->login_action();
        break;
    case 'action/auth':
        echo $page->auth_action();
        break;
    case 'action/signup':
        echo $page->signup_action();
        break;
    case 'action/load/messages':
        echo $page->load_messages_action();
        break;
    case 'action/load/messages/first':
        echo $page->get_first_message();
        break;
    case 'logout':
        echo $page->logout();
        break;
    case 'action/confirm':
        echo $page->confirmation_view();
        break;
    default:
        http_response_code(404);
        echo $page->notfound_view();
        break;
}
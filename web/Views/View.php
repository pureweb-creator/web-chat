<?php

namespace Web\Views;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class View
{
    private object $logger;

    public function __construct()
    {
        $this->logger = new Logger('chat_view');
        $this->logger->pushHandler(new StreamHandler('logs/twig_views.log'));
    }

    public function generate($name, $variables)
    {
        $loader = new FilesystemLoader('static/views');
        $twig = new Environment($loader,['debug'=>true]);

        try {
            $tpl = $twig->load($name);
            return $tpl->render($variables);
        } catch (\Twig\Error\LoaderError | \Twig\Error\RuntimeError | \Twig\Error\SyntaxError $e){
            echo "You got and error. Check logs for more details";
            $this->logger->error("{$e->getMessage()}\nLine: {$e->getFile()}:{$e->getLine()}");
        }
    }
}
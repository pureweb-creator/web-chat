<?php

namespace Web\Views;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class View
{
    private object $logger;
    private $loader;
    private $twig;

    public function __construct()
    {
        $this->logger = new Logger('view');
        $this->logger->pushHandler(new StreamHandler('logs/logs.log'));
    }

    public function generate($name, $variables)
    {
        $this->loader = new FilesystemLoader('static/views');
        $this->twig = new Environment($this->loader,['debug'=>true]);

        try {
            $tpl = $this->twig->load($name);
            return $tpl->render($variables);
        } catch (\Twig\Error\LoaderError | \Twig\Error\RuntimeError | \Twig\Error\SyntaxError $e){
            echo "You got and error. Check logs for more details";
            $this->logger->error("{$e->getMessage()}\nLine: {$e->getFile()}:{$e->getLine()}");
        }
    }
}
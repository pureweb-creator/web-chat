<?php

namespace App\Core;

use App\Exceptions\NotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPMailer\PHPMailer\Exception;

/**
 * Router
 */
final class Router{
	public static function run(View $view, Logger $logger): void
    {
        try {
            // Remove get parameters from query string
            $clean_url = preg_replace("/\?.*$/","",$_SERVER["REQUEST_URI"]);

            // Split the route on / so the first two parts can be extracted
            $uri = array_filter(explode("/", $clean_url));
            
            // Crutch for local servers with "http://localhost/domain-name" url
            $index = $_SERVER["HTTP_HOST"]=="localhost" ? 2 : 1;

            // A name based on the first two parts such as "\User\Edit" or "\User\List"
            $controller_name = ucfirst($uri[$index] ?? "home")."Controller";
            $controller_method = $uri[$index+1] ?? "index";
            $controller_full_name = "App\Controllers\\$controller_name";

            // Does the class e.g. "\User\List\View" exist?
            if (!class_exists($controller_full_name) || !method_exists($controller_full_name, $controller_method))
                throw new NotFoundException("Page not found");

            // E.g. "\App\Controllers\UserController"
            $controller = new $controller_full_name(
                $view, $logger
            );

            // Finally, call matched method
            $controller->$controller_method();

        } catch (NotFoundException $e){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");

            // Render 404 page
            $page_404 = new \App\Controllers\NotFoundController(
                $view, $logger
            );
            $page_404->index();
        } catch (\PDOException $e) {
            header("HTTP/1.1 500 Internal Server Error");
            header("Status: 500 Internal Server Error");

            $logger->critical("Database Error: ".$e->getMessage()." in ". $e->getFile()." on line ".$e->getLine());
        } catch (\Exception $e){
            header("HTTP/1.1 500 Internal Server Error");
            header("Status: 500 Internal Server Error");

            $logger->error("General Error: ".$e->getMessage()." in ". $e->getFile()." on line ".$e->getLine());
        }
    }

	public static function getUrl(): string
    {
		return SITE_URL;
	}
}
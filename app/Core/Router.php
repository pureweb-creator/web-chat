<?php

namespace App\Core;

/**
 * Router
 */
final class Router{
	public static function run(): void
    {
        try {
            // remove get parameters from string
            $clean_url = preg_replace('/\?.*$/','',$_SERVER['REQUEST_URI']);
            $uri = array_filter(explode('/', $clean_url));

            $controller_name = ucfirst($uri[1] ?? 'home')."Controller";
            $controller_method = $uri[2] ?? 'index';
            $controller_fullname = "App\Controllers\\$controller_name";

            $controller = new $controller_fullname;
            $controller->$controller_method();

            if (!class_exists($controller_fullname))
            throw new \Exception("Page not found");

        } catch (\Exception $e){
            header('HTTP/1.1 404 Not Found');
            header("Status: 404 Not Found");
            die();
        }
    }

	public static function getUrl(): string
    {
		return SITE_URL;
	}
}
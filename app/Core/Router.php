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
            $clean_url = preg_replace("/\?.*$/","",$_SERVER["REQUEST_URI"]);
            $uri = array_filter(explode("/", $clean_url));
            
            // cratch for local servers with "http://localhost/domain-name" uri
            $index = $_SERVER["HTTP_HOST"]=="localhost" ? 2 : 1;

            $controller_name = ucfirst($uri[$index] ?? "home")."Controller";
            $controller_method = $uri[$index+1] ?? "index";

            $controller_fullname = "App\Controllers\\$controller_name";

            $controller = new $controller_fullname;
            $controller->$controller_method();

            if (!class_exists($controller_fullname))
            throw new \Exception("Page not found");

        } catch (\Exception $e){
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            die();
        }
    }

	public static function getUrl(): string
    {
		return SITE_URL;
	}
}
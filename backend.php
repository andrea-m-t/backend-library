<?php
switch($_SERVER["REQUEST_METHOD"]){
    
    case "POST":
        switch($_SERVER["PATH_INFO"]){
            case "/register":
                echo "register api";
                break;

            case "/login":
                echo "login api";
                break;

            default:
                http_response_code(400);
                echo "Path is not valid ＞﹏＜";
        }
        break;

    case "GET":
        switch($_SERVER["PATH_INFO"]){
            case "/catalog":
                echo "list of books";
                break;

            case "/about":
                echo "about info";
                break;

            default:
                http_response_code(400);
                echo "Path is not valid :(";
        }
        break;

    default:
        http_response_code(400);
        echo "VERY VERY BAD REQUEST :(";
}
?>
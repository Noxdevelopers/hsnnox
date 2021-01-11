<?php
include './ROUTE.php';
include './app.php';
include './config.php';
include './MyPDO.php';
include 'classes/MyError.php';
include 'classes/products.php';
include 'classes/users.php';

$ROUTE   = app::get(ROUTE)  ;
$SESSION = app::get(SESSION);
$ID      = app::get(USER_ID);

switch ($ROUTE) {
    case ROUTE_USERS:{
        $users = new users();
        break ;
    }
    case ROUTE_PRODUCTS:{
        $products = new products() ;
        break ;
    }

    default:
        $error   = new MyError();
        $error->display("There Is No Valid Route", MyError::$ERROR_NO_ROUTE);

}
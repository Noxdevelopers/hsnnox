<?php
include './ROUTE.php';
include './app.php';
include './config.php';
include './MyPDO.php';
include 'classes/error.php';
include 'classes/products.php';
include 'classes/users.php';

$ROUTE   = app::get(ROUTE)  ;
$SESSION = app::get(SESSION);
$ID      = app::get(USER_ID);

switch (($ROUTE)){
    case ROUTE_USERS:{
        $products = new products() ;
        break ;
    }
    case ROUTE_PRODUCTS:{
        $users = new users();
        break ;
    }

    default:
        $error   = new MyError();
        $error->display("There Is No Valid Route", MyError::$ERROR_NO_ROUTE);

}
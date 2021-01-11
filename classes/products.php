<?php

class products {

    public function __construct($system = false)
    {
        if ($system) return ;

        $action = app::get(ACTION);
        $userID = app::get(USER_ID);

        switch ($action){
            case ACTION_ADD:{
                $title = app::get(INPUT_TITLE);
                $detail= app::get(INPUT_DETAIL);
                $price = app::get(INPUT_PRICE);
                $sort  = app::get(INPUT_SORT);
                $this->addAds($userID , $title , $detail , $price , $sort) ;
                break;
            }
            case ACTION_READ:{
                $start = app::get(INPUT_START) ;
                $this->readAds($userID , $start) ;
                break;
            }
            case ACTION_EDIT:{
                $adID    = app::get(INPUT_AD_ID);
                $title = app::get(INPUT_TITLE);
                $detail= app::get(INPUT_DETAIL);
                $price = app::get(INPUT_PRICE);
                $sort  = app::get(INPUT_SORT);
                $this->editAds($userID , $adID ,$title , $detail , $price , $sort) ;
                break;
            }
            case ACTION_DELETE:{
                $adID = app::get(INPUT_AD_ID);
                $this->deleteAds($userID , $adID) ;
                break;
            }
        }
    }

    private function addAds($userID , $title , $detail , $price , $sort){

        $error = new MyError();

        if (!isset($_REQUEST[SESSION]) ||
            !isset($_REQUEST[USER_ID]) ||
            !isset($_FILES[INPUT_IMAGE])
        ){
            $error->display("Not enough data" , MyError::$ERROR_NOT_ENOUGH_DATA);
            exit;
        }


        $fileName = basename($_FILES[INPUT_IMAGE]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($fileName , PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES[INPUT_IMAGE]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $error->display("File is not an image."  , MyError::$ERROR_FILE_NOT_ALLOWED);
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($fileName)) {
            $error->display("Sorry, file already exists." , MyError::$ERROR_FILE_NOT_ALLOWED);
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES[INPUT_IMAGE]["size"] > 5000000) {
            $error->display("Sorry, your file is too large."  , MyError::$ERROR_FILE_NOT_ALLOWED);
            $uploadOk = 0;
        }


        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png"){
            $error->display("The file type not allowed!"  , MyError::$ERROR_FILE_NOT_ALLOWED);
            $uploadOk = 0;
            exit;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $error->display("Sorry, your file was not uploaded."  , MyError::$ERROR_FILE_NOT_UPLOADED);
            // if everything is ok, try to upload file
        } else {

            $hash = UPLOAD_DIRECTORY . app::generateRandomString(20) . "." .$imageFileType ;

            if (move_uploaded_file($_FILES[INPUT_IMAGE]["tmp_name"] , $hash)){

                $response['state'] = SUCCESS;
                $this->registerImage($userID , $title , $detail , $price , $sort , $hash);

                echo json_encode($response) ;
            }


            else {
                $error->display("Sorry, there was an error uploading your file."  , MyError::$ERROR_FILE_NOT_UPLOADED);
            }
        }

    }
    private function registerImage($userID , $title , $detail , $price , $sort , $hash){


        $conn = MyPDO::getInstance() ;
        $query = "INSERT INTO products (user_id ,title , detail , price , sort , image) VALUES (:user_id ,:title , :detail , :price , :sort , :image)" ;
        $stmt = $conn->prepare($query) ;
        $stmt->bindParam(":user_id" , $userID , PDO::PARAM_INT) ;
        $stmt->bindParam(":title"   , $title) ;
        $stmt->bindParam(":detail"  , $detail) ;
        $stmt->bindParam(":price"   , $price) ;
        $stmt->bindParam(":sort"   ,  $sort) ;
        $stmt->bindParam(":image"   , $hash) ;

        try {
            $stmt->execute() ;
            $id = MyPDO::getLastID($conn) ;
            $response = array("status" => SUCCESSFUL_UPLOAD , "id" => $id) ;
            echo json_encode($response) ;
        }catch (PDOException $ex){

            $error = new MyError();
            $error->display("Server Error " , MyError::$ERROR_MYPDO_SQL);
        }

    }
  private function readAds($userID , $start){

      $conn = MyPDO::getInstance() ;
      $query = "SELECT * FROM products WHERE user_id = :user_id ORDER BY date DESC LIMIT :start , 20" ;
      $stmt = $conn->prepare($query) ;
      $stmt->bindParam(":user_id" , $userID , PDO::PARAM_INT) ;
      $stmt->bindParam(":start"   , $start , PDO::PARAM_INT) ;
      try {

          $stmt->execute() ;
          echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) ;
      }catch (PDOException $ex){

          echo $ex->getMessage();
          $error = new MyError();
          $error->display("Server Error" , MyError::$ERROR_MYPDO_SQL);

      }

    }
  private function editAds($userID , $adID ,$title , $detail , $price , $sort){

      $conn = MyPDO::getInstance() ;
      $query = "UPDATE products SET title = :title , detail	= :detail , price = :price , sort = :sort  WHERE user_id = :user_id AND id = :ad_id" ;
      $stmt = $conn->prepare($query) ;
      $stmt->bindParam(":title"   , $title) ;
      $stmt->bindParam(":detail"  , $detail);
      $stmt->bindParam(":price"   , $price) ;
      $stmt->bindParam(":sort"    , $sort)  ;
      $stmt->bindParam(":user_id" , $userID , PDO::PARAM_INT) ;
      $stmt->bindParam(":ad_id"   , $adID   , PDO::PARAM_INT) ;

      try {
          $stmt->execute() ;
          echo SUCCESS ;
      }catch (PDOException $ex){

          $error = new MyError() ;
          $error->display("Server Error " , MyError::$ERROR_MYPDO_SQL);
      }

    }
  private function deleteAds($userID , $adID){

      $conn  = MyPDO::getInstance() ;
      $query = "DELETE FROM products WHERE user_id = :user_id AND id = :ad_id" ;
      $stmt  = $conn->prepare($query) ;
      $stmt->bindParam(":user_id" , $userID) ;
      $stmt->bindParam(":ad_id"   , $adID) ;

      try {
          $stmt->execute() ;
          echo SUCCESS ;
      }catch (PDOException $ex){
          $error = new MyError();
          $error->display("Server Error " , MyError::$ERROR_MYPDO_SQL);

      }

    }

}
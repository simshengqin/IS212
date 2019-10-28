<?php

require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/protect_json.php';


// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty ('userid'), 
            isMissingOrEmpty ('password') ];
$errors = array_filter($errors);


if (!isEmpty($errors)) {
    $result = [
        "status" => "error",
        "message" => array_values( $errors)
        ];
}
else{
    $userid = $_POST['userid'];
    $password = $_POST['password'];

# complete authenticate API

    if ($userid == "admin"){ 
        if (password_verify($password,'$2y$10$Y64OGHH.HcW17UTrWuxon.nvT6v0viYnQZEurtVN3jurVdT1YgCDW')){ //password is 'SPMisgreat!'
            $result = [
                'status' => 'success',
                'token' => generate_token($userid)
            ];
            $_SESSION['token'] = generate_token($userid);
        }
        else{
            $result = [
                'status' => 'error',
                'message' => 'invalid password'
            ];
        }
    } 
    else {
        $dao = new StudentDAO();
        $student = $dao->retrieveStudent($userid);
        if ($student == null){                      // if userid is not found
            $result = [
                'status' => 'error',
                'message' => 'invalid username'
            ];
        }
        elseif ($student->getPassword() == $password) {       // check password
            $result = [
                'status' => 'success'
            ];
        } 
        else {                                              // if wrong password
            $result = [
                'status' => 'error',
                'message' => 'invalid password'
            ];
        }
        
    }
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
 
?>
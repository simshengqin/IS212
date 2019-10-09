<?php 
require_once '../include/bootstrap.php';

if (isset($_POST['token'])){
$result = doBootstrap();

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
}
else{
  echo "
    <html>
      <form action='http://localhost/spm/json/bootstrap.php'  method='post' enctype='multipart/form-data'>
        File:
        <input type='file' name='bootstrap-file' /><br />
        <input type='text' name='token' value='eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjE0MDk3MTIxNTMsImlhdCI6MTQwOTcwODU1M30.h66rOPHh992gpEPtErfqBP3Hrfkh_nNxYwPG0gcAuCc' />
        <!-- substitute the above value with a valid token -->
        <input type='submit' value='Bootstrap' />
      </form>
    </html>";
}

?>



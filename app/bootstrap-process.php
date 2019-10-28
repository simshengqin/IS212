<?php
# edit the file included below. the bootstrap logic is there
require_once 'include/bootstrap.php';
$_SESSION['bootstrap_error'] = doBootstrap();
header('location: admin.php');



?>

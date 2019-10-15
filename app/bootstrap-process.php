<?php
# edit the file included below. the bootstrap logic is there
require_once 'include/bootstrap.php';
$_SESSION['bootstrap_error'] = doBootstrap();
$bidStatusDAO = new BidStatusDAO();
$bidStatusDAO->updateBidStatus(1, 'open');
header('location: admin.php');



?>

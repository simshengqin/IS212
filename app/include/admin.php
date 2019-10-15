<?php
include_once "common.php";
var_dump($_POST);

if (!isset($_POST["round1"]))
{
    echo " Round 1<br>
    <form method='POST' action='admin.php'> 
    <input type='hidden' id='round1' name='round1' value='Close'>
    <br>
    <button type='submit' value='submit'>Close round </button>
    </form>
    ";
}

if (isset($_POST["round1"]) && (!isset($_POST['round2']))){
    #Changing data on the database 
    update_database("1",'closed');
    
    echo "Round 2 <br>
    <form method='POST' action='admin.php'> 
    <input type='hidden' id='round2' name='round2' value='Open'>
    <input type='hidden' id='round1' name='round1' value='Close'>
    <br>
    <button type='submit' value='submit'>Open round </button>
    </form>";

    

}

if (isset($_POST["round2"])){

    if ($_POST["round2"]=="Open"){
        #Changing data on the database 
        update_database("2",'open');

        echo "Round 2 <form method='POST' action = 'admin.php'> 
        <input type='hidden' id='round2' name='round2' value='Close'>
        <input type='hidden' id='round1' name='round1' value='Close'>
        <br>
        <button type='submit' value='submit'>Close round </button>
        </form>";
    }

    else if ($_POST["round2"]=="Close"){
        #Changing data on the database 
        update_database("2",'closed');

        echo "Round 2 <form method='POST' action = 'logout.php'> 
        <input type='hidden' id='round2' name='round2' value='Close'>
        <input type='hidden' id='round1' name='round1' value='Close'>
        <br>
        <button type='submit' value='submit'>Logout </button>
        </form>";}
    }

    function update_database($round,$status){
        $connMgr = new ConnectionManager();
        $pdo = $connMgr->getConnection();
        $sql = "UPDATE bid_status set round = :round, status = :status where id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt-> bindParam(':round',$round, PDO::PARAM_STR);
        $stmt-> bindParam(':status',$status, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $status = $stmt->execute(); //boolean
    }
?>
<?php
var_dump($_POST);

if (!isset($_POST["round1"]))
{
    echo " Round 1<br>
    <form method='POST' action='admin.php'> 
    <input type='hidden' id='round1' name='round1' value='Close'>
    <br>
    <button type='submit' value='submit'>Close Window </button>
    </form>
    ";
}

if (isset($_POST["round1"]) && (!isset($_POST['round2']))){
    echo "Round 2 <br>
    <form method='POST' action='admin.php'> 
    <input type='hidden' id='round2' name='round2' value='Open'>
    <input type='hidden' id='round1' name='round1' value='Close'>
    <br>
    <button type='submit' value='submit'>Open Window </button>
    </form>";

}

if (isset($_POST["round2"])){

    if ($_POST["round2"]=="Open"){
        echo "Round 2 <form method='POST' action = 'admin.php'> 
        <input type='hidden' id='round2' name='round2' value='Close'>
        <input type='hidden' id='round1' name='round1' value='Close'>
        <br>
        <button type='submit' value='submit'>Close window </button>
        </form>";}

    else if ($_POST["round2"]=="Close")
    {
        echo "Round 2 <form method='POST' action = 'login.php'> 
        <input type='hidden' id='round2' name='round2' value='Close'>
        <input type='hidden' id='round1' name='round1' value='Close'>
        <br>
        <button type='submit' value='submit'>Logout </button>
        </form>";}
    }

    
?>
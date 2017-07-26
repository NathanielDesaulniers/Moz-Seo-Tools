<?php


//if(isset($_POST['url']) && isset($_POST['rows']) && isset($_POST['offset'])){
if(isset($_POST['url_list'])){
    
    $invalid = false;
    
    $moz_url = trim($_POST['url_list']);
    
    if($moz_url == ""){
        $invalid = true;
    }
    
    $_POST = array();
    
    if($invalid == false){
        
        file_put_contents('moz_url.txt', $moz_url);
        file_put_contents('moz_timestamp.txt', time());
        file_put_contents('moz_rows.txt', '0');
        
        //unlink('url-data.csv');
        
        
        sleep(3);
        
        $header_row = "Paid Level Domain (pda), External Equity Links (ueid), Total Links (uid), Moz Rank Log (umrp), Moz Rank Raw (umrr), Page Authority URL (upa), HTTP Status (us), Page Title (ut), Source URL (uu)\n";
        
        if(file_exists('url-data.csv') == False){
            file_put_contents('url-data.csv', $header_row, FILE_APPEND);
        }
        
        header("Location: processing.php");
        die();
        
    }
    
    
}else{}






?>


<!DOCTYPE HTML>
<html>  
<body>
<br>
<b>Find URL Data</b>
<br><br>

<form action="index.php" method="post">
<!--URL: <input type="text" name="url"><br><br>-->

<b>URLS (One per line):</b> 
<br>
<textarea name="url_list" rows="25" cols="100"></textarea>
<br>


<!--
Rows: <input type="text" name="rows" value="25"><br><br>
Offset: <input type="text" name="offset" value="0"><br><br>
-->

<input type="submit" value="Run">
</form>

<br>
<b>Last completed report</b>
<br>
<a href="url-data.csv">Download CSV</a>

</body>
</html>


<?php


if(isset($_POST['url']) && isset($_POST['rows']) && isset($_POST['offset'])){
    
    $invalid = false;
    
    $moz_url = trim($_POST['url']);
    $moz_rows = intval($_POST['rows']);
    $moz_offset = intval($_POST['offset']);
    
    if($moz_url == ""){
        $invalid = true;
    }
    
    //Prevent us from wasting all the api calls
    if($moz_rows > 1000){
        //$invalid = true;
    }
    
    if($moz_rows < 1){
        $invalid = true;
    }
    
    if($moz_offset < 0){
        $invalid = true;
    }
    
    
    $_POST = array();
    
    if($invalid == false){
        
        file_put_contents('moz_url.txt', $moz_url);
        file_put_contents('moz_rows.txt', $moz_rows);
        file_put_contents('moz_offset.txt', '0');
        file_put_contents('moz_global_offset.txt', $moz_offset);
        file_put_contents('moz_timestamp.txt', time());
        unlink('backlinks.csv');
        
        
        sleep(3);
        
        $header_row = "Internal Link ID (lrid), Internal ID Source URL (lsrc), Internal ID Target URL (ltgt), Paid Level Domain (pda), External Equity Links (ueid), Total Links (uid), Moz Rank Log (umrp), Moz Rank Raw (umrr), Page Authority URL (upa), HTTP Status (us), Page Title (ut), Source URL (uu)\n";
        
        file_put_contents("backlinks.csv", $header_row, FILE_APPEND);
        
        
        header("Location: processing.php");
        die();
        
    }
    
    
}else{



}






?>


<!DOCTYPE HTML>
<html>  
<body>
<br>
<b>Find Backlinks</b>
<br><br>

<form action="index.php" method="post">
URL: <input type="text" name="url"><br><br>
Rows: <input type="text" name="rows" value="25"><br><br>
Offset: <input type="text" name="offset" value="0"><br><br>

<input type="submit" value="Run">
</form>

<br>
<b>Last completed report</b>
<br>
<a href="backlinks.csv">Download CSV</a>

</body>
</html>


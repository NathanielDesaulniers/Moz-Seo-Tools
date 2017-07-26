<?php


    function moz_link_metrics($accessID, $secretKey, $objectURL, $moz_limit, $moz_offset){
        //echo "Query Limit: " . $moz_limit . " Query Offset: " . $moz_offset . "<br>";
        
        // Set your expires times for several minutes into the future.
        // An expires time excessively far in the future will not be honored by the Mozscape API.
        $expires = time() + 300;
        // Put each parameter on a new line.
        $stringToSign = $accessID."\n".$expires;
        // Get the "raw" or binary output of the hmac hash.
        $binarySignature = hash_hmac('sha1', $stringToSign, $secretKey, true);
        // Base64-encode it and then url-encode that.
        $urlSafeSignature = urlencode(base64_encode($binarySignature));
        // Specify the URL that you want link metrics for.
        //$objectURL = "www.livechatinc.com";
        // Add up all the bit flags you want returned.
        // Learn more here: https://moz.com/help/guides/moz-api/mozscape/api-reference/url-metrics
        $cols = "103079215108";
        $cols = "103079215140";
        // Put it all together and you get your request URL.
        // This example uses the Mozscape URL Metrics API.
        //$requestUrl = "http://lsapi.seomoz.com/linkscape/url-metrics/".urlencode($objectURL)."?Cols=".$cols."&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;

        $cols = 0;
        $cols += 1; //Page Title
        $cols += 4; //Canonical URL
        $cols += 32; //External Equity Links
        $cols += 16384; //MozRank: URL
        $cols += 2048; //All Links external/internal, equity/non-equity
        $cols += 34359738368; //Page Authority 0 - 100
        $cols += 68719476736; //Domain Authority 0 - 100
        $cols += 536870912; //HTTP Status Code


        /*
        $moz_limit = 10; //Max 50
        $moz_offset = 0; //Defaults to 0
        */

        // Limited when using sort=domain_authority to 3 domains https://moz.com/community/q/api-returns-only-3-results



        $requestUrl = "http://lsapi.seomoz.com/linkscape/links/".urlencode($objectURL)."?Scope=domain_to_domain&Sort=domains_linking_domain&Limit=" . $moz_limit . "&Offset=" . $moz_offset . "&SourceCols=" . $cols . "&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;



        //$requestUrl = "http://lsapi.seomoz.com/linkscape/links/".urlencode($objectURL)."?Scope=domain_to_domain&Sort=domain_authority&Limit=" . $limit . "&SourceCols=" . $cols . "&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;

        //?Scope=domain_to_domain&Sort=domain_authority&Limit=2&SourceCols=16

        /*
        http://lsapi.seomoz.com/linkscape/links/moz.com?Scope=pagetopage&Sort=page_authority&Filter=internal+301&Limit=1&SourceCols=536870916&TargetCols= 4&AccessID=member-cf180f7081&Expires=1225138899&Signature=LmXYcPqc%2BkapNKzHzYz2BI4SXfC%3D
        */

        // Use Curl to send off your request.
        $options = array(
            CURLOPT_RETURNTRANSFER => true
            );
        $ch = curl_init($requestUrl);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);
        
        $contents = json_decode($content);
        //print_r($contents);
        
        
        return $contents;
    }

    function add_rows_to_csv($url, $limit, $offset){
        $link_data = moz_link_metrics("mozscape-XXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", $url, $limit, $offset);
        
        //print_r($link_data);
        
        if(count($link_data) == 0){
            return false;
        }
        
        
        foreach ($link_data as $value) {
            //echo "Internal link ID: " . $value->lrid . "<br>";
            
            $field_internal_link_id = $value->lrid;
            $field_internal_id_of_source_url = $value->lsrc;
            $field_internal_id_of_target_url = $value->ltgt;
            $field_paid_level_domain = $value->pda;
            $field_external_equity_links = $value->ueid;
            $field_all_links_to_url = $value->uid;
            $field_moz_rank_log = $value->umrp;
            $field_moz_rank_raw = $value->umrr;
            $field_page_authority_url = $value->upa;
            $field_http_status = $value->us;
            $field_page_title = $value->ut;
            $field_source_url = $value->uu;
            

            $complete_row_array = array($field_internal_link_id, 
                                        $field_internal_id_of_source_url, 
                                        $field_internal_id_of_target_url,
                                        $field_paid_level_domain,
                                        $field_external_equity_links,
                                        $field_all_links_to_url,
                                        $field_moz_rank_log,
                                        $field_moz_rank_raw,
                                        $field_page_authority_url,
                                        $field_http_status,
                                        $field_page_title,
                                        $field_source_url);


            $fp = fopen('backlinks.csv', 'a');
            fputcsv($fp, $complete_row_array);
            fclose($fp);

            /*
            $complete_row = $field_internal_link_id . ",";
            $complete_row .= $field_internal_id_of_source_url . ",";
            $complete_row .= $field_internal_id_of_target_url . ",";
            $complete_row .= $field_paid_level_domain . ",";
            $complete_row .= $field_external_equity_links . ",";
            $complete_row .= $field_all_links_to_url . ",";
            $complete_row .= $field_moz_rank_log . ",";
            $complete_row .= $field_moz_rank_raw . ",";
            $complete_row .= $field_page_authority_url . ",";
            $complete_row .= $field_http_status . ",";
            $complete_row .= $field_page_title . ",";
            $complete_row .= $field_source_url . "\n";
            
            file_put_contents("backlinks.csv", $complete_row, FILE_APPEND);
            */



            //print_r($value);
            //echo "<br>";
            //echo "Value: $value<br />\n";
        
        }
        
        return true;

    }

    function calculate_time_remaining($rows_left, $rows_per_query, $time_per_query){
        
        $total_seconds_left = round(($rows_left / $rows_per_query) * $time_per_query);
        
        /*
        $minutes_left = intdiv($total_seconds_left, 60);
        $seconds_left = $total_seconds_left % 60;
        */
    
        return "Hours/Minutes/Seconds - " . gmdate("H:i:s", $total_seconds_left);
    
    }


    $moz_url = file_get_contents('moz_url.txt');
    $moz_rows = intval(file_get_contents('moz_rows.txt'));
    $moz_offset = intval(file_get_contents('moz_offset.txt'));
    $moz_global_offset = intval(file_get_contents('moz_global_offset.txt'));
    $timestamp = intval(file_get_contents('moz_timestamp.txt'));

    $current_time = time();
    file_put_contents('moz_timestamp.txt', $current_time);
    
    $elapsed_seconds = $current_time - $timestamp;
    $max_rows_per_query = 25;
    $min_wait_time_seconds = 11;
    
   
    if($moz_rows == 0){
        header("Location: complete.php");
        die();
    }
    
    if($elapsed_seconds >= $min_wait_time_seconds){
        //echo "Ready to Go" . "<br>";
        
        $add_row_success = false;
        
        if($moz_rows <= $max_rows_per_query){
        
            $add_row_success = add_rows_to_csv($moz_url, $moz_rows, $moz_global_offset + $moz_offset); 
            file_put_contents('moz_rows.txt', '0');
            
        }else{
        
            $add_row_success = add_rows_to_csv($moz_url, $max_rows_per_query, $moz_global_offset + $moz_offset); 
            file_put_contents('moz_rows.txt', $moz_rows - $max_rows_per_query);
            file_put_contents('moz_offset.txt', $moz_offset + $max_rows_per_query);
            
        }
        
        
        if($add_row_success == false){
            file_put_contents('moz_rows.txt', '0');
        }
        
        
        
        
    }

   
/* 
    echo "Rows remaining: " . $moz_rows . "<br>";
    echo "Offset: " . $moz_offset . "<br>";
    echo "Elapsed Seconds: " . $elapsed_seconds . "<br>";
*/


?>


<html>
<head>
 <meta http-equiv="refresh" content="13" />

</head>

<body>
    <b>URL: </b> <?php echo $moz_url;?><br>
    <b>Rows remaining: </b> <?php echo $moz_rows;?><br>
    <b>Time remaining: </b> <?php echo calculate_time_remaining($moz_rows, $max_rows_per_query, 13);?><br>

</body>

</html>


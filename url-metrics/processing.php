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



        //$requestUrl = "http://lsapi.seomoz.com/linkscape/links/".urlencode($objectURL)."?Scope=domain_to_domain&Sort=domains_linking_domain&Limit=" . $moz_limit . "&Offset=" . $moz_offset . "&SourceCols=" . $cols . "&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;
        $requestUrl = "http://lsapi.seomoz.com/linkscape/url-metrics/".urlencode($objectURL)."?Cols=".$cols."&AccessID=".$accessID."&Expires=".$expires."&Signature=".$urlSafeSignature;
        

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
        $value = moz_link_metrics("mozscape-XXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", $url, $limit, $offset);
        
        
        //print_r($value);
        
            $field_paid_level_domain = $value->pda; //
            $field_external_equity_links = $value->ueid; //
            $field_all_links_to_url = $value->uid; //
            $field_moz_rank_log = $value->umrp; //
            $field_moz_rank_raw = $value->umrr; //
            $field_page_authority_url = $value->upa; //
            $field_http_status = $value->us; //
            $field_page_title = $value->ut; //
            $field_source_url = $value->uu; //
            

            $complete_row_array = array(
                                        $field_paid_level_domain,
                                        $field_external_equity_links,
                                        $field_all_links_to_url,
                                        $field_moz_rank_log,
                                        $field_moz_rank_raw,
                                        $field_page_authority_url,
                                        $field_http_status,
                                        $field_page_title,
                                        $field_source_url);


            $fp = fopen('url-data.csv', 'a');
            fputcsv($fp, $complete_row_array);
            fclose($fp);

    }

    /*
    function calculate_time_remaining($rows_left, $rows_per_query, $time_per_query){
        $total_seconds_left = round(($rows_left / $rows_per_query) * $time_per_query);
        return "Hours/Minutes/Seconds - " . gmdate("H:i:s", $total_seconds_left);
    }
    */


    $moz_url = file_get_contents('moz_url.txt');
    $url_array = explode (PHP_EOL, $moz_url);
    
    $moz_rows = intval(file_get_contents('moz_rows.txt'));
    $timestamp = intval(file_get_contents('moz_timestamp.txt'));

    $current_time = time();
    file_put_contents('moz_timestamp.txt', $current_time);
    
    $elapsed_seconds = $current_time - $timestamp;
    $min_wait_time_seconds = 11;
    
    if($moz_rows >= count($url_array)){
        header("Location: complete.php");
        die();
    }
    
    $urls_remaining = count($url_array) - $moz_rows;
    
    if($elapsed_seconds >= $min_wait_time_seconds){
    
        $current_url = trim($url_array[$moz_rows]);
        $moz_rows += 1;
        file_put_contents('moz_rows.txt', $moz_rows);
        
        if($current_url != ""){
            add_rows_to_csv($current_url, 0, 0); 
            //echo $current_url;
        }
        
        
    }



?>


<html>
<head>
 <meta http-equiv="refresh" content="13" />

</head>

<body>
    <b>Processing</b><br>
    <b>Urls Remaining: </b><?php echo $urls_remaining;?>
</body>

</html>


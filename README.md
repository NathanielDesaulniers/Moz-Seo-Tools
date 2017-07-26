# Moz-Seo-Tools

Uses the Moz API to find backlinking domains for a website

https://moz.com/products/api

The free Moz Plan allows up to 25,000 rows to be pulled per month

In both "processing.php" files replace the following with your Moz Credentials

$link_data = moz_link_metrics("mozscape-XXXXXXXXXX", "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", $url, $limit, $offset);

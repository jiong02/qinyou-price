<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 

    $httpdns = new HttpdnsGetRequest;
    $client = new ClusterTopClient("23358963","d60915fd89faed62bb1a0dea8af438a8");
    $client->gatewayUrl = "http://gw.api.taobao.com/router/rest";
    $result = $client->execute($httpdns,"6100e23657fb0b2d0c78568e55a3031134be9a3a5d4b3a365753805");
    echo($result->result);

?>
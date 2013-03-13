<?php
    header('Content-type: application/xml; charset=utf8');
    
    $invoiceId = isset($_POST['invoiceId']) ? $_POST['invoiceId'] : 0;
    if (!$invoiceId)
        $invoiceId = isset($_GET['invoiceId']) ? $_GET['invoiceId'] : 0;
    
    $invoiceId = (int) $invoiceId;
    
    $shopId = isset($_POST['shopId']) ? $_POST['shopId'] : 0;
    if (!$shopId)
        $shopId = isset($_GET['shopId'])? $_GET['shopId'] : 0;
    
    $shopId = (int) $shopId;
     
    echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<checkOrderResponse performedDatetime="<?php echo date('Y-m-d')?>T<?php echo date('H:i:s')?>.000+04:00" code="0" invoiceId="<?php echo $invoiceId ?>" shopId="<?php echo $shopId ?>" />
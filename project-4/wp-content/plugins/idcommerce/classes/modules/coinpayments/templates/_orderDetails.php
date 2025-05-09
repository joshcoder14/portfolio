<?php
$key = $order_obj->get_order_meta($order->id,'cps_key',true);
$error = '';
$cps_url = 'https://www.coinpayments.net/';
$checkoutUrl = $cps_url.'index.php?cmd=checkout&id='.$txn_id.'&key='.$key;
$statusUrl = $cps_url.'index.php?cmd=status&id='.$txn_id.'&key='.$key;
$qrCode = $cps_url.'qrgen.php?id='.$txn_id.'&key='.$key;

try {
    $txn_response = $cps_api->GetTxInfoSingleWithRaw($txn_id);
    $result = $txn_response['result'];
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<div class="cps-order-details" data-order_id="<?php echo $order->id;?>" data-cps_status="<?php echo $result['status'];?>">
    <h2><div class="cps-icon"></div>CoinPayments Status:</h2>
    <?php if($error) { ?>
        <div class="text error"><?=$error?></div>
    <?php } else {
        ?>
        <div class="text">
            <p><strong>Status:</strong> <?=$result['status_text']?></p>
            <p><strong>Coin:</strong> <?=$result['coin']?></p>
            <p><strong>Amount:</strong> <?=$result['amountf']?></p>
            <p><strong>Received Amount:</strong> <?=$result['receivedf']?></p>
            <p><strong>Coin Payment Address:</strong> <?=$result['payment_address']?></p>
            <p><strong>Checkout URL:</strong> <a href="<?=$checkoutUrl?>" target="_blank">Click Here</a></p>
            <p><strong>Status URL:</strong> <a href="<?=$statusUrl?>" target="_blank">Click Here</a></p>
            <p><strong>Payment QR Code:</strong> <img src="<?=$qrCode?>" width="250" /></p>
        </div>
    <?php } ?>
</div>
<div class="postbox-container" style="width:35%;">
    <div class="metabox-holder">
        <div class="meta-box-sortables" style="min-height:0;">
            <div class="postbox">
                <h3 class="hndle"><span>Important CoinPayments notes:</span></h3>
                <div class="inside">
                    <strong>API Keys:</strong>
                    <br>
                    <p>In order to generate API keys, <br>login to your <a href="https://www.coinpayments.net" target="_blank">CoinPayments</a> account and follow <b>Account</b> &#10097;&#10097; <b>API Keys</b>, then click on <b>Generate new key..</b> button.</p>
                    <br>
                    <strong>API Keys Permissions:</strong>
                    <br>
                    <p>After generating keys, click on <b>Edit Permissions</b> button, Under <b>API Key Permissions</b> select checkbox to allow desired permissions for API keys.</p>
                    <br>
                    <strong>Accepted Coins:</strong>
                    <br>
                    <p>Click on <b>Coin Settings</b> button, Select the checkbox to enable to desired currency from the list.</p>
                    <p><?php printf(__('Select %1$sLitecoin Testnet (LTCT)%2$s at the bottom of the list, to enable test coin for testing purpose.' , 'memberdeck'), '<a target="_blank" href="https://www.coinpayments.net/index.php?cmd=help&sub=testnet"><b>', '</b></a>'); ?></p>
                    <br><br>
                    <p><i><?php printf(__('Note: Keys that are unused for %u days may be disabled for security.', 'memberdeck'), '30'); ?></i></p>
                    <br>
                    <?php if(isset($_GET['tab']) && $_GET['tab']=='log') {?>
                        <a class="button button-primary" href="<?=admin_url('admin.php?page=idc-coinpayments')?>" style="margin: 0 0 0 auto;display: block;width: 100px;text-align: center;background-color: #bf7d05;border-color: #bf7d05;">Show Config</a>
                    <?php } else { ?>
                        <a class="button button-primary" href="<?=admin_url('admin.php?page=idc-coinpayments&tab=log')?>" style="margin: 0 0 0 auto;display: block;width: 100px;text-align: center;background-color: #bf7d05;border-color: #bf7d05;">Show Log</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
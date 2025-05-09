<div class="wrap ignitiondeck memberdeck">
	<div class="icon32" id="coinpayment"></div><h2 class="title"><?php _e('CoinPayments Merchant Transfer Log', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="mailto:support@ignitionwp.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="https://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
    <div class="id-settings-container">
        <!--MainBox-->
        <div class="postbox-container" style="width:64%; margin-right:1%">
            <div class="metabox-holder">
                <div class="meta-box-sortables" style="min-height:0;">
                    <div class="postbox">
                        <div class="inside cps-merchant-settings">
                            <?php
                            $i=1;
                            $file = new SplFileObject(dirname(__FILE__) . '/' ."../../log/transfers.log");
                            // Loop until we reach the end of the file.
                            while (!$file->eof()) {
                                // Echo one line from the file.
                                //echo '<strong>Log:'.$i.'<br>';
                                $line = str_replace('\r\n','<br>',$file->fgets());
                                $line = str_replace('Date','<br>Date',$line);
                                echo $line;
                                //echo '<br><br>';
                                $i++;
                            }
                            
                            // Unset the file to call __destruct(), closing the file handle.
                            $file = null;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--MainBox-->
        <!--Sidebar-->
        <?php include('sidebar.php');?>
        <!--Sidebar-->
    </div>
</div>

<h2>IgnitionDeck Quick Setup</h2>
<p>Quick Setup for IgnitionDeck. See our <a href="https://docs.ignitiondeck.com/category/15-getting-started" target="_blank">Getting Started Guide</a> in our documentation.</p>

<p><strong>Required:</strong></p>
<ol class="ign-tools">
    <li>
        <span class="check"></span>
        <p>
            Create a frontend <b>IgnitionDeck Dashboard</b> page.
            <a href="https://docs.ignitiondeck.com/article/118-ignitiondeck-commerce-idc-dashboard" title="Click to See Docs" target="_blank">i</a>
        </p>
        <input class="button button-primary" type="button" value="Create Dashboard Page" onclick="wizCreateMyDashboard(this);">
    </li>
    <li>
        <span class="check"></span>
        <p>
            Create a <b>Project Checkout</b> page.
            <a href="https://docs.ignitiondeck.com/article/131-default-pages-and-idc-account-links" title="Click to See Docs" target="_blank">i</a>
        </p>
        <input class="button button-primary" type="button" value="Create Checkout Page" onclick="wizCreateCheckoutPage(this);">
    </li>
    <li>
        <span class="check"></span>
        <p>
            Set <b>Timezone</b> to selected City.
            <a href="https://docs.ignitiondeck.com/article/58-site-default-settings#Timezone" title="Click to See Docs" target="_blank">i</a>
        </p>
        <input class="button button-primary" type="button" value="Set Timezone" onclick="wizSetTimezone(this);">
    </li>
    <li>
        <span class="check"></span>
        <p>
            Set <b>Permalinks</b> to Post Name.
            <a href="https://docs.ignitiondeck.com/article/58-site-default-settings#Permalinks" title="Click to See Docs" target="_blank">i</a>
        </p>
        <input class="button button-primary" type="button" value="Set Permalink"  onclick="wizSetPermalink(this);">
    </li>
    <?php
    $idc_checked = ! get_option( 'idf_commerce_platform' ) || get_option( 'idf_commerce_platform' ) === 'idc' ? true : false;
    if ( $idc_checked ) :
    ?>
    <li class="ign-dashboard-receipt-settings">
        <span class="check"></span>
        <p>
            Set <b>Receipt Settings</b> used in IgnitionDeck.
            <a href="https://docs.ignitiondeck.com/article/119-ignitiondeck-commerce-general-settings#Configure-your-Receipt-and-Platform-Settings" title="Click to See Docs" target="_blank">i</a>
        </p>
        <input class="button button-primary" type="button" value="Specify Receipt Settings" onclick="wizReceiptSettings(this);">
    </li>
    <?php endif; ?>
    <li>
        <span class="check"></span>
        <p>
            Set <b>Payment Gateway</b> credentials to start receiving funds.
            <a href="https://docs.ignitiondeck.com/category/23-payment-gateways" title="Click to See Docs" target="_blank">i</a>
        </p>
        <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=idc-gateways'));?>" target="_blank" onclick="wizPaymentGateway(this);">Check for Active Payment Gateway</a>
    </li>
    <li>
        <span class="check"></span>
        <p>
            Select your <b>Default Currency</b> for the campaigns.
            <a href="https://docs.ignitiondeck.com/article/56-global-currency" title="Click to See Docs" target="_blank">i</a>
        </p>
        <input class="button button-primary" type="button" value="Currency Preference" onclick="wizCurrencyPreference(this);">
    </li>
</ol>

<hr>
<p><strong>Optional:</strong></p>
<ol class="ign-tools">
    <li>
        <span class="check"></span>
        <p>Create <b>Privacy Policy</b> page and link it with IgnitionDeck.</p>
        <input class="button button-primary" type="button" value="Check for Privacy Policy" onclick="wizCreatePrivacyPolicy(this);">
    </li>
    <li>
        <span class="check"></span>
        <p>Create <b>Terms of Use</b> page and link it with IgnitionDeck.</p>
        <input class="button button-primary" type="button" value="Check for Terms of Use" onclick="wizTermsofUse(this);">
    </li>
    <li>
        <span class="check"></span>
        <p>Add a <b>Demo Project</b> with sample data.</p>
        <input class="button button-primary" type="button" value="Create Sample Project" onclick="wizCreateSampleProject(this);">
    </li>
    <li class="ign-tools_delete_sampleproject">
        <p>Delete the <b>Demo Project</b> and all its associated product.</p>
        <input class="button button-primary" type="button" value="Delete Sample Project" onclick="wizDeleteSampleProject(this);">
    </li>
</ol>
<p class="text-center">
    <button type="button" class="wiz-button" onclick="idWizardScreen('#wiz-connect')">Continue</button>
</p>

<?php
require('includes/application_top.php');

$mainPath = dirname(dirname(__FILE__));
$sPath = $mainPath."/feed/config/FeedConfig.php";
require($sPath);

$feedifyConfig = new FeedConfig();

$extraAttributes = array(
    "FEED_EATTRIBUTES_TWIDTH" => "• Tyre Width:",
    "FEED_EATTRIBUTES_TPROFILE" => "• Tyre Profile:",
    "FEED_EATTRIBUTES_TSPEEDINDEX" => "• Tyre Speed Index:",
    "FEED_EATTRIBUTES_TDIAMETER" => "• Tyre Diameter:",
    "FEED_EATTRIBUTES_TLOADINDEX" => "• Tyre Load Index:",
    "FEED_EATTRIBUTES_TSEASON" => "• Tyre Season:",
    "FEED_EATTRIBUTES_TONROAD" => "• Tyre On Road:",
    "FEED_EATTRIBUTES_TOFFROAD" => "• Tyre Off Road:",
);

$attributesToDisplay = array(
    "FEED_ATTRIBUTES_COLOR" => "• Color:",
    "FEED_ATTRIBUTES_SIZE" => "• Size:",
    "FEED_ATTRIBUTES_GENDER" => "• Gender:",
    "FEED_ATTRIBUTES_MATERIAL" => "• Material:",
);

$dbProductsColumns = $feedifyConfig->getDatabaseColumns("'".TABLE_PRODUCTS."', '".TABLE_PRODUCTS_DESCRIPTION."', '".TABLE_CATEGORIES."'");
$feedifyFields = array_merge($feedifyConfig::$gReturn, $feedifyConfig->getQueryFields());

$zones = $feedifyConfig->getTaxZones();
$attributes = $feedifyConfig->getAttributesGroups();

asort($dbProductsColumns);
asort($zones);
asort($attributes);

//check if all required fields are filled
/*if( $_POST ) {
    foreach( $_POST as $key => $value ) {
        if(!strstr($key, "FEEDIFY_SHIPPING") && !strstr($key, "FEEDIFY_FIELD") &&
            !strstr($key, "FEEDIFY_EATTRIBUTES") && !strstr($key, "FEEDIFY_EFIELD")) {
            $connectionValidate = (isset($value) && !empty($value)) ? true : false;
        }
        if($connectionValidate === false) { break; }
    }
}*/

//beginning connection to feed
if(isset($_POST['FEED_USER']) && isset($_POST['FEED_PASS']) && isset($_POST['FEED_SECRET'])){

    $feedifyConfig->remove();
    $feedifyConfig->install();

    $sPath =  $mainPath."/feed/sdk/feed.php";
    if(!file_exists($sPath)) {
        $blSetShopModuleError = true;
    }
    require_once($sPath);

    $sPluginName = "zen_modules";
    $sPluginPath = $mainPath."/feed/plugin/".$sPluginName.".php";
    $oRegisterEvent = new FeedEvent();
    $oNewsEvent = new FeedNewsEvent();

    Feed::getInstance($sPluginPath)->eventManager->dispatchEvent("onRegisterFeed", $oRegisterEvent);
    Feed::getInstance($sPluginPath)->eventManager->dispatchEvent("onNewsFeed", $oNewsEvent);

    if($oRegisterEvent->getResponse()->getStatus() == 204) {
        $blCheckOK = true;
        $FeedifyNews = $oNewsEvent->getNews();
        $response_form = 'succes';
    } else {
        $response_form = 'error';
        $blCheckError = true;
        $FeedifyError = $oRegisterEvent->getResponse()->getStatusMsg();
    }
} else {
    $error = 'error';
    $response_form = '';
    $blCheckError = false;
}

?>
    <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html <?php echo HTML_PARAMS; ?>>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
        <title><?php echo TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
        <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
        <script language="javascript" src="includes/menu.js"></script>
        <script language="javascript" src="includes/general.js"></script>
        <script type="text/javascript">
            <!--
            function init()
            {
                cssjsmenu('navbar');
                if (document.getElementById)
                {
                    var kill = document.getElementById('hoverJS');
                    kill.disabled = true;
                }
            }
            // -->
        </script>
    </head>
    <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
    <!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2" >
    <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="200%" height="75" class="pageHeading" style="background:#3f608b" align="left" ><img style="margin-left: 40px" src="http://interface.feedify.de/includes/css/images-fxm/feedify-logo.png"></td>
                    <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
                </tr>
            </table></td>
    </tr>

    <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0"  >
    <form name="myedit" action="feed_view.php" method="post" >
    <table>
    <tr>
        <td>• Username:</td>
        <td><input type="text" name="FEED_USER" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_USER');?>"></td>
    </tr>
    <tr>
        <td>• Password:</td>
        <td><input type="password" name="FEED_PASS" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_PASS');?>"></td>
    </tr>
    <tr>
        <td>• Secret:</td>
        <td><input type="text" name="FEED_SECRET" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SECRET');?>"><br/></td>
    </tr>
    <tr>
        <td>• Tax Zone:</td>
        <td>
            <select name="FEED_TAX_ZONE"  style="width: 130px; margin-left: 2px">
                <?php
                foreach($zones as $key=>$zone) {
                    echo '<option value='.$key; if ($feedifyConfig->getConfig('FEED_TAX_ZONE') == $key ) {echo " selected ";} echo '>'.$zone.'</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
    <tr>
        <td>• Delivery Time: </td>
    </tr>
    <td style="text-align: right"> - from: </td><td><input type="text" name="FEED_DTIME_FROM" size="number" value="<?php echo $feedifyConfig->getConfig('FEED_DTIME_FROM');?>"></td>
    <tr>
        <td style="text-align: right"> - to: </td><td><input type="text" name="FEED_DTIME_TO" size="number" value="<?php echo $feedifyConfig->getConfig('FEED_DTIME_TO');?>"></td>
    </tr>
    <tr>
        <td style="text-align: right"> - type: </td>
        <td><select name="FEED_DTIME_TYPE" style="width: 130px; margin-left: 2px">
                <option value="D" <?php if ($feedifyConfig->getConfig('FEED_DTIME_TYPE') == 'D') echo "selected"; ?> >days</option>
                <option value="W" <?php if ($feedifyConfig->getConfig('FEED_DTIME_TYPE') == 'W') echo "selected"; ?> >weeks</option>
                <option value="M">months <?php if ($feedifyConfig->getConfig('FEED_DTIME_TYPE') == 'M') echo "selected"; ?> </option>
            </select>
        </td>
    </tr>
    </tr>


    <tr><td><br></td></tr>

    <tr>
        <td colspan="2"><h1 style="color:#3f608b">- Export Configurations:</h1></td>
    </tr>

    <tr>
        <td>• Tax Rate (Default):</td>
        <td><input type="text" name="FEED_FIELD_TAX_RATE" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_FIELD_TAX_RATE');?>"></td>
    </tr>

    <tr>
        <td>• Shipping Cost (Default):</td>
        <td><input type="text" name="FEED_FIELD_SHIPPING_COST" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_FIELD_SHIPPING_COST');?>"></td>
    </tr>

    <tr>
        <td>• Availability (Default):</td>
        <td><select name="FEED_FIELD_AVAILABILITY"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_AVAILABILITY') == 'N') echo "selected"; ?> >-- empty --</option>
                <option value="0" <?php if ($feedifyConfig->getConfig('FEED_FIELD_AVAILABILITY') == '0') echo "selected"; ?> >out of stock</option>
                <option value="1" <?php if ($feedifyConfig->getConfig('FEED_FIELD_AVAILABILITY') == '1') echo "selected"; ?> >in stock</option>
                <option value="2" <?php if ($feedifyConfig->getConfig('FEED_FIELD_AVAILABILITY') == '2') echo "selected"; ?> >available for order</option>
                <option value="3" <?php if ($feedifyConfig->getConfig('FEED_FIELD_AVAILABILITY') == '3') echo "selected"; ?> >preorder</option>
            </select></td>
    </tr>

    <tr>
        <td>• Coupon: </td>
        <td><select name="FEED_FIELD_COUPON"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_COUPON') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key_2=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_COUPON') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select></td>
    </tr>

    <?php foreach ($attributesToDisplay as $key=>$item) { ?>
        <tr>
            <td><?php echo $item ?></td>
            <td><select name="<?php echo $key ?>"  style="width: 130px; margin-left: 2px" >
                    <option value="N" <?php if ($feedifyConfig->getConfig($key) == 'N') echo "selected"; ?> >-- empty --</option>
                    <?php
                    foreach($attributes as $attribute) {
                        echo '<option value='.$attribute['products_options_id'];if ($feedifyConfig->getConfig($key) == $attribute['products_options_id']) { echo " selected ";} echo'>'.$attribute['products_options_name'].'</option>';
                    }
                    ?>
                </select></td>
            <td>
                <select name="<?php echo str_replace( "ATTRIBUTES","FIELD",$key ) ?>"  style="width: 130px; margin-left: 2px">
                    <option value="N" <?php if ($feedifyConfig->getConfig(str_replace( "ATTRIBUTES","FIELD",$key )) == 'N') echo "selected"; ?> >-- empty --</option>
                    <?php
                    foreach($dbProductsColumns as $key_2=>$column) {
                        echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig(str_replace( "ATTRIBUTES","FIELD",$key )) == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    <?php } ?>

    <tr>
        <td>• Ean Field:</td>
        <td>
            <select name="FEED_FIELD_EAN"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_EAN') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_EAN') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>• Subtitle Field:</td>
        <td>
            <select name="FEED_FIELD_SUBTITLE"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_SUBTITLE') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_SUBTITLE') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>• Packet Size:</td>
        <td>
            <select name="FEED_FIELD_PACKET_SIZE"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_PACKET_SIZE') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_PACKET_SIZE') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>• ISBN Field:</td>
        <td>
            <select name="FEED_FIELD_ISBN"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_ISBN') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_ISBN') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>• Base Unit Field:</td>
        <td>
            <select name="FEED_FIELD_BASE_UNIT"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_BASE_UNIT') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_BASE_UNIT') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>• Manufacturer recommended price:</td>
        <td>
            <select name="FEED_FIELD_UVP"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_UVP') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_UVP') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td>• Yategoo Category Field:</td>
        <td>
            <select name="FEED_FIELD_YATEGOO"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_FIELD_YATEGOO') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_FIELD_YATEGOO') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <tr>
        <td colspan="2"><h1 style="color:#3f608b">- Special Delivery Prices:</h1></td>
    </tr>
    <tr>
        <td>•  shipping outside the EU:</td>
        <td><input type="text" name="FEED_SHIPPING_ADDITION_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_ADDITION_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_ADDITION_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_ADDITION_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_ADDITION_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_ADDITION_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_ADDITION_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_ADDITION_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs for paypal Austria:</td>
        <td><input type="text" name="FEED_SHIPPING_PAYPAL_OST_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_OST_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_PAYPAL_OST_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_OST_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_OST_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_PAYPAL_OST_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_OST_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_OST_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping cost for Cash on Deliver:</td>
        <td><input type="text" name="FEED_SHIPPING_COD_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_COD_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_COD_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_COD_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_COD_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_COD_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_COD_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_COD_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping cost for Creditcard:</td>
        <td><input type="text" name="FEED_SHIPPING_CREDIT_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_CREDIT_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_CREDIT_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_CREDIT_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_CREDIT_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
       <!-- <td>or<select name="FEED_SHIPPING_CREDIT_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_CREDIT_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_CREDIT_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs for paypal:</td>
        <td><input type="text" name="FEED_SHIPPING_PAYPAL_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_PAYPAL_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_PAYPAL_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_PAYPAL_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs Ready for Transfer:</td>
        <td><input type="text" name="FEED_SHIPPING_TRANSFER_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_TRANSFER_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_TRANSFER_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_TRANSFER_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_TRANSFER_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_TRANSFER_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_TRANSFER_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_TRANSFER_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs ELV:</td>
        <td><input type="text" name="FEED_SHIPPING_DEBIT_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_DEBIT_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_DEBIT_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_DEBIT_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_DEBIT_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_DEBIT_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_DEBIT_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_DEBIT_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs for purchase orders:</td>
        <td><input type="text" name="FEED_SHIPPING_ACCOUNT_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_ACCOUNT_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_ACCOUNT_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_ACCOUNT_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_ACCOUNT_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_ACCOUNT_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_ACCOUNT_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_ACCOUNT_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs at Moneybookers:</td>
        <td><input type="text" name="FEED_SHIPPING_MONEYBOOKERS_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_MONEYBOOKERS_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_MONEYBOOKERS_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_MONEYBOOKERS_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_MONEYBOOKERS_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_MONEYBOOKERS_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_MONEYBOOKERS_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_MONEYBOOKERS_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs Click & Buy:</td>
        <td><input type="text" name="FEED_SHIPPING_CLICK_BUY_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_CLICK_BUY_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_CLICK_BUY_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_CLICK_BUY_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_CLICK_BUY_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
       <!-- <td>or<select name="FEED_SHIPPING_CLICK_BUY_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_CLICK_BUY_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_CLICK_BUY_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Shipping costs Giropay:</td>
        <td><input type="text" name="FEED_SHIPPING_GIROPAY_1" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_SHIPPING_GIROPAY_1');?>"><br/></td>
        <td>or
            <select name="FEED_SHIPPING_GIROPAY_2"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_SHIPPING_GIROPAY_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_GIROPAY_2') == $column['table_name'].';'.$column['column_name']) {
                        echo " selected ";
                    }
                    echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
        <!--<td>or<select name="FEED_SHIPPING_GIROPAY_3"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_SHIPPING_GIROPAY_3') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];
                    if ($feedifyConfig->getConfig('FEED_SHIPPING_GIROPAY_3') == $attribute['products_options_id']) {
                        echo " selected ";
                    }
                    echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>-->
    </tr>
    <tr>
        <td>• Delivery comment:</td>
        <td><textarea name="FEED_SHIPPING_COMMENT_1" style="margin-left: 2px;"><?php echo $feedifyConfig->getConfig('FEED_SHIPPING_COMMENT_1');?></textarea><br/></td>
    </tr>

    <tr><td><br></td></tr>












    <tr>
        <td><h1 style="color:#3f608b">- Attributes Extra:</h1></td>
    </tr>
    <?php /*foreach($extraAttributes as $key => $extraAttribute) { */?><!--
        <tr>
            <td><?php /*echo $extraAttribute */?></td>
            <td><select name="<?php /*echo $key */?>"  style="width: 130px; margin-left: 2px" >
                    <option value="N" <?php /*if ($feedifyConfig->getConfig($key) == 'N') echo "selected"; */?> >-- empty --</option>
                    <?php
/*                    foreach($attributes as $attribute) {
                        echo '<option value='.$attribute['products_options_id'];if ($feedifyConfig->getConfig($key) == $attribute['products_options_id']) { echo " selected ";} echo'>'.$attribute['products_options_name'].'</option>';
                    }
                    */?>
                </select></td>
        </tr>
    --><?php /*} */?>


    <tr>
        <td>• Condition:</td>
        <td><select name="FEED_EFIELD_CONDITON_1"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_EFIELD_CONDITON_1') == 'N') echo "selected"; ?> >-- empty --</option>
                <option value="0" <?php if ($feedifyConfig->getConfig('FEED_EFIELD_CONDITON_1') == '0') echo "selected"; ?> >new</option>
                <option value="1" <?php if ($feedifyConfig->getConfig('FEED_EFIELD_CONDITON_1') == '1') echo "selected"; ?> >used</option>
            </select></td>
        <td>or
            <select name="FEED_EFIELD_CONDITON_2" style="width: 130px; margin-left: 2px">
                <option value="N" <?php if ($feedifyConfig->getConfig('FEED_EFIELD_CONDITON_2') == 'N') echo "selected"; ?> >-- empty --</option>
                <?php
                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_EFIELD_CONDITON_2') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                ?>
            </select>
        </td>
    </tr>

    <!--<tr>
        <td>• Deposit</td>
        <td><select name="FEED_EATTRIBUTES_DEPOSIT"  style="width: 130px; margin-left: 2px" >
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_EATTRIBUTES_DEPOSIT') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($attributes as $attribute) {
                    echo '<option value='.$attribute['products_options_id'];if ($feedifyConfig->getConfig('FEED_EATTRIBUTES_DEPOSIT') == $attribute['products_options_id']) { echo " selected ";} echo'>'.$attribute['products_options_name'].'</option>';
                }
                */?>
            </select></td>
    </tr>

    <tr>
        <td>• HSN code field</td>
        <td><select name="FEED_EFIELD_HSN_CODE"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_EFIELD_HSN_CODE') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_EFIELD_HSN_CODE') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                */?>
            </select></td>
    </tr>

    <tr>
        <td>• TSN code field</td>
        <td><select name="FEED_EFIELD_TSN_CODE"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_EFIELD_TSN_CODE') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_EFIELD_TSN_CODE') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                */?>
            </select></td>
    </tr>

    <tr>
        <td>• Auto Manufacturer</td>
        <td><select name="FEED_EFIELD_AUTO_MANUFACTURER"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_EFIELD_AUTO_MANUFACTURER') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_EFIELD_AUTO_MANUFACTURER') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                */?>
            </select></td>
    </tr>

    <tr>
        <td>• TECDOC field</td>
        <td><select name="FEED_EFIELD_TECDOC"  style="width: 130px; margin-left: 2px">
                <option value="N" <?php /*if ($feedifyConfig->getConfig('FEED_EFIELD_TECDOC') == 'N') echo "selected"; */?> >-- empty --</option>
                <?php
/*                foreach($dbProductsColumns as $key=>$column) {
                    echo '<option value='.$column['table_name'].';'.$column['column_name']; if ($feedifyConfig->getConfig('FEED_EFIELD_TECDOC') == $column['table_name'].';'.$column['column_name']) { echo " selected ";} echo'>'.$column['column_name'].' ('.$column['table_name'].')</option>';
                }
                */?>
            </select></td>
    </tr>-->

    <tr>
        <td><h1 style="color:#3f608b">- Tracking Pixel:</h1></td>
    </tr>
    <tr>
        <td>• Client Id:</td>
        <td><input type="text" name="FEED_CLIENT_ID" style="margin-left: 2px;" value="<?php echo $feedifyConfig->getConfig('FEED_CLIENT_ID');?>"></td>
    </tr>

    <tr>
        <td>• Products Id field</td>
        <td><select name="FEED_TRACKING_PRODUCTS_ID"  style="width: 130px; margin-left: 2px">
                <?php
                foreach($feedifyFields as $key=>$column) {
                    echo '<option value='.$key; if ($feedifyConfig->getConfig('FEED_TRACKING_PRODUCTS_ID') == $key) {echo " selected ";} echo '>'.$column.'</option>';
                }
                ?>
            </select></td>
    </tr>

    <tr>
        <td>• Tracking Pixel Enable:</td>
        <td>
            <input type="checkbox" name="FEED_TRACKING_PIXEL_STATUS" value="Y" <?php if ($feedifyConfig->getConfig('FEED_TRACKING_PIXEL_STATUS') == 'Y') {echo " checked ";}?> >
        </td>
    </tr>
    <tr>
        <td><br><input name="chek" value="Connect" type="submit" style="margin-left: 30px;font-size: 15px;" ></td>
    </tr>
    </table>
    </form>
    </table></td>
    </tr>
    </table></td>
    <!-- body_text_eof //-->
    </tr>
    </table>
    <?php
    if($response_form == 'succes') echo '<h1 style="color:#3f608b;margin-left: 30px">• Succes!</h1>';
    else if($response_form == 'error') echo '<h1 style="color:#3f608b;margin-left: 30px">• Something goes wrong on connection to feed</h1>';
    if($connectionValidate === false) { echo '<h1 style="color:#3f608b;margin-left: 30px">• Some Fields are Required!</h1>'; }
    ?>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br>

    </body>
    </html>

<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
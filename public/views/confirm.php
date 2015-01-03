<?php
	
	if($GLOBALS['PaymentSuccessfull']) {
    echo __("Thank you for your purchase. Your download should automatically start, if it does not, please <a href='".$GLOBALS['item_url']."'>click here</a> to download.");
?>
<script type="text/javascript">
// add relevant message above or remove the line if not required
window.onload = function(){
    if(window.opener){
      top.window.opener.location = "<?php echo $GLOBALS['item_url'];?>";
    }
};
                                
</script>
<?php
	}
	else
	{

    global $error;
    echo $error;

	}
?>
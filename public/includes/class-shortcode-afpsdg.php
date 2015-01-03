<?php

class AFPSDGShortcode {

    var $afpsdg = null;
    var $amazonfpsdg = null;

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;
    protected static $payment_buttons = array();

    function __construct() { 
        if( ! session_id() ) {
            add_action( 'init', 'session_start', -2 );
        }    
            // ?@session_start(session_name());

        $this->afpsdg = AFPSDG::get_instance();
        $this->amazonfpsdg = AmazonFPSDG::get_instance();

        add_shortcode('amazonfps', array(&$this, 'shortcode_amazonfps'));
        add_shortcode('afpsdg_checkout', array(&$this, 'shortcode_afpsdg_checkout'));
        if (!is_admin()) {
            add_filter('widget_text', 'do_shortcode');
        }

        add_action( 'init', array(&$this, 'init'), -2 );

        // add_action('wp_footer', array(&$this, 'hook_footer'));
    }

    function init(){
        /**
            Process the checkout step before any content sent to browser. 
            Because Amazon require a page redirect.
        */
        if(!empty($_GET['step']) && $_GET['step'] == 'checkout') {
            $currentPage = $this->curPageURL();
            $checkout_url = $this->afpsdg->get_setting('checkout_url');

            $checkout = parse_url($checkout_url);
            $page = parse_url($checkout_url);

            if($checkout['path'] == $page['path'] && $checkout['host'] == $page['host'] ) {
                $this->shortcode_afpsdg_checkout();
                exit;
            }
        }
    }

    function curPageURL() {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
            $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    function shortcode_amazonfps($atts, $content = "") {

        extract(shortcode_atts(array(
            'name' => 'Item Name',
            'price' => '0',
            'quantity' => '1',
            'url' => '',
            'currency' => $this->afpsdg->get_setting('currency_code'),
            'button_text' => $this->afpsdg->get_setting('button_text'),
                        ), $atts));
        if (empty($url)) {
            return '<div style="color:red;">Please specify a digital url for your product </div>';
        }
        $url = base64_encode($url);
        $button_id = 'amazonfps_button_' . count(self::$payment_buttons);
        self::$payment_buttons[] = $button_id;

        $output = "<form action='" . $this->afpsdg->get_setting('checkout_url') . "?step=checkout' METHOD='POST'> ";

        $output .= "<input type='hidden' value='{$name}' name='item_name' />";
        $output .= "<input type='hidden' value='{$price}' name='item_price' />";
        $output .= "<input type='hidden' value='{$quantity}' name='item_quantity' />";
        $output .= "<input type='hidden' value='{$currency}' name='currency_code' />";
        $output .= "<input type='hidden' value='{$url}' name='item_url' />";
        $output .= "<input type='hidden' value='".$this->afpsdg->get_setting('checkout_url') . "?step=ipn' name='ipn_url' />";
        $output .= "<input type='hidden' value='checkout' name='step' />";
        $output .= "<button name='amazonfps_submit' class='amazonfps_submit' id='{$button_id}' alt='{$button_text}'>{$button_text}</button>";
        $output .= "</form>";

        return $output;
    }

    public function hook_footer() {

    }

    public function shortcode_afpsdg_checkout() {
        $step = '';
        if (!empty($_REQUEST['step']) ) {
            $step = $_REQUEST['step'];
        }
        switch ($step) {
            case 'checkout':
                if(!isset($_REQUEST['amazonfps_submit'])) {
                    break;
                }

                if (empty($_POST['item_price'])) {
                    die('Product price must not be zero.');
                }
                if (empty($_POST['item_name'])) {
                    die('Product name must not be empty.');
                }
                if (empty($_POST['item_quantity'])) {
                    die('Product quantity must not be zero.');
                }
                if (empty($_POST['currency_code'])) {
                    die('Please specify currency code either in the shortcode or in General > AFPSDG section in admin panel.');
                }

                $paymentAmount = $_POST['item_price'] * $_POST['item_quantity'];

                $afpsdg_txn_data = array(
                    'item_price' => $_POST['item_price'],
                    'item_name'  => $_POST['item_name'],
                    'item_url'  => $_POST['item_url'],
                    'item_quantity' => $_POST['item_quantity'],
                    'amount'        => $paymentAmount,
                    'currency_code' => $_POST['currency_code']
                );



                $pipeline = new Amazon_FPS_CBUISingleUsePipeline($this->afpsdg->get_setting('aws_access_key_id'), $this->afpsdg->get_setting('aws_secret_access_key') );
                // 'AKIAI3RCRXE6AJQPNT7A', 'faSzg5UL5+fFxVTYndtmjY7Xxyl49qpUb4hMN/EM');

                $_SESSION['afpsdg_txn_data'] = $afpsdg_txn_data;
                $returnURL = $this->afpsdg->get_setting('checkout_url') . '?step=confirm';

                $pipeline->setMandatoryParameters(md5(uniqid(rand(), true)), $returnURL, $paymentAmount);
                
                //optional parameters
                $pipeline->addParameter("currencyCode", $_POST['currency_code']);
                $pipeline->addParameter("paymentReason", $_POST['item_name']);


                wp_redirect($pipeline->getUrl());
                exit;
                break;
            case 'cancel':
                include dirname(dirname(__FILE__)) . '/views/cancel.php';
                break;
            case 'ipn':

                $utils = new Amazon_FPS_SignatureUtilsForOutbound();
                $params = $_REQUEST;
                $urlEndPoint = $this->afpsdg->get_setting('checkout_url'). '?step=ipn';
                if($utils->validateRequest($params, $urlEndPoint, "POST")) 
                {

                    $order = OrdersAFPSDG::get_instance();
                    $order->update($_REQUEST['transactionId'], $_REQUEST);
                    die('Order verified!');
                }
                exit;
            case 'confirm':

                if(empty($_REQUEST['tokenID']) || empty($_REQUEST['callerReference']) ) {
                    echo 'Invalid Request';
                    break;
                }
                $GET = $_GET;

                $afpsdg_txn_data = $_SESSION['afpsdg_txn_data'];
                $service = new Amazon_FPS_Client($this->afpsdg->get_setting('aws_access_key_id'), $this->afpsdg->get_setting('aws_secret_access_key') );

                $request =  new Amazon_FPS_Model_PayRequest();
                $request->setSenderTokenId($_REQUEST['tokenID']);//set the proper senderToken here.
                
                $amount = new Amazon_FPS_Model_Amount();
                $amount->setCurrencyCode($afpsdg_txn_data['currency_code']);
                $amount->setValue($afpsdg_txn_data['amount']); //set the transaction amount here;

                $request->setTransactionAmount($amount);
                $request->setCallerReference($_REQUEST['callerReference']); //set the unique caller reference here.

                // $service_interface = $service;
                try {
                    $response = $service->pay($request);
                    $order_details = $GET;

                    if ($response->isSetPayResult()) { 
                        $payResult = $response->getPayResult();
                        if ($payResult->isSetTransactionId()) 
                            $order_details['TransactionId'] = $payResult->getTransactionId();
                        if ($payResult->isSetTransactionStatus()) 
                            $order_details['TransactionStatus'] = $payResult->getTransactionStatus();
                    } 

                    if ($response->isSetResponseMetadata()) { 
                        $responseMetadata = $response->getResponseMetadata();
                        if ($responseMetadata->isSetRequestId()) 
                            $order_details['RequestId'] = $responseMetadata->getRequestId();
                    } 

                    $order_details = array_merge($order_details, $afpsdg_txn_data);

                    /*  verify request origin */ 
                    $utils = new Amazon_FPS_SignatureUtilsForOutbound();
        
                    //Parameters present in return url.
                    $params["expiry"] = $order_details['expiry'];
                    $params["tokenID"] = $order_details['tokenID'];
                    $params["status"] = $order_details['status'];
                    $params["callerReference"] = $order_details['callerReference'];
                    $params["signatureMethod"] = $order_details['signatureMethod'];
                    $params["signatureVersion"] = $order_details['signatureVersion'];
                    $params["certificateUrl"] = $order_details['certificateUrl'];
                    $params["signature"] = $order_details['signature'];
                    
                    $urlEndPoint = $this->afpsdg->get_setting('checkout_url') . '?step=confirm'; //Your return url end point. 
                    $order_details['signatureVerified'] = $utils->validateRequest($params, $urlEndPoint, "GET");

                    $order = OrdersAFPSDG::get_instance();
                    $order->insert($order_details, $response);
                    $GLOBALS['PaymentSuccessfull'] = false;

                    if(!$order_details['signatureVerified']) {
                        $output ="<strong>".__("Payment Failed")."</strong><br/>";
                        $output .= __("Error Message: Signature verification failed.") ;
                    }
                    elseif(isset($order_details['status']) && in_array($order_details['status'], array('SA','SB','SC'))) {
                        
                        $GLOBALS['PaymentSuccessfull'] = true;
                        $GLOBALS['item_url'] = base64_decode($afpsdg_txn_data['item_url']);
                    }
                    else {
                        $output ="<strong>".__("Payment Failed")."</strong><br/>";
                        switch ($order_details['status']) {
                            case 'SE':
                                $output .= __("Error Message: System error.") ;
                                break;              
                            case 'A':
                                $output .= __("Error Message: Buyer abandoned the pipeline.") ;
                                break;              
                            case 'CE':
                                $output .= __("Error Message: A caller exception occured.") ;
                                break;              
                            case 'PE':
                                $output .= __("Error Message: Payment Method Mismatch Error: Specifies that the buyer does not have the payment method you requested.") ;
                                break;              
                            case 'NP':
                                $output .= __("Error Message: This account type does not support the specified payment method.") ;
                                break;              
                            case 'NM':
                                $output .= __("Error Message: You are not registered as a third-party caller to make this transaction. Contact Amazon Payments for more information.") ;
                                break;              
                            default:
                                $output .= __("Error Message: Undefined Error.") ;
                                break;
                        }
                        $GLOBALS['error'] = $output;
                    }

                } catch (Amazon_FPS_Exception $ex) {
                    echo("Caught Exception: " . $ex->getMessage() . "\n");
                    echo("Response Status Code: " . $ex->getStatusCode() . "\n");
                    echo("Error Code: " . $ex->getErrorCode() . "\n");
                    echo("Error Type: " . $ex->getErrorType() . "\n");
                    echo("Request ID: " . $ex->getRequestId() . "\n");
                    echo("XML: " . $ex->getXML() . "\n");
                }

                include dirname(dirname(__FILE__)) . '/views/confirm.php';

                break;
            default:
                break;
        }
    }

}

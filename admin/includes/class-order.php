<?php

class OrdersAFPSDG {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	function __construct()
	{
		$this->afpsdg = AFPSDG::get_instance();
		$this->text_domain = $this->afpsdg->get_plugin_slug();
	}

	public function register_post_type()
	{
		$text_domain = $this->afpsdg->get_plugin_slug();
		$labels = array(
			'name'                => _x( 'Orders', 'Post Type General Name', $text_domain ),
			'singular_name'       => _x( 'Order', 'Post Type Singular Name', $text_domain ),
			'menu_name'           => __( 'FPS Digital Goods Orders', $text_domain ),
			'parent_item_colon'   => __( 'Parent Order:', $text_domain ),
			'all_items'           => __( 'All Orders', $text_domain ),
			'view_item'           => __( 'View Order', $text_domain ),
			'add_new_item'        => __( 'Add New Order', $text_domain ),
			'add_new'             => __( 'Add New', $text_domain ),
			'edit_item'           => __( 'Edit Order', $text_domain ),
			'update_item'         => __( 'Update Order', $text_domain ),
			'search_items'        => __( 'Search Order', $text_domain ),
			'not_found'           => __( 'Not found', $text_domain ),
			'not_found_in_trash'  => __( 'Not found in Trash', $text_domain ),
		);
		$args = array(
			'label'               => __( 'orders', $text_domain ),
			'description'         => __( 'AFPSDG Orders', $text_domain ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'excerpt', 'revisions', 'custom-fields', ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 80,
			'menu_icon'           => 'dashicons-clipboard',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities' => array(
   				'create_posts' => false, // Removes support for the "Add New" function
  			),
  			'map_meta_cap' => true,
		);

		register_post_type( 'afpsdg-order', $args );
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
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	/**
	 * Returns the order ID.
	 *
	 * @since     1.0.0
	 *
	 * @return    Numeric    Post or Order ID.
	 */
	public function insert($order_details, $response)
	{ 
		$post = array();
		$post['post_title'] = $order_details['item_quantity'].' '.$order_details['item_name'];
		$post['post_status'] = 'pending';

		$output = '';

		// Add error info in case of failure
		if (!isset($order_details['status']) || !in_array($order_details['status'], array('SA','SB','SC'))) {

			$output .= "<h2>Payment Failure Details</h2>"."\n";
			$output .= __("Payment API call failed. ");
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

			$output .= __("Error Code: ") . $order_details['status'];
			$output .= "\n\n";
		}

		$output .= __("<h2>Order Details</h2>")."\n";
		$output .= __("Order Time: ").date("F j, Y, g:i a",strtotime('now'))."\n";
		$output .= __("Transaction ID: ").$order_details['TransactionId']."\n";
		$output .= __("Request ID: ").$order_details['RequestId']."\n";
		$output .= __("Signaure Verification: "). ($order_details['signatureVerified']? 'Verified':'Unverified')."\n";
		$output .= "--------------------------------"."\n";
		$output .= __("Product Name: ").$order_details['item_name']."\n";
		$output .= __("Quantity:"). $order_details['item_quantity']."\n";
		$output .= __("Amount:"). $order_details['item_price'].' '.$order_details['currency_code']."\n";
		$output .= "--------------------------------"."\n";
		$output .= __("Total Amount:"). $order_details['amount'].' '.$order_details['currency_code']."\n";

		$post['post_content'] = $output;
		$post['post_type'] = 'afpsdg-order';

		$post_id = wp_insert_post( $post );

		add_post_meta($post_id, 'TransactionStatus', $order_details['TransactionStatus'], true);
		add_post_meta($post_id, 'TransactionId', $order_details['TransactionId'], true);
		return $post_id;
	}

	public function update($TransactionId, $order_details)
	{
        $order = new WP_Query( "post_type=afpsdg-order&post_status=publish,pending,draft,private&meta_key=TransactionId&meta_value=".$TransactionId );
        //$order = new WP_Query( "post_type=afpsdg-order&post_status=pending,draft,private");
        $orderId = NULL;
        if($order->have_posts()) {
            $order->the_post();
            $orderId = get_the_ID();

            if(get_post_meta( $orderId, 'TransactionStatus', true ) != 'SUCCESS') {

	            $post = get_post($orderId, ARRAY_A);

	            $post['post_title'] = str_replace('PENDING', $order_details['transactionStatus'], $post['post_title']);
	            
	            if(strpos($post['post_title'], $order_details['transactionStatus']) === false)
	            	$post['post_title'] .= ' - '.$order_details['transactionStatus'];

	            $post['post_status'] = 'publish';
	            if(get_post_meta( $orderId, 'TransactionStatus', true ) != 'PENDING') {
		            $post['post_content'] .= "<h1>Customer Information</h1>\n";
		            $post['post_content'] .= "Name: ".$order_details['buyerName']."\n";
		            $post['post_content'] .= "E-Mail Address: ".$order_details['buyerEmail']."\n";
	        	}
	            $post['post_content'] .= "\n\nUpdate: Status - ".$order_details['statusMessage']."\n";

	            wp_update_post($post);
	            update_post_meta($orderId, 'TransactionStatus', $order_details['transactionStatus']);

            }
        }
        else {

        	$message = "Hello,
        	An order IPN was validated but System could not find its order. Here is the dump of the request.

        	Translation ID: ".$TransactionId."
        	Customer Name: ".$order_details['buyerName']."
        	Customer E-Mail Address: ".$order_details['buyerEmail']."

        	Transaction Amount: ".$order_details['transactionAmount']."
        	Payment Method: ".$order_details['paymentMethod']."
        	Caller Reference: ".$order_details['callerReference']."
        	Status Message: ".$order_details['statusMessage']."

        	";
        	wp_mail( get_option('admin_email'), 'Order Verification Failed', $message );
        }

	}

}
?>

<?php

if(!class_exists('AmazonFPSDG')) {


	class AmazonFPSDG {

		var $afpsdg = null;

		var $API_UserName = null;
		var $API_Password = null;
		var $API_Signature= null;

		protected static $instance = null;

		function __construct()
		{

			$this->afpsdg = AFPSDG::get_instance();

			$this->API_UserName = $this->afpsdg->get_setting('api_username');
			$this->API_Password = $this->afpsdg->get_setting('api_password');
			$this->API_Signature= $this->afpsdg->get_setting('api_signature');
				
			if($this->afpsdg->get_setting('is_live')) 
			{

			}
			else
			{

			}
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

	}
}
?>
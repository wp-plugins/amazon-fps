=== Amazon FPS ===
Contributors: wpecommerce
Donate link: https://wp-ecommerce.net/
Tags: amazon, payment, digital goods, payment gateway, instant payment, commerce, digital downloads, download, downloads, e-commerce, e-store, ecommerce, eshop
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept payments using Amazon Flexible Payments Service (FPS).

== Description ==

This plugin provides you a shortcode to generate a customizable buy button that allows a user to pay for an item using Amazon FPS. 

= Features =

* Sell files, digital goods or downloads using Amazon FPS.
* Sell music, video, ebook, PDF or any other digital media files.
* Allow a user to automatically download the file once the purchase is complete via Amazon FPS.
* View the transactions from your WordPress admin dashboard.

= Shortcode Attributes =

This plugin adds the following shortcode to your site:

[amazonfps]

It supports the following attributes in the shortcode -

    name:
    (string) (required) Name of the product
    Possible Values: 'Awesome Script', 'My Ebook', 'Wooden Table' etc.


    price:
    (number) (required) Price of the product or item
    Possible Values: '9.90', '29.95', '50' etc.

    quantity:
    (number) (optional) Number of products to be charged.
    Possible Values: '1', '5' etc.
    Default: 1

    currency:
    (string) (optional) Currency of the price specified.
    Possible Values: 'USD', 'GBP' etc
    Default: The one set up in Settings area.
    
    url:
    (URL) (optional) URL of the downloadable file.
    Possible Values: http://example.com/my-downloads/product.zip

    button_text:
    (string) (optional) Label of the payment button
    Possible Values: 'Buy Now', 'Pay Now' etc

For detailed documentation visit the [Amazon FPS plugin](https://wp-ecommerce.net/wordpress-amazon-fps-plugin) page

== Usage ==

[amazonfps name="Cool Script" price="50" url="http://example.com/downloads/my-script.zip" button_text="Buy Now"]

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'amazon fps'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading via WordPress Dashboard =

1. Navigate to the "plugins->Add New" from your dashboard
2. Click on "Upload Plugin"
3. Select `amazon-fps.zip` from your computer
4. Click on 'Install Now'
5. Activate the plugin

= Using FTP =

1. Download `amazon-fps.zip`
2. Extract the `amazon-fps.zip` file on your computer
3. Upload the `amazon-fps` directory to the `/wp-content/plugins/` directory
4. Activate the plugin from the Plugin dashboard

== Frequently Asked Questions ==

= Can I have multiple payment buttons on a single page? =

Yes

= Can I use it in a WordPress Widget? =

Yes

= Can I specify a quantity for the item? =

Yes

= Can I customize the button label? =

Yes

= Can I test it in sandbox mode? =

Yes


== Screenshots ==

For screenshots please visit the [Amazon FPS plugin](https://wp-ecommerce.net/wordpress-amazon-fps-plugin) page

== Upgrade Notice ==
None

== Changelog ==

= 1.0 =
* First Release
<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     Amazon_FPS
 *  @copyright   Copyright 2008-2011 Amazon Technologies, Inc.
 *  @link        http://aws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2010-08-28
 */
/******************************************************************************* 
 *    __  _    _  ___ 
 *   (  )( \/\/ )/ __)
 *   /__\ \    / \__ \
 *  (_)(_) \/\/  (___/
 * 
 *  Amazon FPS PHP5 Library
 * 
 */

require_once('.config.inc.php');

afps_require_once('Amazon/CBUI/CBUIRecipientTokenPipeline.php');

class CBUIRecipientTokenPipelineSample {

    function test() {
        $pipeline = new Amazon_FPS_CBUIRecipientTokenPipeline(AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY);

        $pipeline->setMandatoryParameters("callerReferenceRecipientToken",  
                "http://www.mysite.com/call_back.jsp", 5, 5, "True");
        
        //optional parameters
        $pipeline->addParameter("paymentMethod", "CC");
        
        //RecipientToken url
        print "Sample CBUI url for RecipientToken pipeline : " . $pipeline->getUrl() . "\n";
    }
}

CBUIRecipientTokenPipelineSample::test();

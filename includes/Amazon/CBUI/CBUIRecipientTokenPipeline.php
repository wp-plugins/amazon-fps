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

require_once('CBUIPipeline.php');

class Amazon_FPS_CBUIRecipientTokenPipeline extends Amazon_FPS_CBUIPipeline {

    /**
     * @param string $accessKeyId    Amazon Web Services Access Key ID.
     * @param string $secretAccessKey   Amazon Web Services Secret Access Key.
     */
    function Amazon_FPS_CBUIRecipientTokenPipeline($awsAccessKey, $awsSecretKey) {
        parent::Amazon_FPS_CBUIPipeline("Recipient", $awsAccessKey, $awsSecretKey);
    }

    /**
     * Set mandatory parameters required for recipient token pipeline.
     */
    function setMandatoryParameters($callerReference, $returnUrl, 
    		$maxFixedFee, $maxVariableFee, $recipientPaysFee) {
        $this->addParameter("callerReference", $callerReference);
        $this->addParameter("returnURL", $returnUrl);
        $this->addParameter("maxFixedFee", $maxFixedFee);
        $this->addParameter("maxVariableFee", $maxVariableFee);
        $this->addParameter("recipientPaysFee", $recipientPaysFee);
    }

    function validateParameters($parameters) {
        //mandatory parameters for recipient token pipeline
        if (!isset($parameters["maxFixedFee"])) {
            throw new Exception("maxFixedFee is missing in parameters.");
        }

        if (!isset($parameters["maxVariableFee"])) {
            throw new Exception("maxVariableFee is missing in parameters.");
        }

        if (!isset($parameters["recipientPaysFee"])) {
            throw new Exception("recipientPaysFee is missing in parameters.");
        }
    }

}

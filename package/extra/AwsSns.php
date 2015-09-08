<?php
/**
 * Created by PhpStorm.
 * User: viniciusthiengo
 * Date: 9/14/15
 * Time: 1:18 AM
 */

use Aws\Sdk;

class AwsSns {
        public $sns;
        public $platformApplicationArn;


        public function __construct(){
            $sdk = new Sdk([
                'version'  => 'latest',
                'debug'    => false,
                'retries'  => 3,
                'credentials' => [
                    'key'    => CustomCredentials::SNS_KEY,
                    'secret' => CustomCredentials::SNS_SECRET
                ],
                'Sns' => [
                    'region'  => 'sa-east-1'
                ]
            ]);

            $this->sns = $sdk->createSns();
            $this->generatePlatformApplicationArn();
        }


        private function generatePlatformApplicationArn( ){
            $result = $this->sns->createPlatformApplication( array(
                // Name is required
                'Name' => 'PushMessageSerie',
                // Platform is required
                'Platform' => 'GCM',
                // Attributes is required
                'Attributes' => array(
                    // Associative array of custom 'String' key names
                    'PlatformCredential' => __API_KEY__
                ),
            ));
            $this->platformApplicationArn = $result->get('PlatformApplicationArn');

            Util::generateFile('PlataformApplicationArn: '.$this->platformApplicationArn, 'a');
        }


        public function getEndpointArn( $token ){
            $result = $this->sns->createPlatformEndpoint(array(
                // PlatformApplicationArn is required
                'PlatformApplicationArn' => $this->platformApplicationArn,
                // Token is required
                'Token' => $token,
                //'CustomUserData' => 'string',
                'Attributes' => array(
                    // Associative array of custom 'String' key names
                    'Enabled' => 'true'
                ),
            ));

            Util::generateFile('EndpointArn: '.$result->get('EndpointArn'), 'a');

            return( $result->get('EndpointArn') );
        }
    }
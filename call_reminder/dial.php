<?php

class dial_twilio {

    private $_account_sid = 'AC805fa27199239e400a2dc5bf7d451ee5';
    private $_auth_token = '1e340df6fd5b6be603bcad452101e71f';
    private $_from_phone = '403-800-3431';

    public function __construct() {
        require_once('twilio.php');
    }

    public function make_call($call=TRUE, $dial_url='', $to_number='', $voice_type='man',$saying_text='',$playing_file='none') {
        if ($call) {
            // make call request to Twilio
            /*if(!is_null($dial_url)){
                $dial_array=explode ('tAND_', $dial_url);
                $dial_url=implode('&', $dial_array);
            }*/
			
            $twilio = new TwilioRestClient($this->_account_sid, $this->_auth_token);
            $result = $twilio->request('2010-04-01/Accounts/' . $this->_account_sid . '/Calls', 'POST', array('From' => $this->_from_phone, 'To' => $to_number, 'Url' => $dial_url));
        }
    }

    /**
     * Method to generate XML instructions for Twilio call handling.
     *
     * @access public
     * @param int
     * @return void
     */
    public function handle_call($user_id = NULL, $voice_type='man', $saying_script='', $playing_script=NULL) {
        $script = '';
        $playing_file = NULL;
        if (!is_null($saying_script)) {
            $script = $saying_script;
        } else {
            $script = "Remember you have an appointment with a doctor";
        }

        if ((trim($playing_script) != 'none') OR (trim($playing_script) != '')) {
            $playing_file = $playing_script;
        }
        $twilio = new Response();
        $twilio->addSay($script, array('loop' => 2, 'voice' => $voice_type));
        if (!is_null($playing_file))
            $twilio->addPlay($playing_file, array('loop' => 2));
        $twilio->Respond();
    }

}

?>
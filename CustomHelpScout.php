<?php 
class CustomHelpScout
{
	public function __construct($api_key)
	{
		$this->api_key = $api_key;
	}

	public function getMailBoxes()
	{
		return $this->request('https://api.helpscout.net/v1/mailboxes.json');
	}

	public function getAllConversations($mailboxid,$page = 1)
	{
		return $this->request('https://api.helpscout.net/v1/mailboxes/'.$mailboxid.'/conversations.json', $page);
	}

	public function getAllThreads($conversationid)
	{
		return $this->request('https://api.helpscout.net/v1/conversations/'.$conversationid.'.json');
	}

	private function request($url,$page = 1, $fields = null)
	{
		$args = array(
				'method'            => 'GET',
				'headers'           => array(
					'Authorization' => 'Basic ' . base64_encode( $this->api_key  . ':' . 'X' )
				),
				'body'              => array(
					'page'   => $page
				),
			);	
		if($fields) {
			foreach($fields as $key=>$field){	
				$args['body'][$key] = $field;
			}
		}
		$response = wp_remote_request( $url,  $args );
		if ( $response['response']['code'] == '401' ) {
				return false; // Bail early
		}
		$results = wp_remote_retrieve_body( $response );
		$data = json_decode( $results,true );
		return $data;
	}
}
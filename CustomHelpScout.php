<?php
class CustomHelpScout
{
    public function __construct($api_key = '')
    {
        $this->api_key = $api_key;
    }

    public function getMailBoxes()
    {
        return $this->request('https://api.helpscout.net/v2/mailboxes');
    }

    public function getAllConversations($mailboxid, $page = 1)
    {
        $current_user = wp_get_current_user();
        if ($current_user) {
            $email = $current_user->user_email;
            return $this->request('https://api.helpscout.net/v2/conversations?mailbox=' . $mailboxid . '&status=active,open,closed,pending&query=(email:"' . $email . '")', "GET", $page);
        } else {
            return $this->request('https://api.helpscout.net/v2/conversations?mailbox=' . $mailboxid . '&status=active,open,closed,pending', "GET", $page);
        }

    }

    public function getLoggedInUserId($mailboxId)
    {
        $current_user = wp_get_current_user();
        if ($current_user) {
            $email = $current_user->user_email;
            $user = $this->request('https://api.helpscout.net/v2/users?mailbox='.$mailboxId.'&email='.$email);
            if ($user && isset($user['_embedded']['users'][0]['id'])) {
                $userid = $user['_embedded']['users'][0]['id'];
                return $userid;
            } else {
                return false;
            }
        }
    }

    public function getAllThreads($conversationid)
    {
        return $this->request('https://api.helpscout.net/v2/conversations/' . $conversationid . '/threads');
    }

    public function replyToThread($conversationid, $customerId, $text, $userId = '')
    {
        $fields['customer']['id'] = (int)$customerId;
        $fields['text']           = $text;
        if ($userId) {
            $fields['user'] = $userId;
         }   
        return $this->requestReplyThread('https://api.helpscout.net/v2/conversations/' . $conversationid . '/reply', 'POST', '', $fields);
        
    }

    private function requestReplyThread($url, $method = 'GET', $page = 1, $fields = null)
    {
        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-type'  => 'application/json; charset=UTF-8',
            ),
        );
        $args['body']    = json_encode($fields);
        $args['timeout'] = 40;
        $response        = wp_remote_request($url, $args);
        if ($response['response']['code'] == '401') {
            return false; // Bail early
        }

        $data = json_decode($response['body'], true);
        return $data;
    }

    private function request($url, $method = 'GET', $page = 1, $fields = null)
    {
        $args = array(
            'method'  => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-type'  => 'application/json; charset=UTF-8',
            ),
            'body'    => array(
                'page' => $page,
            ),
        );
        if ($fields) {
            foreach ($fields as $key => $field) {
                $args['body'][$key] = $field;
            }
        }

        $args['timeout'] = 40;
        $response        = wp_remote_request($url, $args);
        if ($response['response']['code'] == '401') {
            return false; // Bail early
        }

        $data = json_decode($response['body'], true);
        return $data;
    }

    public function getAccessToken($url, $clientId, $clientSecret)
    {
        $args = array(
            'method' => 'POST',
            'body'   => array(
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ),
        );
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return false;

        } else {
            return json_decode($response['body'], true);

        }
    }
}

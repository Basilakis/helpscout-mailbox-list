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
        return $this->request('https://api.helpscout.net/v2/conversations?mailbox=' . $mailboxid . '&status=active,open', "GET", $page);
    }

    public function getAllThreads($conversationid)
    {
        return $this->request('https://api.helpscout.net/v2/conversations/' . $conversationid . '/threads');
    }

    public function replyToThread($conversationid, $customerId, $text)
    {
        $fields['customer']['id'] = $customerId;
        $fields['text'] = $text;
        return $this->request('https://api.helpscout.net/v2/conversations/' . $conversationid, 'POST', '', $fields);
    }

    private function request($url, $method = 'GET', $page = 1, $fields = null)
    {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'body' => array(
                'page' => $page,
            ),
        );
        if ($fields) {
            foreach ($fields as $key => $field) {
                $args['body'][$key] = $field;
            }
        }
        $args['timeout'] = 40;
        $response = wp_remote_request($url, $args);
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
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
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

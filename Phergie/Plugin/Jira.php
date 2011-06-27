<?php

class Phergie_Plugin_Jira extends Phergie_Plugin_Abstract
{
    const issue_pattern = '/@([a-zA-Z][a-zA-Z0-9_]+-[1-9][0-9]*)/';
    const certify_pattern = '/certify ([a-zA-Z][a-zA-Z0-9_]+-[1-9][0-9]*)/';

    protected $auth = null;
    protected $client = null;
    protected $user = null;
    protected $pass = null;
    protected $url = null;

    public function onConnect()
    {
        $this->user = $this->config['jira.username'];
        $this->pass = $this->config['jira.password'];
        $this->url = $this->config['jira.url'];

        $endpoint = "{$url}/rpc/soap/jirasoapservice-v2?wsdl";

        $this->client = new SoapClient($endpoint);
        $this->login();
    }

    /**
     * Processes acronym lookups and returns results when available, or
     * returns a random action when a lookup returns no results.
     *
     * @return void
     */
    public function onPrivmsg()
    {
        $message = $this->event->getArgument(1);

        if (preg_match(self::issue_pattern, $message)) {
            $this->onGetIssue();
        } else if (preg_match(self::certify_pattern, $message)) {
            $this->onCertifyIssue();
        }
    }

    public function onGetIssue()
    {
        $source = $this->event->getSource();
        $message = $this->event->getArgument(1);

        if (!preg_match(self::issue_pattern, $message, $matches)) {
            return;
        }

        $key = $matches[1];

        try {
            $response = $this->getIssue($key);
        } catch (Exception $e) {
            if ($e->faultcode == 'soapenv:Server.userException') {
                $this->login();
                try {
                    $response = $this->getIssue($key);
                } catch (Exception $e) {
                    return;
                }
            }
        }

        $url = "{$this->url}/{$key}";

        $text = "{$response->key} - {$response->summary} ({$url})";

        $this->doPrivmsg($source, $text);
    }

    public function onCertifyIssue()
    {
        $source = $this->event->getSource();
        $message = $this->event->getArgument(1);

        if (!preg_match(self::certify_pattern, $message, $matches)) {
            return;
        }

        $key = $matches[1];
        $comment = "{$source} has certified this issue via hautebot.";

        try {
            $response = $this->addComment($key, $comment);
        } catch (Exception $e) {
            if ($e->faultcode == 'soapenv:Server.userException') {
                $this->login();
                try {
                    $response = $this->addComment($key, $source);
                } catch (Exception $e) {
                }
            }
        }

        $this->doPrivmsg('#Tech', "{$source} certified {$key}.");
    }

    public function login()
    {
        $this->auth = $this->client->login($this->user, $this->pass);
    }

    public function getIssue($key)
    {
        $response = $this->client->getIssue($this->auth, $key);
        return $response;
    }

    public function addComment($key, $comment)
    {
        $this->client->addComment($this->auth, $key, array('body' => $comment));
    }
}

<?php

namespace lumydev;

use Exception;

class GitHubIssueCreator
{
    public $repositoryOwner;
    public $repositoryName;

    protected $personalAccessToken;

    public $issueTitle;
    public $issueBody;
    public $issueLabels;


    public function __construct(string $repositoryOwner, string $repositoryName, string $personalAccessToken, string $issueTitle, string $issueBody, array $issueLabels)
    {
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
        $this->personalAccessToken = $personalAccessToken;
        $this->issueTitle = htmlentities($issueTitle, ENT_QUOTES, 'UTF-8');
        $this->issueBody = htmlentities($issueBody, ENT_QUOTES, 'UTF-8');
        $this->issueLabels = $issueLabels;
    }

    public function create() : string
    {

        try {
            $post = '{"title":"'. $this->issueTitle .'","body":"' . $this->issueBody . '","labels":'.json_encode($this->issueLabels).'}';

            $headers = [
                'User-Agent: PHP',
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: token ' . $this->personalAccessToken,
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/$this->repositoryOwner/$this->repositoryName/issues");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = json_decode(curl_exec($ch));

            if (isset($response->message) && !empty($response->message)) {
                throw new Exception("GitHub failed to proccess your request. The error message returned is: \"{$response->message}\". This is usually an error with your token key and/or your repo owner / repo name you provided.");
            }

            return "Issue created: {$response->html_url}";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

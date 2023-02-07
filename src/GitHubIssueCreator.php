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


    public function __construct(string $repositoryOwner, string $repositoryName, string $personalAccessToken)
    {
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
        $this->personalAccessToken = $personalAccessToken;
    }

    public function create(string $issueTitle, string $issueBody, array $issueLabels, bool $disableResponse = true) : string
    {

        try {
            $post = '{"title":"'. $issueTitle .'","body":"' . $issueBody . '","labels":'.json_encode($issueLabels).'}';

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

            if (isset($response->message) && !empty($response->message) && !$disableResponse) {
                throw new Exception("GitHub failed to proccess your request. The error message returned is: \"{$response->message}\". This is usually an error with your token key and/or your repo owner / repo name you provided.");
            }else{
                return false;
            }

            if(!$disableResponse){
                return "Issue created: {$response->html_url}";
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}

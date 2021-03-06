<?php
/**
 * Copyright 2014 Openstack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

/**
 * Class GitHubPullRequestManager
 * validates if a pull request belongs to a foundation member or not
 */
final class GitHubPullRequestManager implements IPullRequestManager
{

    static $allowed_pull_request_actions = ['opened', 'reopened'];

    /**
     * @var ITransactionManager
     */
    private $tx_manager;

    public function __construct(ITransactionManager $tx_manager)
    {
        $this->tx_manager = $tx_manager;
    }

    /**
     * @param $payload
     * @param array $params
     * @return void
     * @throws SecurityException
     */
    public function registerPullRequest($payload, $params = [])
    {
        //get sender user
        $data = json_decode($payload, true);
        $repository_data = isset($data['repository']) ? $data['repository'] : null;
        $action = isset($data['action']) ? $data['action'] : null;
        if (!in_array($action, self::$allowed_pull_request_actions)) return;

        $pull_request_data = isset($data['pull_request']) ? $data['pull_request'] : null;
        $organization_data = isset($data['organization']) ? $data['organization'] : null;
        $sender_data = isset($data['sender']) ? $data['sender'] : null;
        if (is_null($repository_data)) return;
        $git_repo = GitHubRepositoryConfiguration::get()->filter(
            ['Name' => $repository_data["name"]]
        )->first();

        if (is_null($git_repo)) return;
        $git_hub_signature_header = isset($params['X-Hub-Signature']) ? $params['X-Hub-Signature'] : null;
        $webhook_secret = $git_repo->WebHookSecret;
        if (!empty($webhook_secret) && !empty($git_hub_signature_header)) {
            // validate hash
            $digest = hash_hmac('sha1', $payload, $webhook_secret);
            $signature = 'sha1=' . $digest;
            if (strcmp($signature, $git_hub_signature_header) != 0)
                throw new SecurityException("X-Hub-Signature does not match with our stored secret");
        }

        $pr = new GitHubRepositoryPullRequest();
        $pr->GitHubRepositoryID = $git_repo->ID;
        $pr->Body = $payload;
        $pr->write();
    }


    /**
     * @param int $batch_size
     * @return int
     */
    public function processPullRequests($batch_size = 100)
    {
        return $this->tx_manager->transaction(function () use ($batch_size) {
            $count = 0;
            foreach (GitHubRepositoryPullRequest::get()->filter(['Processed' => 0])->limit($batch_size) as $pr) {
                try {
                    $data              = json_decode($pr->Body, true);
                    $sender_data       = isset($data['sender']) ? $data['sender'] : null;
                    $github_user       = !is_null($sender_data) ? (isset($sender_data['login']) ? $sender_data['login'] : null) : null;

                    $git_repo = $pr->GitHubRepository();

                    if(is_null($git_repo))
                        throw new Exception(sprintf("missing git repo config for PR %s.",$pr->ID ));

                    $reject_reason = GitHubRepositoryPullRequest::RejectReason_Approved;

                    if (is_null($github_user)) {
                        throw new SecurityException("missing github user");
                    }

                    if (empty($reject_reason)) {
                        $member = Member::get()->filter('GitHubUser', $github_user)->first();
                        if (is_null($member)) {
                            $reject_reason = GitHubRepositoryPullRequest::RejectReason_NotMember;
                        }
                        if (!$member->isFoundationMember()) {
                            $reject_reason = GitHubRepositoryPullRequest::RejectReason_NotFoundationMember;
                        }
                    }

                    if (empty($reject_reason)) {
                        // verify that member belongs to configured ccla teams
                        $can_pull_request = false;
                        foreach ($git_repo->AllowedTeams() as $team) {
                            if ($member->Teams()->find('ID', $team->ID)) {
                                $can_pull_request = true;
                                break;
                            }
                        }
                        if (!$can_pull_request) {
                            $reject_reason = GitHubRepositoryPullRequest::RejectReason_NotCCLATeam;
                        }
                    }

                    $pr->RejectReason = $reject_reason;

                    if($pr->RejectReason != GitHubRepositoryPullRequest::RejectReason_Approved)
                        $this->rejectPullRequest($pr);

                    $pr->markAsProcessed($reject_reason);
                    $pr->write();

                    $count++;
                }
                catch (Exception $ex) {
                    echo $ex->getMessage().PHP_EOL;
                    SS_Log::log($ex->getMessage(), SS_Log::WARN);
                }
            }
        });
    }

    /**
     * @param GitHubRepositoryPullRequest $pr
     * @throws Exception
     */
    private function rejectPullRequest(GitHubRepositoryPullRequest $pr)
    {

        $client            = new \Github\Client();
        $data              = json_decode($pr->Body, true);
        $pull_request_data = isset($data['pull_request']) ? $data['pull_request'] : null;
        $git_repo          = $pr->GitHubRepository();

        if (!defined('GITHUB_API_OAUTH2TOKEN'))
            throw new InvalidArgumentException();

        $client->authenticate(GITHUB_API_OAUTH2TOKEN, null, \Github\Client::AUTH_HTTP_TOKEN);

        $comment_body = '';
        switch ($pr->RejectReason) {
            case GitHubRepositoryPullRequest::RejectReason_NotMember: {
                $comment_body = $git_repo->RejectReasonNotMember;
            }
            break;
            case GitHubRepositoryPullRequest::RejectReason_NotFoundationMember: {
                $comment_body = $git_repo->RejectReasonNotFoundationMember;
            }
            break;
            case GitHubRepositoryPullRequest::RejectReason_NotCCLATeam: {
                $comment_body = $git_repo->RejectReasonNotCCLATeam;
            }
            break;
        }
        if(empty($comment_body))
            throw new Exception(sprintf("comment_body is empty for reason %s", $pr->RejectReason));

        // close pull request
        $params = [
            'state' => 'closed'
        ];
        $base  = $pull_request_data['base'];
        $head  = $pull_request_data['head'];
        $user  = $head['user'];
        $repo  = $base['repo'];
        $owner = $repo['owner'];
        $id    = $pull_request_data['number'];
        $client->api('pull_request')->update($owner['login'], $repo['name'], $id, $params);
        // comment close reason
        $params         = [];
        $params['body'] = $comment_body;
        $client->api('issue')->comments()->create($owner['login'], $repo['name'], $id, $params);
    }
} 
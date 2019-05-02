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
 * Class DupesMembers_Controller
 */
final class DupesMembers_Controller extends AbstractController {

    static $url_handlers = [
        'GET merge-account/merging'      => 'mergingAccount',
        'GET merge-account/revoke'       => 'revokeMergingAccount',
        'GET merge-account'              => 'mergeAccount',
        'GET delete-account/deleting'    => 'deletingAccount',
        'GET delete-account/revoke'      => 'revokeDeletingAccount',
        'GET delete-account'             => 'deleteAccount',
        'GET $CONFIRMATION_TOKEN/merge'  => 'mergeAccount',
        'GET $CONFIRMATION_TOKEN/delete' => 'deleteAccount',
    ];

    /**
     * @var array
     */
    static $allowed_actions = [
        'mergeAccount',
        'deleteAccount',
        'deletingAccount',
        'mergingAccount',
        'revokeMergingAccount',
        'revokeDeletingAccount',
    ];

    /**
     * @var SapphireDupeMemberDeleteRequestRepository
     */
    private $delete_request_repository;

    /**
     * @var SapphireDupeMemberDeleteRequestRepository
     */
    private $merge_request_repository;


    /**
     * @var DupesMembersManager 
     */
    private $manager;

    public function __construct()
    {
        parent::__construct();

        $this->delete_request_repository = new SapphireDupeMemberDeleteRequestRepository;
        $this->merge_request_repository  = new SapphireDupeMemberMergeRequestRepository;

        $this->manager = new DupesMembersManager(new SapphireDupesMemberRepository,
            new DupeMemberMergeRequestFactory,
            new DupeMemberDeleteRequestFactory,
            $this->merge_request_repository,
            $this->delete_request_repository,
            new SapphireDeletedDupeMemberRepository,
            new DeletedDupeMemberFactory,
            new SapphireCandidateNominationRepository,
            new SapphireNotMyAccountActionRepository,
            new NotMyAccountActionFactory,
            SapphireTransactionManager::getInstance(),
            SapphireBulkQueryRegistry::getInstance());
    }


    public function init()
    {
        parent::init();
        Page_Controller::AddRequirements();
        SweetAlert2Dependencies::renderRequirements();

        $js_files = [
            "themes/openstack/javascript/jquery.ticker.js",
            "themes/openstack/javascript/jquery.tools.min.js",
            "themes/openstack/javascript/jcarousellite.min.js",
            "themes/openstack/javascript/navigation.js",
            "themes/openstack/javascript/filetracking.jquery.js",
        ];

        foreach($js_files as $js_file)
            Requirements::javascript($js_file);

    }
    /**
     * @return string|void
     */
    public function mergeAccount() {

        $token = $this->request->param('CONFIRMATION_TOKEN');
        try{
            $current_member = Member::currentUser();
            if(is_null($current_member))
                return Controller::curr()->redirect("Security/login?BackURL=" . urlencode($_SERVER['REQUEST_URI']));

            if(!empty($token)){
                Session::set("DUP_MEMBER_MERGE_TOKEN", $token);
                return Controller::curr()->redirect('dupes-members/merge-account');
            }

            $token = Session::get("DUP_MEMBER_MERGE_TOKEN");

            $request = $this->merge_request_repository->findByConfirmationToken($token);

            if(is_null($request) || $request->isVoid())
                throw new DuperMemberActionRequestVoid();

            $dupe_account =  $request->getDupeAccount();

            if($dupe_account->getEmail() != $current_member->getEmail()){
                throw new AccountActionBelongsToAnotherMemberException;
            }

            return $this->renderWith(array('DupesMembers_MergeAccountConfirm', 'Page'), array(
                'DupeAccount'       => $request->getDupeAccount(),
                'CurrentAccount'    => $request->getPrimaryAccount(),
            ));
        }
        catch(AccountActionBelongsToAnotherMemberException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            $request = $this->merge_request_repository->findByConfirmationToken($token);
            return $this->renderWith(array('DupesMembers_belongs_2_another_user','Page'), array(
                'UserName' => $request->getDupeAccount()->getEmail()
            ));
        }
        catch(DuperMemberActionRequestVoid $ex2)
        {
            SS_Log::log($ex2,SS_Log::WARN);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
    }

    /**
     * @return HTMLText
     */
    public function mergingAccount(){
        try{
            $current_member = Member::currentUser();
            if(is_null($current_member))
                return Controller::curr()->redirect("Security/login?BackURL=" . urlencode($_SERVER['REQUEST_URI']));

            $token = Session::get("DUP_MEMBER_MERGE_TOKEN");

            $request = $this->merge_request_repository->findByConfirmationToken($token);

            if(is_null($request) ||  $request->isVoid())
                throw new DuperMemberActionRequestVoid();

            $dupe_account =  $request->getDupeAccount();

            if($dupe_account->getEmail() != $current_member->getEmail()){
                throw new AccountActionBelongsToAnotherMemberException;
            }

            $any_account_has_gerrit = $request->getDupeAccount()->isGerritUser() || $request->getPrimaryAccount()->isGerritUser();
            $any_account_has_gerrit = $any_account_has_gerrit? 'true' : 'false';

            Requirements::customScript('var any_account_has_gerrit = '.$any_account_has_gerrit.';');
            Requirements::javascript('dupe_members/javascript/dupe.members.merge.action.js');

            Session::clear("DUP_MEMBER_MERGE_TOKEN");

            return $this->renderWith(array('DupesMembers_MergeAccountMerging', 'Page'), array(
                'DupeAccount'       => $request->getDupeAccount(),
                'CurrentAccount'    => $request->getPrimaryAccount(),
                'ConfirmationToken' => $token,
            ));

        }
        catch(AccountActionBelongsToAnotherMemberException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            $request = $this->merge_request_repository->findByConfirmationToken($token);
            return $this->renderWith(array('DupesMembers_belongs_2_another_user','Page'), array(
                'UserName' => $request->getDupeAccount()->getEmail()
            ));
        }
        catch(DuperMemberActionRequestVoid $ex2)
        {
            SS_Log::log($ex2,SS_Log::WARN);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
    }

    public function revokeMergingAccount(){
        try{
            $current_member = Member::currentUser();
            if(is_null($current_member))
                return Controller::curr()->redirect("Security/login?BackURL=" . urlencode($_SERVER['REQUEST_URI']));

            $token = Session::get("DUP_MEMBER_MERGE_TOKEN");

            $request = $this->merge_request_repository->findByConfirmationToken($token);

            if(is_null($request) ||  $request->isVoid())
                throw new DuperMemberActionRequestVoid();

            $request->revoke();
            $request->write();
            Session::clear("DUP_MEMBER_MERGE_TOKEN");
            Controller::curr()->redirect(Director::absoluteBaseURL());
        }
        catch(AccountActionBelongsToAnotherMemberException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            $request = $this->merge_request_repository->findByConfirmationToken($token);
            return $this->renderWith(array('DupesMembers_belongs_2_another_user','Page'), array(
                'UserName' => $request->getDupeAccount()->getEmail()
            ));
        }
        catch(DuperMemberActionRequestVoid $ex2)
        {
            SS_Log::log($ex2,SS_Log::WARN);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
    }

    public function currentRequestAnyAccountHasGerrit(){
        $token = $this->request->param('CONFIRMATION_TOKEN');
        if(empty($token)) return false;
        $request = $this->merge_request_repository->findByConfirmationToken($token);
        if(is_null($request) ||  $request->isVoid())
            return false;
        $any_account_has_gerrit = $request->getDupeAccount()->isGerritUser() || $request->getPrimaryAccount()->isGerritUser();
        return $any_account_has_gerrit;
    }

    /**
     * @return string|void
     */
    public function deleteAccount() {

        $token = $this->request->param('CONFIRMATION_TOKEN');

        try{
            $current_member = Member::currentUser();
            if(is_null($current_member))
                return Controller::curr()->redirect("Security/login?BackURL=" . urlencode($_SERVER['REQUEST_URI']));

            if(!empty($token)){
                Session::set("DUP_MEMBER_DELETE_TOKEN", $token);
                return Controller::curr()->redirect('/dupes-members/delete-account');
            }

            $token = Session::get("DUP_MEMBER_DELETE_TOKEN");

            $request = $this->delete_request_repository->findByConfirmationToken($token);

            if(is_null($request) || $request->isVoid())
                throw new DuperMemberActionRequestVoid();

            $dupe_account =  $request->getDupeAccount();

            if($dupe_account->getEmail() != $current_member->getEmail()){
                throw new AccountActionBelongsToAnotherMemberException;
            }

            return $this->renderWith(array('DupesMembers_DeleteAccountConfirm', 'Page'), array(
                'DupeAccount'       => $request->getDupeAccount(),
                'CurrentAccount'    => $request->getPrimaryAccount(),
            ));

        }
        catch(AccountActionBelongsToAnotherMemberException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            $request = $this->delete_request_repository->findByConfirmationToken($token);
            return $this->renderWith(array('DupesMembers_belongs_2_another_user','Page'), array(
                'UserName' => $request->getDupeAccount()->getEmail()
            ));
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
    }
    /**
     * @return string|void
     */
    public function deletingAccount() {

        try{
            $current_member = Member::currentUser();
            if(is_null($current_member))
                return Controller::curr()->redirect("Security/login?BackURL=" . urlencode($_SERVER['REQUEST_URI']));
            $token = Session::get("DUP_MEMBER_DELETE_TOKEN");

            $request = $this->delete_request_repository->findByConfirmationToken($token);

            if(is_null($request) || $request->isVoid())
                throw new DuperMemberActionRequestVoid();

            $dupe_account =  $request->getDupeAccount();

            if($dupe_account->getEmail() != $current_member->getEmail()){
                throw new AccountActionBelongsToAnotherMemberException;
            }

            Requirements::javascript('dupe_members/javascript/dupe.members.delete.action.js');

            Session::clear("DUP_MEMBER_DELETE_TOKEN");

            return $this->renderWith(array('DupesMembers_DeleteAccountDeleting', 'Page'), array(
                    'DupeAccount'       => $request->getDupeAccount(),
                    'CurrentAccount'    => $request->getPrimaryAccount(),
                    'ConfirmationToken' => $token,
            ));

        }
        catch(AccountActionBelongsToAnotherMemberException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            $request = $this->delete_request_repository->findByConfirmationToken($token);
            return $this->renderWith(array('DupesMembers_belongs_2_another_user','Page'), array(
                'UserName' => $request->getDupeAccount()->getEmail()
            ));
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
    }

    function revokeDeletingAccount(){
        try{
            $current_member = Member::currentUser();
            if(is_null($current_member))
                return Controller::curr()->redirect("Security/login?BackURL=" . urlencode($_SERVER['REQUEST_URI']));

            $token = Session::get("DUP_MEMBER_DELETE_TOKEN");

            $request = $this->delete_request_repository->findByConfirmationToken($token);

            if(is_null($request) ||  $request->isVoid())
                throw new DuperMemberActionRequestVoid();

            $request->revoke();
            $request->write();
            Session::clear("DUP_MEMBER_DELETE_TOKEN");
            Controller::curr()->redirect(Director::absoluteBaseURL());
        }
        catch(AccountActionBelongsToAnotherMemberException $ex1){
            SS_Log::log($ex1,SS_Log::WARN);
            $request = $this->merge_request_repository->findByConfirmationToken($token);
            return $this->renderWith(array('DupesMembers_belongs_2_another_user','Page'), array(
                'UserName' => $request->getDupeAccount()->getEmail()
            ));
        }
        catch(DuperMemberActionRequestVoid $ex2)
        {
            SS_Log::log($ex2,SS_Log::WARN);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
        catch(Exception $ex){
            SS_Log::log($ex,SS_Log::ERR);
            return $this->renderWith(array('DupesMembers_error','Page'));
        }
    }
} 
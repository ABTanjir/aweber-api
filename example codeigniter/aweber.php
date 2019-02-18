<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
ob_start();
require_once(APPPATH.'libraries/aweber_api/aweber_api.php');
/*
	this is codeigniter library class i was using on one of my client's project
	hope this will also be helpful for you
	************************************************USES************************************************
	#1 Copy the  [aweber_api] folder in [application/library] folder of your codeigniter project 
	#2 Copy the  [config/aweber.php] file in [application/config] folder of your codeigniter project 
	#3 Copy this file in [application/library] so the destination of this file should be [application/library/aweber_demo_2.php]
	#4 Load the library in your controller and use its magic
*/
class Aweber {

    protected $_consumerKey;
    protected $_consumerSecret;
    protected $_accessTokenSecret;
    protected $_accessToken;
    protected $_appId;

    function __construct($params = array()){
        $this->_ci =& get_instance();
        $this->_ci->config->load('aweber');
        #initialize all variable from config with an underscore prefix
        $this->_initialize($params);

        
        $this->consumerKey      = $this->_consumerKey;
        $this->consumerSecret   = $this->_consumerSecret;
        $this->accessSecret     = $this->_accessTokenSecret;
        $this->accessKey        = $this->_accessToken;

        $this->application      = new AWeberAPI($this->consumerKey, $this->consumerSecret);
        $this->account          = $this->application->getAccount($this->accessKey, $this->accessSecret);
    }

    public function _initialize($params = array()){
		$this->_response = '';
		foreach ($params as $key => $val){
			$this->{'_'.$key} = (isset($this->{'_'.$key}) ? $val : $this->_ci->config->item($key));
		}
	}

    //-------------------------------------------------------------------------------------------------------
    //Done
    function getList($listUniqueID) {
        if(!empty($listUniqueID)){
            try {
                return $this->account->lists->find(array('name' => $listUniqueID));
            }catch(AWeberAPIException $AWexc) {
                return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
            }catch(Exception $exc) {
                return array('success' => 0, 'message' => $exc);
            }
        }
        return array('success' => 0, 'message' => 'No unique list ID supplied');
    }

    function getLists(){
        return $this->account->lists->data['entries'];
    }

    //Done
    function listSubscribers($listUniqueID){
        if(!empty($listUniqueID)){
            $subscribers = array();
            foreach ($this->account->lists as $list) {
                if($list->unique_list_id === $listUniqueID){
                    foreach ($list->subscribers as $subscriber) {
                        array_push($subscribers, array('name' => $subscriber->name, 'email' => $subscriber->email));
                    }
                }
            }
            return array('success' => 1, 'message' => 'Subscribers', 'subscriber' => $subscribers);
        }
        return array('success' => 0, 'message' => 'No unique list id supplied');
    }

    //Done
    function getListSubscribers($listUniqueID){
        if(!empty($listUniqueID)){
            $list = $this->getList($listUniqueID);

            $listUrl = "{$list[0]->url}/subscribers";
            $subscribers_collection = $this->account->loadFromUrl($listUrl);

            $subscribers = array();
            foreach ($subscribers_collection as $subscriber) {
                array_push($subscribers, array('name' => $subscriber->name, 'email' => $subscriber->email));
            }
            return $subscribers;
        }
        return "No unique list id supplied.";
    }

    //Done
    function getSubscriber($email) {
        if(!empty($email)){
            try {
                $subscriber = $this->account->findSubscribers(array('email' => $email));
                return $subscriber->data;
            }catch(AWeberAPIException $AWexc) {
                return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
            }catch(Exception $exc){
                return array('success' => 0, 'message' => $exc);
            }
        }
        return "No email id supplied to check.";
    }

    //Done
    function addSubscriber($subscriber, $listUniqueID) {
        try {
            $list = $this->getList($listUniqueID);
            $list = $list[0];
            $listUrl = "/accounts/{$this->account->id}/lists/{$list->id}";
            $list = $this->account->loadFromUrl($listUrl);

            $newSubscriber = $list->subscribers->create($subscriber);
            return array('success' => 1, 'message' => "Subscriber {$subscriber['name']} ({$subscriber['email']}) added to {$listUniqueID}", 'subscriber' => $newSubscriber);
        }catch(AWeberAPIException $AWexc) {
            return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
        }catch(Exception $exc) {
            return array('success' => 0, 'message' => $exc);
        }
    }

    //Done
    function updateSubscriber($email, $updates = array()){
        if(!empty($email)){
            try{
                $subscriber = $this->account->findSubscribers(array('email' => $email));
                $subscriber = $subscriber[0];
                if(!empty($subscriber)){
                    foreach ($updates as $key => $value) {
                        $subscriber->$key = $value;
                    }
                    $update = $subscriber->save();
                    return $update;
                }
                return "No subscriber found.";
            }catch(AWeberAPIException $AWexc) {
                return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
            }catch(Exception $exc){
                return array('success' => 0, 'message' => $exc);
            }
        }
        return "No email address supplied.";
    }

    //Done
    function moveSubscriber($email, $new_listUniqueID){
        try{
            $subscriber = $this->account->findSubscribers(array('email' => $email));
            $subscriber = $subscriber[0];

            $list = $this->account->lists->find(array('name' => $new_listUniqueID));
            $dest_list = $list[0];
            $move = $subscriber->move($dest_list);
            print_r($subscriber->delete());
            return array('success' => 1, 'message' => "Subscriber {$email} moved to {$new_listUniqueID}", 'subscriber' => $move);
        }catch(AWeberAPIException $AWexc) {
            return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
        }catch(Exception $exc){
            return array('success' => 0, 'message' => $exc);
        }
    }
    //tasting
    function moveOrAddSubscriber($subscriber_data, $new_listUniqueID){
        try{
            $subscriber = $this->account->findSubscribers(array('email' => $subscriber_data['email']));
            $subscriber = $subscriber[0];

            $list = $this->account->lists->find(array('name' => $new_listUniqueID));
            $dest_list = $list[0];

            if(!$subscriber){
                $this->addSubscriber($subscriber_data, $new_listUniqueID);
            }else{
                if($subscriber->move($dest_list)){
                    $subscriber->delete();
                }
                return array('success' => 1, 'message' => "Subscriber {$email} moved to {$new_listUniqueID}", 'subscriber' => $move);
            }
        }catch(AWeberAPIException $AWexc) {
            return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
        }catch(Exception $exc){
            return array('success' => 0, 'message' => $exc);
        }
    }

    //Done
    function subscribe($email, $listUniqueID){
        if(!empty($email) && !empty($listUniqueID)){
            try{
                $list = $this->getList($listUniqueID);
                $list = $list[0];
                $list_id = $list->id;

                $subscriber = $this->account->findSubscribers(array('email' => $email));
                $subscription_lists = $subscriber->data['entries'];

                if(!empty($subscription_lists)){
                    foreach ($subscription_lists as $key => $subscription) {
                        $self_link = $subscription['self_link'];
                        $self_link = explode('/', $self_link);
                        foreach ($self_link as $value) {
                            if(@(int)$value === (int)$list_id){
                                $subscription_key = $key;
                            }
                        }
                    }

                    if(isset($subscription_key)){
                        $subscriber = $subscriber[$subscription_key];
                    
                        $subscriber->status = 'subscribed';
                        $save = $subscriber->save();
                        return array('success' => $save, 'message' => "Subscriber {$email} is subscribed to {$listUniqueID}");
                    }else{
                        return array('success' => 0, 'message' => "No subscriber found for given Unique List ID: {$listUniqueID}");
                    }                        
                }
                return array('success' => 0, 'message' => 'No subscriber found');
            }catch(AWeberAPIException $AWexc) {
                return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
            }catch(Exception $exc){
                return array('success' => 0, 'message' => $exc);
            }
        }
        return "No email address supplied.";
    }

    //Done
    function unsubscribe($email, $listUniqueID){
        if(!empty($email) && !empty($listUniqueID)){
            try{
                $list = $this->getList($listUniqueID);
                $list = $list[0];
                $list_id = $list->id;

                $subscriber = $this->account->findSubscribers(array('email' => $email));
                $subscription_lists = $subscriber->data['entries'];

                if(!empty($subscription_lists)){
                    foreach ($subscription_lists as $key => $subscription) {
                        $self_link = $subscription['self_link'];
                        $self_link = explode('/', $self_link);
                        foreach ($self_link as $value) {
                            if(@(int)$value === (int)$list_id){
                                $subscription_key = $key;
                            }
                        }
                    }

                    if(isset($subscription_key)){
                        $subscriber = $subscriber[$subscription_key];
                    
                        $subscriber->status = 'unsubscribed';
                        $save = $subscriber->save();
                        return array('success' => $save, 'message' => "Subscriber {$email} is unsubscribed from {$listUniqueID}");
                    }else{
                        return array('success' => 0, 'message' => "No subscriber found for given Unique List ID: {$listUniqueID}");
                    }                        
                }
                return array('success' => 0, 'message' => 'No subscriber found');
            }catch(AWeberAPIException $AWexc) {
                return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
            }catch(Exception $exc){
                return array('success' => 0, 'message' => $exc);
            }
        }
        return "No email address supplied.";
    }

    //Done
    function deleteSubscriber($email, $listUniqueID){
        if(!empty($email) && !empty($listUniqueID)){
            try{
                $list = $this->getList($listUniqueID);//$this->account->lists->find(array('name' => $listUniqueID));//
                $list = $list[0];
                $list_id = $list->id;

                $subscriber = $this->account->findSubscribers(array('email' => $email));
                $subscription_lists = $subscriber->data['entries'];

                if(!empty($subscription_lists)){
                    foreach ($subscription_lists as $key => $subscription) {
                        $self_link = $subscription['self_link'];
                        $self_link = explode('/', $self_link);
                        foreach ($self_link as $value) {
                            if(@(int)$value === (int)$list_id){
                                $subscription_key = $key;
                            }
                        }
                    }

                    if(isset($subscription_key)){
                        $subscriber = $subscriber[$subscription_key];
                    
                        $delete = $subscriber->delete();
                        return array('success' => $delete, 'message' => "Subscriber {$email} is deleted from {$listUniqueID}");
                    }else{
                        return array('success' => 0, 'message' => "No subscriber found for given Unique List ID: {$listUniqueID}");
                    }                        
                }
                return array('success' => 0, 'message' => 'No subscriber found');
            }catch(AWeberAPIException $AWexc) {
                /*$aweberExc = "<h3>AWeberAPIException:</h3>";
                $aweberExc .= " <li> Type: $AWexc->type              <br>";
                $aweberExc .= " <li> Msg : $AWexc->message           <br>";
                $aweberExc .= " <li> Docs: $AWexc->documentation_url <br>";
                $aweberExc .= "<hr>";*/
                return array('success' => 0, 'message' => array('type' => $AWexc->type, 'message' => $AWexc->message, 'docs' => $AWexc->documentation_url));
            }catch(Exception $exc){
                return array('success' => 0, 'message' => $exc);
            }
        }
        return array('success' => 0, 'message' => 'No email address and/or Aweber unique list ID supplied.');
    }


    
}


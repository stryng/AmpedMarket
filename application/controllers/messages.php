<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('my_session');
		$this->load->model('messages_model');
		$this->load->model('users_model');
	}

	public function inbox(){

		$data['title'] = 'Inbox';
		$data['page'] = 'messages/inbox';
		$userId = $this->my_session->userdata('id');
		$data['messages'] = $this->messages_model->messages($userId);
		if($data['messages'] === NULL)
			$data['returnMessage'] = 'You have no messages in your inbox';
		
		$this->load->library('layout',$data);
	}

	//Delete a message.
	public function delete($messageHash){

		$userId = $this->my_session->userdata('id');

		//Check if user wants to delete all messages
		if($messageHash == "all") {
			$userMessages = $this->messages_model->messages($userId); //Load all messages for current user
			if($userMessages){
				foreach($userMessages as $message) {
					$this->messages_model->deleteMessage($message['messageHash']);
				}
			}
			$data['title'] = 'Messages Deleted';
			$data['returnMessage'] = 'All messages have been permentantly deleted! ';
			$userId = $this->my_session->userdata('id');
			$data['messages'] = $this->messages_model->messages($userId);
			if($data['messages'] === NULL)
				$data['returnMessage'] .= 'You have no messages in your inbox';
		//Otherwise user wants to delete a specific message		
		} else {
			$messageInfo = $this->messages_model->getMessage($messageHash);
			if($messageInfo==NULL){ //Could not find this message, throw an error
				$data['title'] = 'Message Not Found';
				$data['returnMessage'] = 'This message cannot be found.';
				$userId = $this->my_session->userdata('id');
				$data['messages'] = $this->messages_model->messages($userId);
				if($data['messages'] === NULL)
					$data['returnMessage'] .= 'You have no messages in your inbox';
	 		} else {
				if(($messageInfo['fromId']==$userId)||($messageInfo['toId']==$userId)){ //User owns this message, delete ie
					$this->messages_model->deleteMessage($messageHash);
					$data['title'] = 'Message Deleted';
					$data['returnMessage'] = 'This message has been deleted!';

					$userId = $this->my_session->userdata('id');
					$data['messages'] = $this->messages_model->messages($userId);
					if($data['messages'] === NULL)
						$data['returnMessage'] .= ' You have no messages in your inbox';

				} else {
					$data['title'] = 'Forbidden';
					$data['returnMessage'] = 'You do not have permission to delete this messsage!';
					$userId = $this->my_session->userdata('id');
					$data['messages'] = $this->messages_model->messages($userId);
					if($data['messages'] === NULL)
						$data['returnMessage'] .= 'You have no messages in your inbox';

				}
			}
		}
		$data['page'] = 'messages/inbox';
		$this->load->library('layout',$data);
	}

	public function read($messageHash){
		$messageInfo = $this->messages_model->getMessage($messageHash); //Look up the requested message
		if($messageInfo === NULL){  //Display an error if there is no matching message
			$data['title'] = 'Inbox';
			$data['page'] = 'messages/inbox';
			$data['returnMessage'] = 'This message cannot be found.';
			$userId = $this->my_session->userdata('id');
			$data['messages'] = $this->messages_model->messages($userId);

			if($data['messages'] === NULL)
				$data['returnMessage'] .= 'You have no messages in your inbox';
		
		} else { //There is matching messages, begin outputting
			if($this->my_session->userdata('id') == $messageInfo['toId'] ||
			   $this->my_session->userdata('id') == $messageInfo['fromId'] ){ //Show messages from or to the current user.

				//Mark the message as read
				$this->messages_model->setMessageViewed($messageInfo['id']);
			
				$data['title'] = substr($messageInfo['subject'], 0, 40).'...';	
				$data['page'] = 'messages/read';

				$data['fromUser'] = $this->users_model->get_user(array('id' => $messageInfo['fromId']));
				$data['subject'] = $messageInfo['subject'];
				$data['message'] = $messageInfo['message'];
				$data['messageHash'] = $messageInfo['messageHash'];
			
				if($messageInfo['encrypted']==1){ $data['isEncrypted']=1; } else { $data['isEncrypted'] = 0; } //Check if message is encrypted.

			} else { //Otherwise the user should not be able to view this message
				$data['title'] = 'Inbox';	
				$data['page'] = 'messages/inbox';
				$data['returnMessage'] = 'Not authorized to view this message.';

				$userId = $this->my_session->userdata('id');
				$data['messages'] = $this->messages_model->messages($userId);
				if($data['messages'] === NULL)
					$data['returnMessage'] .= 'You have no messages in your inbox';
			}
		}
		$this->load->library('layout',$data);
	}

	public function send($toHash = NULL){
		//Include the required files for clientside encryption
		$data['header_meta'] = $this->load->view('messages/encryptionHeader', NULL, true);
		$data['returnMessage'] = '';

		$this->load->library('form_validation');
		$data['title'] = 'Send Message';
		$data['page'] = 'messages/send';
		$data['hiddenFields'] = array();


		//Check if the user is replying to a message
		$messageReply = $this->messages_model->getMessage($toHash); //Look up the requested message
		if(isset($messageReply)) { //The user is replying to an existing message
			$fromUser = $this->users_model->get_user(array('id' => $messageReply['fromId']));
			//Forward thread hash to the form.
			$data['hiddenFields'] = array('threadHash' => $messageReply['threadHash']);
			$data['to'] = $fromUser['userName'];
			$data['subject'] = $messageReply['subject'];
		}


		//Otherwise check if the provided user hash matches a user load their username.
		elseif($toHash !== NULL){ //A user was specified
			$recipient = $this->users_model->get_user(array('userHash' => $toHash));
			if($recipient['userName'] === NULL){ //Check if a user is found with the specified userHash
				$data['returnMessage'] = 'The requested username could not be found';
				$data['to'] = '';
			} else { //A matching user was found.
				$data['to'] = $recipient['userName'];
			}

		//If no user hash was specified try use the submitted value
		} else {
			if($this->input->post('recipient') == FALSE){ //No username was inputted
				$data['to'] = '';
			} else { //A username was provided
				$recipient = $this->users_model->get_user(array('userName' => $this->input->post('recipient')));
				if($recipient['userName'] != NULL) { //Check if the username is valid.
					$data['to'] = $recipient['userName'];
				} else {
					$data['returnMessage'] = 'The requested username could not be found..';
					$data['subject'] = $this->input->post('subject');
					$data['to'] = '';
				}
			}
		}

		$recipient = $this->users_model->get_user(array('userName' => $data['to']));
	
		$data['publickey'] = $this->users_model->get_pubKey_by_id($recipient['id']);
		$fingerprint = $this->users_model->get_pubKey_by_id($recipient['id'],1);
		if($data['publickey']!=''){ 
			$data['returnMessage'] .= 'This message will be encrypted automatically if you have javascript enabled.<br />';  
		} 

		//Need to check if provided username is valid
                if ($this->form_validation->run('sendmessage') == FALSE){
			//Submitted form didn't pass validation, display it again
			if($this->input->post('subject')) {	
				$data['subject'] = $this->input->post('subject');
			}
                } else  {
			//Add message to database
			if($recipient!==FALSE){ //Check if recipient is found.

				//Check if the message appears to be encrypted.
				$checkEncrypt = stripos($this->input->post('message'), '-----BEGIN');
				if ($checkEncrypt!== FALSE) {
				    $isEncrypted = 1;
				} else {  $isEncrypted = 0; }

				$messageHash = $this->general->uniqueHash('messages','messageHash');

				//Check if this is a message is a reply to an existing conversation
				if($this->input->post('threadHash')){
					//Check if this is a valid threadHash
					$threadHash = $this->input->post('threadHash');
					if($this->messages_model->getMessageByThread($threadHash, TRUE)<=0) { //This thread has no messages
						$threadHash = $this->general->uniqueHash('messages','threadHash'); //Create new thread
					}
				} else {
					$threadHash = $this->general->uniqueHash('messages','threadHash');
				}

				$messageText = $this->input->post('message');

				if($isEncrypted == 0 && $this->input->post('PGPencryption') == '1'){
					$messageText = $this->general->encryptPGPmessage($fingerprint,$messageText);
					$isEncrypted = 1;
				}
				if($isEncrypted == 0 && $recipient['forcePGPmessage'] == '1'){
					$messageText = $this->general->encryptPGPmessage($fingerprint,$messageText);
					$isEncrypted = 1;
				}


				$messageArray = array(  'toId' => $recipient['id'],
				        'fromId' => $this->my_session->userdata('id'),
				        'messageHash' => $messageHash,
					'orderID' => 0,
					'subject' => $this->input->post('subject'),
					'message' => $messageText,
					'encrypted' => $isEncrypted,
					'time' => time(),
					'threadHash' => $threadHash,
					);

				$this->messages_model->addMessage($messageArray);
	

				$userId = $this->my_session->userdata('id');
				$data['messages'] = $this->messages_model->messages($userId);
				if($data['messages'] === NULL)
					$data['returnMessage'] .= 'You have no messages in your inbox';

				$data['returnMessage'] = 'Message has been sent.';
				$data['title'] = 'Message Sent';
        $data['sentSuccess'] = 1;
			        $data['page'] = 'messages/inbox'; 
			}
                }
                $this->load->library('Layout',$data);

	}


};

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Googlelogin extends CI_Controller {

public function __construct()
{
	parent::__construct();
	require_once APPPATH.'third_party/src/Google_Client.php';
	require_once APPPATH.'third_party/src/contrib/Google_Oauth2Service.php';
	$this->load->model('user');
}
	
	public function index()
	{
		$this->load->view('login_view');
	}
	
	public function login()
	{
	
		$clientId = '511203486710-5rhdi294l9uush08khnf1ltvo7oo5115.apps.googleusercontent.com'; //Google client ID
		$clientSecret = 'jd1VOWQ5HQ1gJ1s82xLy92-q'; //Google client secret
		$redirectURL = base_url() .'googlelogin/login';
		
		//https://curl.haxx.se/docs/caextract.html

		//Call Google API
		$gClient = new Google_Client();
		$gClient->setApplicationName('Login');
		$gClient->setClientId($clientId);
		$gClient->setClientSecret($clientSecret);
		$gClient->setRedirectUri($redirectURL);
		$google_oauthV2 = new Google_Oauth2Service($gClient);
		
		if(isset($_GET['code']))
		{
			$gClient->authenticate($_GET['code']);
			$_SESSION['token'] = $gClient->getAccessToken();
			header('Location: ' . filter_var($redirectURL, FILTER_SANITIZE_URL));
		}

		if (isset($_SESSION['token'])) 
		{
			$gClient->setAccessToken($_SESSION['token']);
		}
		
		if ($gClient->getAccessToken()) {
			$gpInfo = $google_oauthV2->userinfo->get();
			
			$userData['oauth_provider'] = 'google';
			$userData['oauth_uid']      = $gpInfo['id'];
			$userData['first_name']     = $gpInfo['given_name'];
			$userData['last_name']      = $gpInfo['family_name'];
			$userData['email']          = $gpInfo['email'];
			$userData['gender']         = !empty($gpInfo['gender'])?$gpInfo['gender']:'';
			$userData['locale']         = !empty($gpInfo['locale'])?$gpInfo['locale']:'';
			$userData['link']           = !empty($gpInfo['link'])?$gpInfo['link']:'';
			$userData['picture']        = !empty($gpInfo['picture'])?$gpInfo['picture']:'';
		
			$userID = $this->user->checkUser($userData);
		
			$this->session->set_userdata('loggedIn', true);
			$this->session->set_userdata('userData', $userData);

			redirect('user_authentication/profile/');
			// $this->load->view('user_authentication/profile',$userData);

			// echo "<pre>";
			// print_r($userData);
			// die;
			
        } 
		else 
		{
            $url = $gClient->createAuthUrl();
		    header("Location: $url");
            exit;
		}
		

		
	}	
}

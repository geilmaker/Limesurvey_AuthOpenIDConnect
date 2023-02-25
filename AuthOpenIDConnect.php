<?php
    require_once(__DIR__."/vendor/autoload.php");
    use Jumbojett\OpenIDConnectClient;

    class AuthOpenIDConnect extends LimeSurvey\PluginManager\AuthPluginBase {
        protected $storage = 'DbStorage';
        protected $settings = array(
            'info' => array(
                'type' => 'info',
                'content' => '<h1>OpenID Connect</h1><p>Please provide the following settings.</br>If necessary settings are missing, the default authdb login will be shown.</p>'
            ),
            'providerURL' => array(
                'type' => 'string',
                'label' => 'Provider URL',
                'help' => 'Required.',
                'default' => ''
            ),
            'clientID' => array(
                'type' => 'string',
                'label' => 'Client ID',
                'help' => 'Required.',
                'default' => ''
            ),
            'clientSecret' => array(
                'type' => 'string',
                'label' => 'Client Secret',
                'help' => 'Required.',
                'default' => ''
	    ),
            'redirectURL' => array(
                'type' => 'string',
                'label' => 'Redirect URL',
                'help' => 'The Redirect URL is automatically set on plugin activation.',
                'default' => '',
                'htmlOptions' => array(
                    'readOnly' => true,          
                )
            ),
	    'roleBasedLogin' => array(
	        'type' => 'boolean',
		'label' => 'Activate role based login',
		'help' => 'Only users with specific role can log in.',
	        'default' => false,
	    ),
	    'roleClaimName' => array(
	        'type' => 'string',
		'label' => 'Role Claim Name',
		'help' => 'Name of the claim containing the active role. Can\'t login without active role.',
	        'default' => 'roles',
	    ),
	    'roleName' => array(
	        'type' => 'string',
		'label' => 'Role Name',
		'help' => 'Name of the necessary role to log in.',
		'default' => 'active',
	    ),
        );
        static protected $description = 'OpenID Connect Authenticaton Plugin for LimeSurvey.';
        static protected $name = 'AuthOpenIDConnect';

        public function init(){
            $this->subscribe('beforeActivate');
	    $this->subscribe('beforeLogin');
	    $this->subscribe('newUserSession');
            $this->subscribe('afterLogout');
	}

        public function beforeActivate(){
            $baseURL = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}";
            $basePath = preg_split("/\/pluginmanager/", $_SERVER['REQUEST_URI']);
            $this->set('redirectURL', $baseURL . $basePath[0] . "/authentication/sa/login");
	}

        public function beforeLogin(){
            $providerURL = $this->get('providerURL', null, null, false);
            $clientID = $this->get('clientID', null, null, false);
            $clientSecret = $this->get('clientSecret', null, null, false);
            $redirectURL = $this->get('redirectURL', null, null, false);
	    $roleBasedLogin = $this->get('roleBasedLogin', null, null, false);
	    	    
	    if(!$providerURL || !$clientSecret || !$clientID || !$redirectURL){
                // Display authdb login if necessary plugin settings are missing.
                return;
            }
            
            $oidc = new OpenIDConnectClient($providerURL, $clientID, $clientSecret);
            $oidc->setRedirectURL($redirectURL);

            if(isset($_REQUEST['error'])){
                return;
            }

            try {
		if($oidc->authenticate()){
		    $user_data = $oidc->requestUserInfo();
		    $username = $user_data->preferred_username;
		    $email = $user_data->email;
		    $givenName = $user_data->given_name;
		    $familyName = $user_data->family_name;
		    if ($roleBasedLogin == true) {
		        $claimName = $this->get('roleClaimName', null, null, false);
		    	$roleName = $this->get('roleName', null, null, false);
		    	$roles = $user_data->$claimName;
		        if (!is_array($roles) || !in_array($roleName, $roles)) {
		    	    // No active group  
		    	    return;
		        }
		    }
                    $user = $this->api->getUserByName($username);
    		    
                    if(empty($user)){
                        $user = new User;
                        $user->users_name = $username;
                        $user->setPassword(createPassword());
                        $user->full_name = $givenName.' '.$familyName;
                        $user->parent_id = 1;
                        $user->lang = $this->api->getConfigKey('defaultlang', 'en');
                        $user->email = $email;
        
                        if(!$user->save()){
                            // Couldn't create user, navigate to authdb login.
                            return;
                        }
                        // User successfully created.
                    }
                    $this->setUsername($user->users_name);
                    $this->setAuthPlugin();
                    return;
                }
            } catch (\Throwable $error) {
                // Error occurred during authentication process, redirect to authdb login.
                return;
            }
            
        }
        
        public function newUserSession(){
            $identity = $this->getEvent()->get('identity');
            if ($identity->plugin != 'AuthOpenIDConnect') {
                return;
            }

            $user = $this->api->getUserByName($this->getUsername());

            // Shouldn't happen, but just to be sure.
            if(empty($user)){
                $this->setAuthFailure(self::ERROR_UNKNOWN_IDENTITY, gT('User not found.'));
            } else {
                $this->setAuthSuccess($user);
            }
        }

        public function afterLogout(){
            Yii::app()->getRequest()->redirect('/', true, 302);
        }
    }
?>

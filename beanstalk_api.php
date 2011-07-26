<?php
class beanstalk_api {
	/**
	 * Beanstalk account configuration
	 *
	 * Please enter your account name, username and password below.
	 */

	private $account_name		= 'example';		// Beanstalk account name (first segment of your beanstalk URL - http://example.beanstalkapp.com)
	private $username		= 'username';		// Beanstalk username
	private $password		= 'password';		// Beanstalk password


	//
	// Account
	//

	/**
	 * Returns Beanstalk account details.
	 *
	 * @link http://api.beanstalkapp.com/account.html
	 * @return xml
	 */
	public function get_account_details() {
		return $this->_execute_curl("account.xml");
	}

	/**
	 * Allows a user to update their account details by sending specific parameters
	 *
	 * @link http://api.beanstalkapp.com/account.html
	 * @param string $name			required
	 * @param string $time_zone		required
	 * @return xml
	 */
	public function update_account_details($name, $time_zone) {
		if(empty($name) || empty($time_zone))
			return "Name and time zone required";
	}


	//
	// Plans
	//

	/**
	 * Returns Beanstalk account plans
	 *
	 * @link http://api.beanstalkapp.com/plan.html
	 * @return xml
	 */
	public function find_all_plans() {
		return $this->_execute_curl("plans.xml");
	}


	//
	// Users
	//

	/**
	 * Returns Beanstalk account user list.
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @return xml
	 */
	public function find_all_users() {
		return $this->_execute_curl("users.xml");
	}

	/**
	 * Returns a Beanstalk account user based on a specific user ID
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @param integer $user_id		required
	 * @return xml
	 */
	public function find_single_user($user_id) {
		if(empty($user_id))
			return "User ID required";
		else
			return $this->_execute_curl("users", $user_id . ".xml");
	}

	/**
	 * Returns Beanstalk user currently being used to access the API
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @return xml
	 */
	public function find_current_user() {
		return $this->_execute_curl("users", "current.xml");
	}

	/**
	 * Create a new Beanstalk user
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @param string $login
	 * @param string $email
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $password
	 * @param int $admin [optional]
	 * @param string $timezone [optional]
	 * @return xml
	 */
	public function create_user($login, $email, $first_name, $last_name, $password, $admin = 0, $timezone = NULL) {
		if(empty($login) || empty($email) || empty($first_name) || empty($last_name) || empty($password))
			return "Some required fields missing";
		
		$xml = new SimpleXMLElement('<user></user>');
		
		$xml->addChild('login', $login);
		$xml->addChild('email', $email);
		$xml->addChild('first_name', $first_name);
		$xml->addChild('last_name', $last_name);
		$xml->addChild('password', $password);
		$xml->addChild('admin', $admin); // Should change to optional?
		
		if(!is_null($timezone))
			$xml->addChild('timezone', $timezone);
		
		return $this->_execute_curl("users.xml", NULL, "POST", $xml->asXml());
	}

	/**
	 * Update an existing user
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @param integer $user_id
	 * @param array $params Accepts - email, first_name, last_name, password, admin, timezone
	 * @return xml
	 */
	public function update_user($user_id, $params = array()) {
		if(empty($user_id))
			return "User ID required";
		
		if(count($params) == 0)
			return "Nothing to update";
		
		$xml = new SimpleXMLElement('<user></user>');
		
		if(isset($params['email']))
			$xml->addChild('email', $params['email']);
		
		if(isset($params['first_name']))
			$xml->addChild('first_name', $params['first_name']);
		
		if(isset($params['last_name']))
			$xml->addChild('last_name', $params['last_name']);
		
		if(isset($params['password']))
			$xml->addChild('password', $params['password']);
		
		if(isset($params['admin']))
			$xml->addChild('admin', $params['admin']);
		
		if(isset($params['timezone']))
			$xml->addChild('timezone', $params['timezone']);
		
		return $this->_execute_curl("users", $user_id . ".xml", "PUT", $xml->asXml());
	}

	/**
	 * Delete a user
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @param integer $user_id
	 * @return xml
	 */
	public function delete_user($user_id) {
		if(empty($user_id))
			return "User ID required";
		
		return $this->_execute_curl("users", $user_id . ".xml", "DELETE");
	}


	//
	// Repositories
	//

	/**
	 * Returns Beanstalk account repository list
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @return xml
	 */
	public function find_all_repositories() {
		return $this->_execute_curl("repositories.xml");
	}

	/**
	 * Returns a Beanstalk account repository based on a specific repository ID
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @param integer $repo_id		required
	 * @return xml
	 */
	public function find_single_repository($repo_id) {
		if(empty($repo_id))
			return "Repository ID required";
		else
			return $this->_execute_curl("repositories", $repo_id . ".xml");
	}

	/**
	 * Create a repository
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @param string $name
	 * @param string $type_id [optional] Can be git or subversion
	 * @param string $title
	 * @param bool $create_structure [optional]
	 * @param string $color_label [optional] Accepts - red, orange, yellow, green, blue, pink, grey
	 * @return xml
	 */
	public function create_repository($name, $type_id = "subversion", $title, $create_structure = true, $color_label = "grey") {
		if(empty($name) || empty($title))
			return "Repository name and title required";
		
		$xml = new SimpleXMLElement('<repository></repository>');
		
		$xml->addChild('name', $name);
		
		if(!is_null($type_id))
			$xml->addChild('type_id', $type_id);
		
		$xml->addChild('title', $title);
		
		if(!is_null($create_structure))
			$xml->addChild('create_structure', $create_structure);
		
		if(!is_null($color_label))
			$xml->addChild('color_label', "label-" . $color_label);
		
		return $this->_execute_curl("repositories.xml", NULL, "POST", $xml->asXml());
	}

	/**
	 * Update an existing repository
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @param integer $repo_id
	 * @param array $params Accepts - name, title, color_label (red, orange, yellow, green, blue, pink, grey)
	 * @return xml
	 */
	public function update_repository($repo_id, $params = array()) {
		if(empty($repo_id))
			return "Repository ID required";
		
		if(count($params) == 0)
			return "Nothing to update";
		
		$xml = new SimpleXMLElement('<repository></repository>');
		
		if(isset($params['name']))
			$xml->addChild('name', $params['name']);
		
		if(isset($params['title']))
			$xml->addChild('title', $params['title']);
		
		if(isset($params['color-label']))
			$xml->addChild('color-label', "label-" . $params['color-label']);
		
		return $this->_execute_curl("repositories", $repo_id . ".xml", "PUT", $xml->asXml());
	}


	//
	// Changesets
	//

	/**
	 * Returns Beanstalk account changeset list
	 *
	 * @link http://api.beanstalkapp.com/changeset.html
	 * @return xml
	 */
	public function find_all_changesets() {
		return $this->_execute_curl("changesets.xml");
	}

	/**
	 * Returns a Beanstalk repository changeset based on a specific repository ID
	 *
	 * @link http://api.beanstalkapp.com/changeset.html
	 * @param integer $repo_id		required
	 * @return xml
	 */
	public function find_single_repository_changeset($repo_id) {
		if(empty($repo_id))
			return "Repository ID required";
		else
			return $this->_execute_curl("changesets", "repository.xml?repository_id=" . $repo_id);
	}

	/**
	 * Returns a Beanstalk repository's specific changeset based on a specific repository ID and changeset ID
	 *
	 * @link http://api.beanstalkapp.com/changeset.html
	 * @param integer $repo_id		required
	 * @param integer $revision		required
	 * @return xml
	 */
	public function find_single_changeset($repo_id, $revision) {
		if(empty($repo_id) || empty($revision))
			return "Changeset ID and repository ID required";
		else
			return $this->_execute_curl("changesets", $revision . ".xml?repository_id=" . $repo_id);
	}


	//
	// Comments
	//

   /**
	* Returns a Beanstalk repository's comment listing
	*
	* @link http://api.beanstalkapp.com/comment.html
	* @param integer $repo_id		required
	* @return xml
	*/
	public function find_all_comments($repo_id) {
		if(empty($repo_id))
			return "Repository ID required";
		else
			return $this->_execute_curl($repo_id, "comments.xml");
	}
		
	/**
	 * Returns a Beanstalk repository's comment listing for a specific changeset
	 *
	 * @link http://api.beanstalkapp.com/comment.html
	 * @param integer $repo_id		required
	 * @param integer $revision		required
	 * @return xml
	 */
	public function find_all_changeset_comments($repo_id, $revision) {
		if(empty($repo_id) || empty($revision))
			return "Repository ID and revision ID required";
		else
			return $this->_execute_curl($repo_id, "comments.xml?revision=" . $revision);
	}
		
	/**
	 * Returns a Beanstalk repository's comment based on a specific comment ID
	 *
	 * @link http://api.beanstalkapp.com/comment.html
	 * @param integer $repo_id		required
	 * @param integer $revision		required
	 * @return xml
	 */
	public function find_single_comment($repo_id, $comment_id) {
		if(empty($repo_id) || empty($comment_id))
			return "Repository ID and comment ID required";
		else
			return $this->_execute_curl($repo_id, "comments/" . $comment_id . ".xml");
	}

	/**
	 * Create new comment - unclear from docs which parameters are required
	 *
	 * @link http://api.beanstalkapp.com/comment.html
	 * @param integer $repo_id
	 * @param integer $revision_id
	 * @param string $body
	 * @param string $file_path
	 * @param integer $line_number
	 * @return xml
	 */
	public function create_comment($repo_id, $revision_id, $body, $file_path, $line_number) {
		if(empty($repo_id) || empty($revision_id) || empty($body) || empty($file_path) || empty($line_number))
			return "Some required fields missing";
		
		$xml = new SimpleXMLElement('<comment></comment>');
		
		$revision_xml = $xml->addChild('revision', $revision_id);
		$revision_xml->addAttribute('type', 'integer');
		
		$xml->addChild('body', $body);
		$xml->addChild('file_path', $file_path);
		$xml->addChild('line_number', $line_number); // Should this have type attribute set as well?
		
		return $this->_execute_curl($repo_id, "comments.xml", "POST", $xml->asXml());
	}


	//
	// Server Environments
	//

	/**
	 * Returns a Beanstalk repository's server environment listing
	 *
	 * @link http://api.beanstalkapp.com/server_environment.html
	 * @param integer $repo_id		required
	 * @return xml
	 */
	public function find_all_server_environments($repo_id) {
		if(empty($repo_id))
			return "Repository ID required";
		else
			return $this->_execute_curl($repo_id, "server_environments.xml");
	}

	/**
	 * Returns a Beanstalk repository's server environment listing based on a specific environment ID
	 *
	 * @link http://api.beanstalkapp.com/server_environment.html
	 * @param integer $repo_id		required
	 * @param integer $environment_id	required
	 * @return xml
	 */
	public function find_single_server_environment($repo_id, $environment_id) {
		if(empty($repo_id) || empty($environment_id))
			return "Repository ID required";
		else
			return $this->_execute_curl($repo_id, "server_environments/" . $environment_id . ".xml");
	}


	//
	// Release Servers
	//

	/**
	 * Returns a Beanstalk repository's release server listing
	 *
	 * @link http://api.beanstalkapp.com/release_server.html
	 * @param integer $repo_id		required
	 * @param integer $environment_id	required
	 * @return xml
	 */
	function find_all_release_servers($repo_id, $environment_id) {
		if(empty($repo_id) || empty($environment_id))
			return "Repository ID and environment ID required";
		else
			return $this->_execute_curl($repo_id, "release_servers.xml?environment_id=" . $environment_id);
	}

	/**
	 * Returns a Beanstalk repository's release server listing based on a specific server ID
	 *
	 * @link http://api.beanstalkapp.com/release_server.html
	 * @param integer $repo_id		required
	 * @param integer $server_id		required
	 * @return xml
	 */
	public function find_single_release_server($repo_id, $server_id) {
		if(empty($repo_id) || empty($server_id))
			return "Repository ID and server ID required";
		else
			return $this->_execute_curl($repo_id, "release_servers/" . $server_id . ".xml");
	}


	//
	// Releases
	//

	/**
	 * Returns a Beanstalk repository's successful releases listing
	 *
	 * @link http://api.beanstalkapp.com/release.html
	 * @param integer $repo_id		required
	 * @return xml
	 */
	public function find_all_sucessful_releases($repo_id) {
		if(empty($repo_id))
			return "Repository ID required";
		else
			return $this->_execute_curl($repo_id, "releases.xml");
	}

	/**
	 * Returns a Beanstalk repository's release based on a specific release id
	 *
	 * @link http://api.beanstalkapp.com/release.html
	 * @param integer $repo_id		required
	 * @param integer $release_id		required
	 * @return xml
	 */
	public function find_single_release($repo_id, $release_id) {
		if(empty($repo_id) || empty($release_id))
			return "Repository ID and release ID required";
		else
			return $this->_execute_curl($repo_id, $release_id . ".xml");
	}


	//
	// Utility functions
	//

	/**
	 * Sets up and executes the cURL requests and returns the response
	 *
	 * @param string $api_name
	 * @param string $api_params [optional]
	 * @param string $curl_verb [optional]
	 * @param string $write_data [optional]
	 * @return mixed
	 */
	private function _execute_curl($api_name, $api_params = NULL, $curl_verb = "GET", $write_data = NULL) {
		if( ! isset($api_params))
			$ch = curl_init("https://" . $this->account_name . ".beanstalkapp.com/api/" . $api_name);
		else
			$ch = curl_init("https://" . $this->account_name . ".beanstalkapp.com/api/" . $api_name . "/" . $api_params);
			
		$headers = array('Content-type: application/xml');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		if(!is_null($write_data))
			curl_setopt($ch, CURLOPT_POSTFIELDS, $write_data);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $curl_verb);

		$data = curl_exec($ch);
		
		if(curl_errno($ch))
			throw new Exception("cURL request failed - " . curl_errno($ch) . " : " . curl_error($ch));
		
		//TODO Check response code using curl_getinfo()
		
		curl_close($ch);

		return $data;
	}
}
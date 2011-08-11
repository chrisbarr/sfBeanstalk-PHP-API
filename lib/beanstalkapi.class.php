<?php

/**
 * PHP class for connecting to the Beanstalk API
 *
 * @link http://api.beanstalkapp.com/
 * @version 0.5
 */
class BeanstalkAPI {
	/**
	 * Beanstalk account configuration
	 *
	 * Either enter your account name, username and password below,
	 * or pass details when creating object ie. new BeanstalkAPI('account', 'user', 'pass');
	 */

	private $account_name	= '';		// Beanstalk account name (first segment of your beanstalk URL - http://example.beanstalkapp.com)
	private $username		= '';		// Beanstalk username
	private $password		= '';		// Beanstalk password
	
	public $error_code = '';
	public $error_string = '';

	// ------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param string $account_name [optional]
	 * @param string $username [optional]
	 * @param string $password [optional]
	 * @return void
	 */
	public function __construct($account_name = null, $username = null, $password = null) {
		if(!is_null($account_name))
			$this->account_name = $account_name;
		
		if(!is_null($username))
			$this->username = $username;
		
		if(!is_null($password))
			$this->password = $password;
		
		if(empty($this->account_name) || empty($this->username) || empty($this->password))
			throw new InvalidArgumentException("Account name, username and password required");
	}


	//
	// Account
	//

	/**
	 * Returns Beanstalk account details.
	 *
	 * @link http://api.beanstalkapp.com/account.html
	 * @return SimpleXMLElement
	 */
	public function get_account_details() {
		return $this->_execute_curl("account.xml");
	}

	/**
	 * Allows a user to update their account details by sending specific parameters
	 *
	 * @link http://api.beanstalkapp.com/account.html
	 * @param array $params Accepts - name, timezone
	 * @return SimpleXMLElement
	 */
	public function update_account_details($params = array()) {
		if(count($params) == 0)
			throw new InvalidArgumentException("Nothing to update");
		
		$xml = new SimpleXMLElement("<account></account>");
		
		if(isset($params['name']))
			$xml->addChild('name', $params['name']);
		
		if(isset($params['timezone']))
			$xml->addChild('time-zone', $params['timezone']); // Inconsistency in API?
		
		return $this->_execute_curl("account.xml", NULL, "PUT", $xml->asXml());
	}


	//
	// Plans
	//

	/**
	 * Returns Beanstalk account plans
	 *
	 * @link http://api.beanstalkapp.com/plan.html
	 * @return SimpleXMLElement
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
	 * @return SimpleXMLElement
	 */
	public function find_all_users() {
		return $this->_execute_curl("users.xml");
	}

	/**
	 * Returns a Beanstalk account user based on a specific user ID
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @param integer $user_id		required
	 * @return SimpleXMLElement
	 */
	public function find_single_user($user_id) {
		if(empty($user_id))
			throw new InvalidArgumentException("User ID required");
		else
			return $this->_execute_curl("users", $user_id . ".xml");
	}

	/**
	 * Returns Beanstalk user currently being used to access the API
	 *
	 * @link http://api.beanstalkapp.com/user.html
	 * @return SimpleXMLElement
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
	 * @return SimpleXMLElement
	 */
	public function create_user($login, $email, $first_name, $last_name, $password, $admin = 0, $timezone = NULL) {
		if(empty($login) || empty($email) || empty($first_name) || empty($last_name) || empty($password))
			throw new InvalidArgumentException("Some required fields missing");

		$xml = new SimpleXMLElement('<user></user>');

		$xml->addChild('login', $login);
		$xml->addChild('email', $email);
		$xml->addChild('first-name', $first_name);
		$xml->addChild('last-name', $last_name);
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
	 * @return SimpleXMLElement
	 */
	public function update_user($user_id, $params = array()) {
		if(empty($user_id))
			throw new InvalidArgumentException("User ID required");

		if(count($params) == 0)
			throw new InvalidArgumentException("Nothing to update");

		$xml = new SimpleXMLElement('<user></user>');

		if(isset($params['email']))
			$xml->addChild('email', $params['email']);

		if(isset($params['first_name']))
			$xml->addChild('first-name', $params['first_name']);

		if(isset($params['last_name']))
			$xml->addChild('last-name', $params['last_name']);

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
	 * @return SimpleXMLElement
	 */
	public function delete_user($user_id) {
		if(empty($user_id))
			throw new InvalidArgumentException("User ID required");

		return $this->_execute_curl("users", $user_id . ".xml", "DELETE");
	}


	//
	// Public Keys
	//

	/**
	 * Return all public keys for current user - or for a specified user (if using admin account)
	 *
	 * @link http://api.beanstalkapp.com/public_key.html
	 * @param integer $user_id [optional]
	 * @return SimpleXMLElement
	 */
	public function find_all_public_keys($user_id = NULL) {
		if(!is_null($user_id))
			return $this->_execute_curl("public_keys.xml?user_id=" . $user_id);
		else
			return $this->_execute_curl("public_keys.xml");
	}

	/**
	 * Return a single public key
	 *
	 * @link http://api.beanstalkapp.com/public_key.html
	 * @param integer $key_id
	 * @return SimpleXMLElement
	 */
	public function find_single_public_key($key_id) {
		if(empty($key_id))
			throw new InvalidArgumentException("Public key ID required");
		
		return $this->_execute_curl("public_keys", $key_id . ".xml");
	}

	/**
	 * Create a new public key - creates for current user unless specified (must be admin)
	 *
	 * @link http://api.beanstalkapp.com/public_key.html
	 * @param string $content
	 * @param string $name [optional]
	 * @param integer $user_id [optional] Defaults to current user
	 * @return SimpleXMLElement
	 */
	public function create_public_key($content, $name = NULL, $user_id = NULL) {
		if(empty($content))
			throw new InvalidArgumentException("Key content required");
		
		$xml = new SimpleXMLElement('<public-key></public-key>');
		
		$xml->addChild('content', $content);
		
		if(!is_null($name))
			$xml->addChild('name', $name);
		
		if(!is_null($user_id))
			$xml->addChild('user-id', $user_id);
		
		return $this->_execute_curl("public_keys.xml", NULL, "POST", $xml->asXml());
	}

	/**
	 * Update a public key - can only update own keys unless admin
	 *
	 * @link http://api.beanstalkapp.com/public_key.html
	 * @param integer $key_id
	 * @param array $params Accepts - content, name
	 * @return SimpleXMLElement
	 */
	public function update_public_key($key_id, $params = array()) {
		if(empty($key_id))
			throw new InvalidArgumentException("Public key ID required");
		
		if(count($params) == 0)
			throw new InvalidArgumentException("Nothing to update");
		
		$xml = new SimpleXMLElement('<public-key></public-key>');
		
		if(!is_null($params['content']))
			$xml->addChild('content', $params['content']);

		if(!is_null($params['name']))
			$xml->addChild('name', $params['name']);
		
		return $this->_execute_curl("public_keys", $key_id . ".xml", "PUT", $xml->asXml());
	}

	/**
	 * Delete a public key - can only delete own keys unless admin
	 *
	 * @link http://api.beanstalkapp.com/public_key.html
	 * @param integer $key_id
	 * @return SimpleXMLElement
	 */
	public function delete_public_key($key_id) {
		if(empty($key_id))
			throw new InvalidArgumentException("Public key ID required");
		
		return $this->_execute_curl("public_keys", $key_id . ".xml", "DELETE");
	}


	//
	// Repositories
	//

	/**
	 * Returns Beanstalk account repository list
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @return SimpleXMLElement
	 */
	public function find_all_repositories() {
		return $this->_execute_curl("repositories.xml");
	}

	/**
	 * Returns a Beanstalk account repository based on a specific repository ID
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @param integer $repo_id		required
	 * @return SimpleXMLElement
	 */
	public function find_single_repository($repo_id) {
		if(empty($repo_id))
			throw new InvalidArgumentException("Repository ID required");
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
	 * @return SimpleXMLElement
	 */
	public function create_repository($name, $type_id = "subversion", $title, $create_structure = true, $color_label = "grey") {
		if(empty($name) || empty($title))
			throw new InvalidArgumentException("Repository name and title required");

		$xml = new SimpleXMLElement('<repository></repository>');

		$xml->addChild('name', $name);

		if(!is_null($type_id))
			$xml->addChild('type-id', $type_id);

		$xml->addChild('title', $title);

		if(!is_null($create_structure))
			$xml->addChild('create_structure', $create_structure);

		if(!is_null($color_label))
			$xml->addChild('color-label', "label-" . $color_label);

		return $this->_execute_curl("repositories.xml", NULL, "POST", $xml->asXml());
	}

	/**
	 * Update an existing repository
	 *
	 * @link http://api.beanstalkapp.com/repository.html
	 * @param integer $repo_id
	 * @param array $params Accepts - name, title, color_label (red, orange, yellow, green, blue, pink, grey)
	 * @return SimpleXMLElement
	 */
	public function update_repository($repo_id, $params = array()) {
		if(empty($repo_id))
			throw new InvalidArgumentException("Repository ID required");

		if(count($params) == 0)
			throw new InvalidArgumentException("Nothing to update");

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
	// User Permissions
	//

	/**
	 * Find permissions for a user
	 *
	 * @link http://api.beanstalkapp.com/permissions.html
	 * @param integer $user_id
	 * @return SimpleXMLElement
	 */
	public function find_user_permissions($user_id) {
		if(empty($user_id))
			throw new InvalidArgumentException("User ID required");
		
		return $this->_execute_curl("permissions", $user_id . ".xml");
	}

	/**
	 * Create permissions for a user for a repository - overwrites existing
	 *
	 * @link http://api.beanstalkapp.com/permissions.html
	 * @param integer $user_id
	 * @param integer $repo_id
	 * @param bool $read [optional]
	 * @param bool $write [optional]
	 * @param bool $full_deployments_access [optional] Gives full deployment access to a repository
	 * @param integer $server_environment_id [optional] Give deployment access only to a specific server environment
	 * @return SimpleXMLElement
	 */
	public function create_user_permissions($user_id, $repo_id, $read = false, $write = false, $full_deployments_access = false, $server_environment_id = NULL) {
		if(empty($user_id) || empty($repo_id))
			throw new InvalidArgumentException("Some required fields missing");
		
		$xml = new SimpleXMLElement('<permission></permission>');
		
		$user_xml = $xml->addChild('user-id', $user_id);
		$user_xml->addAttribute('type', 'integer');
		
		$repo_xml = $xml->addChild('repository-id', $repo_id);
		$repo_xml->addAttribute('type', 'integer');
		
		if($read === true)
			$read_xml = $xml->addChild('read', 'true');
		else
			$read_xml = $xml->addChild('read', 'false');
		
		$read_xml->addAttribute('type', 'boolean');
		
		if($write === true)
			$write_xml = $xml->addChild('write', 'true');
		else
			$write_xml = $xml->addChild('write', 'false');
		
		$write_xml->addAttribute('type', 'boolean');
		
		if($full_deployments_access === true)
			$full_deploy_xml = $xml->addChild('full-deployments-access', 'true');
		else
			$full_deploy_xml = $xml->addChild('full-deployments-access', 'false');
		
		$full_deploy_xml->addAttribute('type', 'boolean');
		
		if(!is_null($server_environment_id)) {
			$environment_xml = $xml->addChild('server-environment-id', $server_environment_id);
			$environment_xml->addAttribute('type', 'integer');
		}
		
		return $this->_execute_curl("permissions.xml", NULL, "POST", $xml->asXml());
	}

	/**
	 * Strip a user of a set of permissions for a repository
	 *
	 * @link http://api.beanstalkapp.com/permissions.html
	 * @param integer $permission_id
	 * @return SimpleXMLElement
	 */
	public function delete_user_permissions($permission_id) {
		if(empty($permission_id))
			throw new InvalidArgumentException("Permission ID required");
		
		return $this->_execute_curl("permissions", $permission_id . ".xml", "DELETE");
	}


	//
	// Changesets
	//

	/**
	 * Returns Beanstalk account changeset list
	 *
	 * @link http://api.beanstalkapp.com/changeset.html
	 * @param integer $page [optional] 15 results per page
	 * @return SimpleXMLElement
	 */
	public function find_all_changesets($page = 1) {
		return $this->_execute_curl("changesets.xml?page=" . $page);
	}

	/**
	 * Returns a Beanstalk repository changesets based on a specific repository ID
	 *
	 * @link http://api.beanstalkapp.com/changeset.html
	 * @param integer $repo_id		required
	 * @param integer $page [optional] UNDOCUMENTED
	 * @return SimpleXMLElement
	 */
	public function find_single_repository_changesets($repo_id, $page = 1) {
		if(empty($repo_id))
			throw new InvalidArgumentException("Repository ID required");
		else
			return $this->_execute_curl("changesets", "repository.xml?repository_id=" . $repo_id . "&page=" . $page);
	}

	/**
	 * Returns a Beanstalk repository's specific changeset based on a specific repository ID and changeset ID
	 *
	 * @link http://api.beanstalkapp.com/changeset.html
	 * @param integer $repo_id		required
	 * @param integer $revision		required
	 * @return SimpleXMLElement
	 */
	public function find_single_changeset($repo_id, $revision) {
		if(empty($repo_id) || empty($revision))
			throw new InvalidArgumentException("Changeset ID and repository ID required");
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
	* @param integer $page [optional] 15 results per page
	* @return SimpleXMLElement
	*/
	public function find_all_comments($repo_id, $page = 1) {
		if(empty($repo_id))
			throw new InvalidArgumentException("Repository ID required");
		else
			return $this->_execute_curl($repo_id, "comments.xml?page=" . $page);
	}

	/**
	 * Returns a Beanstalk repository's comment listing for a specific changeset
	 *
	 * @link http://api.beanstalkapp.com/comment.html
	 * @param integer $repo_id		required
	 * @param integer $revision		required
	 * @return SimpleXMLElement
	 */
	public function find_all_changeset_comments($repo_id, $revision) {
		if(empty($repo_id) || empty($revision))
			throw new InvalidArgumentException("Repository ID and revision ID required");
		else
			return $this->_execute_curl($repo_id, "comments.xml?revision=" . $revision);
	}

	/**
	 * Return comments from a specific user
	 *
	 * @link http://api.beanstalkapp.com/comment.html
	 * @param integer $user_id
	 * @param integer $page [optional] 15 results per page
	 * @return SimpleXMLElement
	 */
	public function find_single_user_comments($user_id, $page = 1) {
		if(empty($user_id))
			throw new InvalidArgumentException("User ID required");
		
		return $this->_execute_curl("comments", "user.xml?user_id=" . $user_id . "&page=" . $page);
	}

	/**
	 * Returns a Beanstalk repository's comment based on a specific comment ID
	 *
	 * @link http://api.beanstalkapp.com/comment.html
	 * @param integer $repo_id		required
	 * @param integer $revision		required
	 * @return SimpleXMLElement
	 */
	public function find_single_comment($repo_id, $comment_id) {
		if(empty($repo_id) || empty($comment_id))
			throw new InvalidArgumentException("Repository ID and comment ID required");
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
	 * @return SimpleXMLElement
	 */
	public function create_comment($repo_id, $revision_id, $body, $file_path, $line_number) {
		if(empty($repo_id) || empty($revision_id) || empty($body) || empty($file_path) || empty($line_number))
			throw new InvalidArgumentException("Some required fields missing");

		$xml = new SimpleXMLElement('<comment></comment>');

		$revision_xml = $xml->addChild('revision', $revision_id);
		$revision_xml->addAttribute('type', 'integer');

		$xml->addChild('body', $body);
		$xml->addChild('file-path', $file_path);
		$xml->addChild('line-number', $line_number); // Should this have type attribute set as well?

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
	 * @return SimpleXMLElement
	 */
	public function find_all_server_environments($repo_id) {
		if(empty($repo_id))
			throw new InvalidArgumentException("Repository ID required");
		else
			return $this->_execute_curl($repo_id, "server_environments.xml");
	}

	/**
	 * Returns a Beanstalk repository's server environment listing based on a specific environment ID
	 *
	 * @link http://api.beanstalkapp.com/server_environment.html
	 * @param integer $repo_id		required
	 * @param integer $environment_id	required
	 * @return SimpleXMLElement
	 */
	public function find_single_server_environment($repo_id, $environment_id) {
		if(empty($repo_id) || empty($environment_id))
			throw new InvalidArgumentException("Repository ID required");
		else
			return $this->_execute_curl($repo_id, "server_environments/" . $environment_id . ".xml");
	}

	/**
	 * Create a new server environment
	 *
	 * @link http://api.beanstalkapp.com/server_environment.html
	 * @param integer $repo_id
	 * @param string $name
	 * @param bool $automatic [optional]
	 * @param string $branch_name [optional] Git only
	 * @return SimpleXMLElement
	 */
	public function create_server_environment($repo_id, $name, $automatic = false, $branch_name = NULL) {
		if(empty($repo_id) || empty($name) || ($automatic !== false && $automatic !== true))
			throw new InvalidArgumentException("Repository ID, name and deploy automatically required");
		
		$xml = new SimpleXMLElement('<server-environment></server-environment>');
		
		$xml->addChild('name', $name);
		$xml->addChild('automatic', $automatic);
		
		if(!is_null($branch_name))
			$xml->addChild('branch-name', $branch_name);
		
		return $this->_execute_curl($repo_id, "server_environments.xml", "POST", $xml->asXml());
	}

	/**
	 * Update a server environment
	 *
	 * @link http://api.beanstalkapp.com/server_environment.html
	 * @param integer $repo_id
	 * @param integer $environment_id
	 * @param array $params Accepts - name, automatic, branch_name
	 * @return SimpleXMLElement
	 */
	public function update_server_environment($repo_id, $environment_id, $params = array()) {
		if(empty($repo_id) || empty($environment_id))
			throw new InvalidArgumentException("Repository ID and server environment ID requried");
		
		if(count($params) == 0)
			throw new InvalidArgumentException("Nothing to update");
		
		$xml = new SimpleXMLElement('<server-environment></server-environment>');
		
		if(isset($params['name']))
			$xml->addChild('name', $params['name']);
		
		if(isset($params['automatic']))
			$xml->addChild('automatic', $params['automatic']);
		
		if(isset($params['branch_name']))
			$xml->addChild('branch-name', $params['branch_name']);
		
		return $this->_execute_curl($repo_id, "server_environments/" . $environment_id . ".xml", "PUT", $xml->asXml());
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
	 * @return SimpleXMLElement
	 */
	function find_all_release_servers($repo_id, $environment_id) {
		if(empty($repo_id) || empty($environment_id))
			throw new InvalidArgumentException("Repository ID and environment ID required");
		else
			return $this->_execute_curl($repo_id, "release_servers.xml?environment_id=" . $environment_id);
	}

	/**
	 * Returns a Beanstalk repository's release server listing based on a specific server ID
	 *
	 * @link http://api.beanstalkapp.com/release_server.html
	 * @param integer $repo_id		required
	 * @param integer $server_id		required
	 * @return SimpleXMLElement
	 */
	public function find_single_release_server($repo_id, $server_id) {
		if(empty($repo_id) || empty($server_id))
			throw new InvalidArgumentException("Repository ID and server ID required");
		else
			return $this->_execute_curl($repo_id, "release_servers/" . $server_id . ".xml");
	}

	/**
	 * Create a release server
	 *
	 * @link http://api.beanstalkapp.com/release_server.html
	 * @param integer $repo_id
	 * @param integer $environment_id
	 * @param string $name
	 * @param string $local_path
	 * @param string $remote_path
	 * @param string $remote_addr
	 * @param string $protocol [optional] Accepts - ftp, sftp
	 * @param integer $port [optional]
	 * @param string $login
	 * @param string $password
	 * @param bool $use_active_mode [optional]
	 * @param bool $authenticate_by_key [optional]
	 * @param bool $use_feat [optional] Defaults to true
	 * @param string $pre_release_hook [optional]
	 * @param string $post_release_hook [optional]
	 * @return SimpleXMLElement
	 */
	public function create_release_server($repo_id, $environment_id, $name, $local_path, $remote_path, $remote_addr, $protocol = 'ftp', $port = 21, $login, $password, $use_active_mode = NULL, $authenticate_by_key = NULL, $use_feat = true, $pre_release_hook = NULL, $post_release_hook = NULL) {
		if(empty($repo_id) || empty($environment_id) || empty($name) || empty($local_path) || empty($remote_path) || empty($remote_addr) || empty($protocol) || empty($port) || empty($login))
			throw new InvalidArgumentException("Some required fields missing");
		
		$xml = new SimpleXMLElement('<release-server></release-server>');
		
		$xml->addChild('name', $name);
		$xml->addChild('local-path', $local_path);
		$xml->addChild('remote-path', $remote_path);
		$xml->addChild('remote-addr', $remote_addr);
		
		$xml->addChild('login', $login);
		
		if($protocol == 'sftp') {
			$xml->addChild('protocol', 'sftp');
			
			if($authenticate_by_key == true) {
				$xml->addChild('authenticate_by_key', true);
			}
			else {
				$xml->addChild('password', $password);
			}
		}
		else {
			$xml->addChild('protocol', 'ftp');
			$xml->addChild('password', $password);
		}
		
		$xml->addChild('port', $port);
		
		if(!is_null($use_active_mode))
			$xml->addChild('use-active-mode', $use_active_mode);
		
		if(!is_null($use_feat))
			$xml->addChild('use-feat', $use_feat); // True by default
		
		if(!is_null($pre_release_hook))
			$xml->addChild('pre-release-hook', $pre_release_hook);
		
		if(!is_null($post_release_hook))
			$xml->addChild('post-release-hook', $post_release_hook);
		
		return $this->_execute_curl($repo_id, "release_servers.xml?environment_id=" . $environment_id, "POST", $xml->asXml());
	}

	/**
	 * Update a release server
	 *
	 * @link http://api.beanstalkapp.com/release_server.html
	 * @param integer $repo_id
	 * @param integer $server_id
	 * @param array $params Accepts - name, local_path, remote_path, remote_addr, protocol, port, login, password, use_active_mode, authenticate_by_key, use_feat, pre_release_hook, post_release_hook
	 * @return SimpleXMLElement
	 */
	public function update_release_server($repo_id, $server_id, $params = array()) {
		if(empty($repo_id) || empty($server_id))
			throw new InvalidArgumentException("Repository ID and release server ID required");
		
		if(count($params) == 0)
			throw new InvalidArgumentException("Nothing to update");
		
		$xml = new SimpleXMLElement('<release-server></release-server>');
		
		if(!is_null($params['name']))
			$xml->addChild('name', $params['name']);
		
		if(!is_null($params['local_path']))
			$xml->addChild('local-path', $params['local_path']);
		
		if(!is_null($params['remote_path']))
			$xml->addChild('remote-path', $params['remote_path']);
		
		if(!is_null($params['remote_addr']))
			$xml->addChild('remote-addr', $params['remote_addr']);
		
		if(!is_null($params['protocol']))
			$xml->addChild('protocol', $params['protocol']);
		
		if(!is_null($params['port']))
			$xml->addChild('port', $params['port']);
		
		if(!is_null($params['login']))
			$xml->addChild('login', $params['login']);

		if(!is_null($params['password']))
			$xml->addChild('password', $params['password']);

		if(!is_null($params['use_active_mode']))
			$xml->addChild('use-active-mode', $params['use_active_mode']);

		if(!is_null($params['authenticate_by_key']))
			$xml->addChild('authenticate-by-key', $params['authenticate_by_key']);

		if(!is_null($params['use_feat']))
			$xml->addChild('use-feat', $params['use_feat']);

		if(!is_null($params['pre_release_hook']))
			$xml->addChild('pre-release-hook', $params['pre_release_hook']);

		if(!is_null($params['post_release_hook']))
			$xml->addChild('post-release-hook', $params['post_release_hook']);
		
		return $this->_execute($repo_id, "release_servers/" . $server_id . ".xml", "PUT", $xml->asXml());
	}

	/**
	 * Delete a release server
	 *
	 * @link http://api.beanstalkapp.com/release_server.html
	 * @param integer $repo_id
	 * @param integer $server_id
	 * @return SimpleXMLElement
	 */
	public function delete_release_server($repo_id, $server_id) {
		if(empty($repo_id) || empty($server_id))
			throw new InvalidArgumentException("Repository ID and release server ID required");
		
		return $this->_execute_curl($repo_id, "release_servers/" . $server_id . ".xml", "DELETE");
	}


	//
	// Releases
	//

	/**
	 * Returns a Beanstalk repository's releases listing
	 *
	 * @link http://api.beanstalkapp.com/release.html
	 * @param integer $repo_id		required
	 * @param integer $page [optional] 20 results per page
	 * @return SimpleXMLElement
	 */
	public function find_all_releases($repo_id, $page = 1) {
		if(empty($repo_id))
			throw new InvalidArgumentException("Repository ID required");
		else
			return $this->_execute_curl($repo_id, "releases.xml?page=" . $page);
	}

	/**
	 * Returns a Beanstalk repository's release based on a specific release id
	 *
	 * @link http://api.beanstalkapp.com/release.html
	 * @param integer $repo_id		required
	 * @param integer $release_id		required
	 * @return SimpleXMLElement
	 */
	public function find_single_release($repo_id, $release_id) {
		if(empty($repo_id) || empty($release_id))
			throw new InvalidArgumentException("Repository ID and release ID required");
		else
			return $this->_execute_curl($repo_id, $release_id . ".xml");
	}

	/**
	 * Create a new release - ie. deploy to a server environment
	 *
	 * @link http://api.beanstalkapp.com/release.html
	 * @param integer $repo_id
	 * @param integer $environment_id
	 * @param integer $revision_id
	 * @param string $comment [optional]
	 * @param bool $deploy_from_scratch [optional]
	 * @return SimpleXMLElement
	 */
	public function create_release($repo_id, $environment_id, $revision_id, $comment = '', $deploy_from_scratch = false) {
		if(empty($repo_id) || empty($environment_id) || empty($revision))
			throw new InvalidArgumentException("Repository ID, server environment ID and revision required");
		
		$xml = new SimpleXMLElement('<release></release>');
		
		$revision_xml = $xml->addChild('revision', $revision_id);
		$revision_xml->addAttribute('type', 'integer');
		
		$xml->addChild('comment', $comment);
		$xml->addChild('deploy-from-scratch', $deploy_from_scratch);
		
		return $this->_execute_curl($repo_id, "releases.xml?environment_id=" . $environment_id, "POST", $xml->asXml());
	}

	/**
	 * Retry a failed release
	 *
	 * @link http://api.beanstalkapp.com/release.html
	 * @param integer $repo_id
	 * @param integer $release_id
	 * @return SimpleXMLElement
	 */
	public function retry_release($repo_id, $release_id) {
		if(empty($repo_id) || empty($release_id))
			throw new InvalidArgumentException("Repository ID and release ID required");
		
		return $this->_execute_curl($repo_id, "releases/" . $release_id . "/retry.xml", "PUT");
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
	 * @return SimpleXMLElement Returns false on error
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

		$curl_info = curl_getinfo($ch);
		
		if($curl_info['http_code'] != 200) {
			$this->error_code = $curl_info['http_code'];
			$this->error_string = "Curl request failed";
			throw new APIException($this->error_code . ": ".$this->error_string, $this->error_code);
		}

		// Request failed
		if ($data === FALSE) {
			$this->error_code = curl_errno($ch);
			$this->error_string = curl_error($ch);
			curl_close($ch);
			throw new APIException($this->error_code . ": " . $this->error_string, $this->error_code);
		}
		
		curl_close($ch);
		
		// Process XML into SimpleXMLElement
		return simplexml_load_string($data);
	}
}

// Exception thrown if there's a problem with the API
class APIException extends Exception {}

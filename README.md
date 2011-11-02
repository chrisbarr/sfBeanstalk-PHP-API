# Beanstalk PHP API v0.7.2 Documentation #

## Installation ##
Requires PHP 5, libcurl library and SimpleXML extension

### *Either* download source ###
Download the most recent tag from https://github.com/chrisbarr/Beanstalk-PHP-API/tags

### *Or* clone via GitHub ###
Clone the most recent copy of the repository

	git clone git://github.com/chrisbarr/Beanstalk-PHP-API.git
	cd ./Beanstalk-PHP-API

### Then include file ###

Include beanstalkapi.class.php in the php file you wish to use it in, using

	require_once('lib/beanstalkapi.class.php');

## Usage ##
Before using any of the following methods, you must first call the following:
	
	$Beanstalk = new BeanstalkAPI('ACCOUNT_NAME_HERE', 'USERNAME_HERE', 'PASSWORD_HERE');

Make sure to put your account details in the appropriate places.

Now call the API functions using the `$Beanstalk` variable, ie. `$Beanstalk->find_all_users();`

The BeanstalkAPI object can use either XML or JSON to communicate with your Beanstalk account. By default it uses JSON, and will return an array as the response. If you want to use XML, create the object like this:

	$Beanstalk = new BeanstalkAPI('ACCOUNT_NAME_HERE', 'USERNAME_HERE', 'PASSWORD_HERE', 'xml');

and it will return a SimpleXMLElement.

### API Methods ###
List of available function calls:

* `get_account_details();`
* `update_account_details();`
* `find_all_plans();`
* `find_all_users();`
* `find_single_user(user_id);`
* `find_current_user();`
* `create_user(login, email, first_name, last_name, password);`
* `update_user(user_id, params);`
* `delete_user(user_id);`
* `find_invitation(invitation_id);`
* `create_invitation(email, first_name, last_name);`
* `find_all_public_keys();`
* `find_single_public_key(key_id);`
* `create_public_key(content);`
* `update_public_key(key_id, params);`
* `delete_public_key(key_id);`
* `find_all_repositories();`
* `find_single_repository(repo_id);`
* `create_repository(name, type_id, title);`
* `update_repository(repo_id, params);`
* `find_import(import_id);`
* `create_import(repo_id, import_url);`
* `find_user_permissions(user_id);`
* `create_user_permissions(user_id, repo_id, read, write, full_deployments_access);`
* `delete_user_permissions(user_id);`
* `find_all_changesets();`
* `find_single_repository_changesets(repo_id);`
* `find_single_changeset(repo_id, revision);`
* `find_all_comments(repo_id);`
* `find_all_changeset_comments(repo_id, revision);`
* `find_single_user_comments(user_id);`
* `find_single_comment(repo_id, comment_id);`
* `create_comment(repo_id, revision_id, body, file_path, line_number);`
* `find_all_server_environments(repo_id);`
* `find_single_server_environment(repo_id, environment_id);`
* `create_server_environment(repo_id, name, automatic);`
* `update_server_environment(repo_id, environment_id, params);`
* `find_all_release_servers(repo_id, environment_id);`
* `find_single_release_server(repo_id, server_id);`
* `create_release_server(repo_id, environment_id, name, local_path, remote_path, remote_addr, protocol, port, login, password);`
* `update_release_server(repo_id, server_id, params);`
* `delete_release_server(repo_id, server_id);`
* `find_all_releases();`
* `find_all_repository_releases(repo_id);`
* `find_single_release(repo_id, release_id);`
* `create_release(repo_id, revision_id);`
* `retry_release(repo_id, release_id);`

### Examples ###
Display account details:

	<?php
		require_once('lib/beanstalkapi.class.php')
		$Beanstalk = new BeanstalkAPI('myaccount', 'chris', 'pass');
		
		$account_details = $Beanstalk->get_account_details();
		
		print_r($account_details);
	?>

Fetch a list of repositories:

	<?php
		require_once('lib/beanstalkapi.class.php');
		$Beanstalk = new BeanstalkAPI('myaccount', 'chris', 'pass');
		
		$repositories = $Beanstalk->find_all_repositories();
		
		print_r($repositories);
	?>

If there is a problem connecting to the API, the function will throw an APIException:

	<?php
		require_once('lib/beanstalkapi.class.php');
		$Beanstalk = new BeanstalkAPI('myaccount', 'chris', 'pass');
		
		try
		{
			$users = $Beanstalk->find_all_users();
			
			// This will only be executed if find_all_users() ran correctly
			print_r($users);
		}
		catch(APIException $e)
		{
			echo 'Oops, there was a problem ' . $e->getMessage();
			// Use $e->getCode() to get the returned HTTP status code of the exception
		}
	?>

## Further info ##
Detailed documentation about the API can be found on the Beanstalk website at http://api.beanstalkapp.com/

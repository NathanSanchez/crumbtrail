<?php

/**
 * API for the Profile class
 * @author Kevin Lee Kirk
 *
 *  Profile information is confidential; only the person who created a profile can view or modify that profile.
 *  Attributes involved: profileId, profileName, profileEmail, profilePhone.
 **/

require_once(dirname(__DIR__, 2) . "/classes/autoload.php");
require_once(dirname(__DIR__, 2) . "/lib/xsrf.php");
require_once("/etc/apache2/capstone-mysql/encrypted-config.php");
require_once (dirname(__DIR__,4) . "/vendor/autoload.php");

use Edu\Cnm\CrumbTrail\{Profile};

// Verify the session, start a session if not active.
if(session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Prepare an empty reply.
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;


try {
	// Connect to mySQL.
	$pdo = connectToEncryptedMySQL("/etc/apache2/capstone-mysql/crumbtrail.ini");

	// Determine which HTTP method was used: GET, PUT, or DELETE.
	$method = array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

	// Sanitize the input.
	$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$profileName = filter_input(INPUT_GET, "profileName", FILTER_SANITIZE_STRING);
	$profileEmail = filter_input(INPUT_GET, "profileEmail", FILTER_SANITIZE_EMAIL);
	$profilePhone = filter_input(INPUT_GET, "profilePhone", FILTER_SANITIZE_STRING);

	// TODO What the heck is going on in the next 4 lines?
	// Make sure the id (primary key) is valid for the methods that require it.
	 if(empty($id) === true || $id < 0) {
	 	throw(new InvalidArgumentException("profile id cannot be empty or negative", 405));
	 }

	/**
	 * Make the profile information confidential.
	 * If it's not your profile, you can't see it, modify it, or delete it.
	 **/
	if((empty($_SESSION["profile"]) === false) && (($_SESSION["profile"]->getProfileId()) === $id)) {

// --------------------------- GET --------------------------------
		if($method === "GET") {
			setXsrfCookie();

			// Get a specific profile, by profileId, then update the reply.
			if(empty($id) === false) {
				$profile = Profile::getProfileByProfileId($pdo, $id);
				if($profile !== null) {
					$reply->data = $profile;
				}
			}
//			elseif(empty($profileName) === false) {
//				$profile = Profile::getProfileByProfileName($pdo, $profileName);
//				if($profile !== null) {
//					$reply->data = $profile;
//				}
//			} elseif(empty($profileEmail) === false) {
//				var_dump($profileEmail);
//				$profile = Profile::getProfileByProfileEmail($pdo, $profileEmail);
//				if($profile !== null) {
//					$reply->data = $profile;
//				}
//			} elseif(empty($profilePhone) === false) {
//				$profile = Profile::getProfileByProfilePhone($pdo, $profilePhone);
//				if($profile !== null) {
//					$reply->data = $profile;
//				}
//			} else {
//				$profiles = Profile::getAllProfiles($pdo);
//				if($profiles !== null) {
//					$reply->data = $profiles;
//				}
//			}

// --------------------------- PUT  --------------------------------
		} elseif($method === "PUT") {
			verifyXsrf();
			$requestContent = file_get_contents("php://input");
			$requestObject = json_decode($requestContent);

			// Make sure profileName is available (required field).
			if(empty($requestObject->profileName) === true) {
				throw(new \InvalidArgumentException ("No content for Profile name.", 405));
			}
			// Make sure profileEmail is available (required field).
			if(empty($requestObject->profileEmail) === true) {
				throw(new \InvalidArgumentException ("No content for Profile email.", 405));
			}
			// Make sure profilePhone is available (required field).
			if(empty($requestObject->profilePhone) === true) {
				throw(new \InvalidArgumentException ("No content for Profile phone.", 405));
			}
			// Make sure profileType is available (required field).
			if(empty($requestObject->profileType) === true) {
				throw(new \InvalidArgumentException ("No content for Profile type.", 405));
			}

			// Retrieve the profile that will be updated in this PUT.
			$profile = Profile::getProfileByProfileId($pdo, $id);
			if($profile === null) {
				throw(new RuntimeException("The profile does not exist", 404));
			}

			// Update these attributes in this profile.
			$profile->setProfileName($requestObject->profileName);
			$profile->setProfileEmail($requestObject->profileEmail);
			$profile->setProfilePhone($requestObject->profilePhone);
			$profile->setProfileType($requestObject->profileType);

			$profile->update($pdo);

			// Update the reply message.
			$reply->message = "The profile was updated OK";
		}

// ------------ Throw exception if no valid HTTP method request has been made. ---------
	 else {
			throw (new InvalidArgumentException("Invalid HTTP method request"));
		}
	}

// -----------  Update the reply error message with the exception information.  --------
} catch (Exception $exception) {
	$reply->status = $exception->getCode();
	$reply->message = $exception->getMessage();

} catch (TypeError $typeError) {
	$reply->status = $typeError->getCode();
	$reply->message = $typeError->getMessage();
}

// ---------  Encode and return the reply to the frontend caller.  ---------------------
header("Content-type: application/json");
if($reply->data === null) {
	unset($reply->data);
}

echo json_encode($reply);

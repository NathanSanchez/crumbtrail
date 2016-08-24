<?php

require_once (dirname(__DIR__)) . "autoloader.php";//where is this? same autoload.php as before or a new one?
require_once (dirname(__DIR__) . "/lib/xsrf.php"; //when do we make this?
require_once("/etc/apache2/capstone-mysql/encrypted-config.php"); //do i put crumbtrail-mysql here?
require_once (dirname(__DIR__)) . "composer.json";

use Edu\Cnm\Crumbtrail\{Company, Profile}; //is this correct? i dont have to add mmalvar13 right? do i add company and profile like this?

/**
 * api for signUp
 *
 * @author Monica Alvarez <mmalvar13@gmail.com>
 **/

//verify the session, start if not active
if(session_status() !== PHP_SESSION_ACTIVE){
	session_start();
}

//prepare an empty reply
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;

try {
	//grab the mySQL connection
	$pdo = connectionToEncryptedMySQL("/etc/apache2/capstone-mysql/crumbtrail.ini"); //check notes for this

	//determine which HTTP method was used
	$method = array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

	//sanitize input
	$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

	//skipping "make sure the id is valid for the methods that require it"

	if($method === "POST") {
		//set XSRF cookie
		setXsrfCookie();

		verifyXsrf();
		$requestContent = file_get_contents("php://input"); //what is the directory reference here??
		$requestObject = json_decode($requestContent);

		//make sure all required fields are entered
		if(empty($requestObject->profileName) === true){
			throw(new \InvalidArgumentException("Must provide your name", 405));
		}
		if(empty($requestObject->profileEmail) === true ){
			throw(new \InvalidArgumentException("Must provide an email address", 405)); //what error code would i throw. what do i compare the inserted profile email too? do I have to traverse an array of getAllEmails???
		}
		if(empty($requestObject->profilePhone)=== true){
			throw(new \InvalidArgumentException("Must provide a phone number", 405));
		}
		if(empty($requestObject->profileType)=== true){
			$requestObject->profileType = "o"; //is this correct? anyone going this route is going to be an owner automatically right????
		}
		if(empty($requestObject->companyName)=== true){
			throw(new \InvalidArgumentException("You must enter a company name", 405));
		}
		if(empty($requestObject->companyEmail)===true){
			throw(new\InvalidArgumentException("You must enter a company email address", 405));
		}
		if(empty($requestObject->companyPhone)=== true){
			throw(new \InvalidArgumentException("You must enter a company phone number", 405));
		}
		if(empty($requestObject->companyPermit)=== true){
			throw(new \InvalidArgumentException("You must enter your businesses health permit number", 405));
		}
		if(empty($requestObject->companyLicense) === true){
			throw(new \InvalidArgumentException("Must provide a company business license number", 405));
		}
		if(empty($requestObject->companyAttn)=== true){
			throw(new \InvalidArgumentException("Must provide company attention contact", 405));
		}
		if(empty($requestObject->companyStreet1) === true){
			throw(new \InvalidArgumentException("Must provide company address", 405));
		}
		if(empty($requestObject->companyStreet2)=== true){
			$requestObject->companyStreet2 = null;
		}
		if(empty($requestObject->companyCity)=== true){
			throw(new \InvalidArgumentException("Must provide city", 405));
		}
		if(empty($requestObject->companyState)=== true){
			throw(new \InvalidArgumentException("Must provide state", 405));
		}
		if(empty($requestObject->companyZip)=== true){
			throw(new \InvalidArgumentException("Must provide zip code", 405));
		}
		if(empty($requestObject->companyDescription) === true){
			$requestObject->companyDescription = null;
		}
		if(empty($requestObject->companyMenuText) === true){
			$requestObject->companyMenuText = null;
		}

		//sanitize the email
		$profileEmail = filter_var($requestObject->profileEmail, FILTER_SANITIZE_EMAIL);
		$profile = Profile::getProfileByProfileEmail($pdo, $profileEmail);
		if($profile !== null){
			throw(new \InvalidArgumentException("this email already has an account",422);
		}

		//before hashing and salting, angular sends password and confirmed password. throw exception if they are not the same. requestObject->password/confirm password
		//hash and salt it here. same as in the test. whatever is in the setup method in test

		//create a new profile and insert it into the databases
		$profile = new Profile(null, $requestObject->profileName, $requestObject->profileEmail, $requestObject->profilePhone, $profileAccessToken, $profileActivationToken, $requestObject->profileType, $profileHash, $profileSalt);

		$profile->insert($pdo);

		//create a new company and insert it into the database
		$company = new Company(null, $profile->getProfileId(), $requestObject->companyName, $requestObject->companyEmail, $requestObject->companyPhone, $requestObject->companyPermit, $requestObject->companyLicense, $requestObject->companyAttn, $requestObject->companyStreet1, $requestObject->companyStreet2, $requestObject->companyCity, $requestObject->companyState, $requestObject->companyZip, $requestObject->companyDescription , $requestObject->companyMenuText, $companyActivationToken, $companyApproved);

		$company->insert($pdo);

		//update reply
		$reply->message = "In the next 48 hours you will receive your approval notice from Crumbtrail. Check your email to activate your account";

		//swiftmailer code here

		//create Swift message
		$swiftMessage = Swift_Message::newInstance();

		//attach the sender to the message
		//this takes the form of an associative array where the Email is the key for the real name
		$swiftMessage->setFrom(["crumbtrail@gmail.com" => "CrumbTrail"]);

		/**
		 * attach the recipients to the message
		 * notice this is an array that can inlcude or omit the recipient's real name
		 * use the recipient's real name where possible; this reduces the probability of the Email being marked as spam
		 **/
		$recipients = [$requestObject->profileEmail];
		$swiftMessage->setTo($recipients);

		//attach the subject line to the message
		$swiftMessage->setSubject("Please confirm your CrumbTrail account to activate");

		/**
		 * attach the actual message to the message
		 * here, we set two versions of the message: the HTML formatted message and a special filter_var()ed version of the message that generates a plaint ext version of the HTML content.
		 * notice one tactic used is to display the entire $confirmLink to plain text; this lets users who aren't viewing HTML content in emails to still access your links
		 **/

		//building the activation link that can travel to another server and still work. This is the link that will be clicked to confirm

		$lastSlash = strrpos($_SERVER["SCRIPT_NAME"], "/");
		$basePath = substr($_SERVER["SCRIPT_NAME"], 0, $lastSlash + 1);
		$urlglue = $basePath . "email-confirmation?emailActivation=" . $profileEmailActivation;

		$confirmLink = "https://" . $_SERVER["SERVER_NAME"] . $urlglue;

		$message = <<< EOF
<h1> Thanks for signing up with Crumbtrail!</h1>
<p>Your food truck has been approved. Please click on the following link to set up your profile and start serving: </p>
<a href = "$confirmLink">$confirmLink</a></p>
EOF;

		$swiftMessage->setBody($message, "text/html");
		$swiftMessage->addPart(html_entity_decode(filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)), "text/plain");


		/**
		 * send the Email via SMTP; the SMTP server here is configured to relay everything upstream via CNM
		 * this default may or may not be available on all web hosts; consult their documentation/support for details
		 * SwiftMailer supports many different transport methods; SMTP was chosen because it's the most compatible and has the best error handling
		 *
		 * @see http://swiftmailer.org/docs/sending.html Sending Messages - Documentation - SwiftMailer
		 **/

		$smtp = Swift_SmtpTransport::newInstance("localhost", 25);
		$mailer = Swift_Mailer::newInstance($smtp);
		$numSent = $mailer->send($swiftMessage, $failedRecipients);

		/**
		 * the send method returns the number of recipients that accepted the Email
		 * so, if the number attempted is not the number accepted, this is an exception
		 **/
		if($numSent !== count($recipients)){
			//the $failedRecipients parameter passed in the send() method now contains an array of the Emails that failed
			throw(new RuntimeException("unable to send email"));
		}



	} else {
		throw(new InvalidArgumentException("Invalid HTTP method request"));
	}

	//update reply with exception information
}catch(Exception $exception){
		$reply->status = $exception->getCode();
		$reply->message = $exception->getMessage();
		$reply->trace = $exception->getTraceAsString();
	}catch(TypeError $typeError){
		$reply->status = $typeError->getCode();
		$reply->message = $typeError->getMessage();
	}

header("Content-type: application/json");
if($reply->data === null){
	unset($reply->data);
}

//encode and return reply to front end caller
echo json_encode($reply);


<?php

namespace Edu\Cnm\CrumbTrail;

require_once("autoload.php");

/**
 * Class ExtraServing
 *
 * this class is used to keep track of the most special and unique of food truck
 * serving events. These 'Extra Servings' will be uncommon events the food-truck company plans to attend
 * sometime in the future
 *
 * Author @L   baca.loren@gmail.com
 */
class ExtraServing implements \JsonSerializable {

	use ValidateDate;

	/**
	 * primary key for ExtraServing Class
	 * @var int $extraServingId
	 */
	private $extraServingId;

	/**
	 * foreign key to Company Class
	 * @var int $extraServingCompanyId
	 */
	private $extraServingCompanyId;

	/**
	 * description of the extra serving event
	 * @var string $extraServingDescription
	 */
	private $extraServingDescription;

	/**
	 * location of where the food truck will be serving
	 * @var string $extraServingLocation
	 */
	private $extraServingLocation;

	/**
	 * start time of the extra serving event
	 * @var \DateTime $extraServingStartTime
	 */
	private $extraServingStartTime;

	/**
	 * end time of the extra serving event
	 * @var \DateTime $extraServingEndTime
	 */
	private $extraServingEndTime;


	/**
	 * constructor for extraServing
	 */


//	--------------------------------------SETTERS AND GETTERS SECTION---------------------------------

	/**
	 * getter for extraServingId
	 * @return int|null for $extraServingId
	 */

	public function getExtraServingId() {
		return ($this->extraServingId);

	}

	/**
	 * setter for extraServingId
	 * @param int|null $newExtraServingId
	 * @throws \InvalidArgumentException if $newExtraServingId not valid
	 * @throws \RangeException if $newExtraServingId negative or zero
	 * @throws \TypeError if $newExtraServingId not an int
	 */

	public function setExtraServingId(int $newExtraServingId = null){

		if($newExtraServingId === null){
			$this->extraServingId = null;
			return;
		}

		if($newExtraServingId <= 0){
			throw(new \RangeException("The Extra Serving ID cannot be negative or zero"));
		}

		$this->extraServingId = $newExtraServingId;
	}


	/**
	 * getter for extraServingCompanyId
	 * @return int|null for $extraServingCompanyId
	 */
	public function getExtraServingCompanyId(){
		return ($this->extraServingCompanyId);
	}

	/**
	 * setter for extraServingCompanyId
	 * @param int|null for $newExtraServingCompanyId
	 * @throws \InvalidArgumentException if $newExtraServingCompanyId not valid
	 * @throws \RangeException if $newExtraServingCompanyId is less than or equal to zero
	 * @throws \TypeError if $newExtraServingCompanyId not an int
	 */
	public function setExtraServingCompanyId(int $newExtraServingCompanyId){
		if($newExtraServingCompanyId <= 0){
			throw(new \RangeException("company ID cannot be negative or zero!"));
		}
		$this->extraServingCompanyId = $newExtraServingCompanyId;

	}


	/**
	 * getter for extraServingDescription
	 * @return string for $extraServingDescription
	 */
	public function getExtraServingDescription(){
		return ($this->extraServingCompanyId);
	}

	/**
	 * setter for extraServingDescription
	 * @param string $newExtraServingDescription
	 * @throws \InvalidArgumentException if $newExtraServingDescription not a string or insecure
	 * @throws \RangeException if $newExtraServingDescription longer than 4096 char
	 * @throws \TypeError if $newExtraServingDescription not a string
	 */
	public function setExtraServingDescription(string $newExtraServingDescription){

		$newExtraServingDescription = trim($newExtraServingDescription);
		$newExtraServingDescription = filter_var($newExtraServingDescription, FILTER_SANITIZE_STRING);

		if(strlen($newExtraServingDescription) === 0){
			throw(new \InvalidArgumentException("Please enter a description!"));
		}

		if(strlen($newExtraServingDescription) > 4096){
			throw(new \InvalidArgumentException("The description is too long!"));
		}

		$this->extraServingDescription = $newExtraServingDescription;
	}


	/**
	 * getter for extraServingLocation
	 * @return string for $extraServingLocation
	 */
	public function getExtraServingLocation(){
		return ($this->extraServingLocation);
	}

	/**
	 * setter for extraServingLocation
	 * @param string $newExtraServingLocation
	 * @throws \InvalidArgumentException if $newExtraServingLocation not a string or insecure
	 * @throws \RangeException if $newExtraServingLocation longer than 512 char
	 * @throws \TypeError if $newExtraServingLocation not a string
	 */
	public function setExtraServingLocation(string $newExtraServingLocation){

		$newExtraServingLocation = trim($newExtraServingLocation);
		$newExtraServingLocation = filter_var($newExtraServingLocation, FILTER_SANITIZE_STRING);

		if(strlen($newExtraServingLocation) === 0){
			throw(new \InvalidArgumentException("Please enter a location!"));
		}

		if(strlen($newExtraServingLocation) > 512){
			throw(new \InvalidArgumentException("The location is too long!"));
		}

		$this->extraServingLocation = $newExtraServingLocation;
	}


	/**
	 * getter for extraServingStartTime
	 * @return \DateTime for $extraServingStartTime
	 */
	public function getExtraServingStartTime(){
		return ($this->extraServingStartTime);
	}

	/**
	 * setter for extraServingStartTime
	 * @param \DateTime|string for $newExtraServingStartTime
	 * @throws \InvalidArgumentException if $newExtraServingStartTime is null
	 * @throws \RangeException if $newExtraServingStartTime is less than current date-time
	 */
	public function setExtraServingStartTime(\DateTime $newExtraServingStartTime)



}
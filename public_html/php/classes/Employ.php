<?php
namespace Edu\Cnm\CrumbTrail; //idk

require_once("autoload.php");

/**
 * Welcome to the Employ class! Enjoy your stay!
 *
 * @author Monica Alvarez <mmalvar13@gmail.com>
 **/
class Employ implements \JsonSerializable {
	/**
	 * id of the Company that employed the profile, this is a foreign key. Composite key with $employProfileId.
	 * @var int|null $employCompanyId //null??
	 **/
	private $employCompanyId;
	/**
	 * id of the profile that is employed by the company, this is a foreign key. Composite key with $employCompanyId.
	 * @var int|null $employProfileId //null??
	 **/
	private $employProfileId;



	/*-------------------------------Constructor--------------------------------*/

	/**
	 * Employ class constructor.
	 * @param int $newEmployProfileId id of employProfile
	 * @param int $newEmployCompanyId id of employCompany
	 * @throws \InvalidArgumentException if data types are not valid
	 * @throws \RangeException if id is not positive
	 * @throws \TypeError if data types violate type hints
	 * @throws \Exception if any other exception occurs
	 **/
	public function __construct(int $newEmployCompanyId, int $newEmployProfileId) {
		try {
			$this->setEmployCompanyId($newEmployCompanyId);
			$this->setEmployProfileId($newEmployProfileId);
		} catch(\InvalidArgumentException $invalidArgument) {
			//rethrow the exception to the caller
			throw(new \InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(\RangeException $range) {
			//rethrow the exception to the caller
			throw(new \RangeException($range->getMessage(), 0, $range));
		} catch(\TypeError $typeError) {
			//rethrow exception to the caller
			throw(new \TypeError($typeError->getMessage(), 0, $typeError));
		} catch(\Exception $exception) {
			//rethrow the exception to the caller
			throw(new \Exception($exception->getMessage(), 0, $exception));
		}
	}

	/*-------------------------Accessor and Mutator Methods----------------------------*/

	/**
	 * accessor method for employProfileId
	 * @return int $employProfileId
	 **/
	public function getEmployProfileId() {
		return ($this->employProfileId);
	}

	/**
	 * mutator method for employProfileId
	 * @param int $newEmployProfileId new value of employProfileId
	 * @throws \InvalidArgumentException if $newEmployProfileId is null
	 * @throws \RangeException if $newEmployProfileId is not positive
	 * @throws \TypeError if $newEmployProfileId is not an integer
	 * @throws \Exception if any other exception occurs
	 **/
	public function setEmployProfileId(int $newEmployProfileId) {
		//verify that the profile id is positive
		if($newEmployProfileId === null){
			throw(new \InvalidArgumentException("employProfileId cannot be null"));
		}
		if($newEmployProfileId <= 0) {
			throw(new \RangeException("employ profile id is not positive"));
		}
		//convert and store the employProfileId
		$this->employProfileId = $newEmployProfileId;
	}



	/**
	 * accessor method for employCompanyId
	 * @return int $employCompanyId
	 **/
	public function getEmployCompanyId() {
		return ($this->employCompanyId);
	}

	/**
	 * mutator method for employCompanyId
	 * @param int $newEmployCompanyId is the new value of employCompanyId
	 * @throws \RangeException if $newEmployCompanyId is not positive
	 * @throws \TypeError if $newEmployCompanyId is not an integer
	 * @throws \Exception if any other exception occurs
	 **/
	public function setEmployCompanyId(int $newEmployCompanyId) {
		if($newEmployCompanyId === null){
			throw(new \InvalidArgumentException("employCompanyId cannot be null"));
		}
		if($newEmployCompanyId <= 0) {
			throw(new \RangeException("employ company id is not positive"));
		}
		//convert and store the employCompanyId
		$this->employCompanyId = $newEmployCompanyId;
	}

	/*-------------------------------PDO Connection Objects Here------------------------------*/
	/*----------------only need insert, delete, and select for this weak entity---------------*/
	/**
	 * inserts this employ into mySQL. is there a better way i can write this? that is not english.
	 *
	 * @param \PDO $pdo PDO Connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function insert(\PDO $pdo) {
		//enforce that this employProfileId is not null (i.e. don't insert an employProfileId that hasn't been assigned. Right?? its a foreign key.
		if($this->employCompanyId === null || $this->employProfileId === null) {
			throw(new \PDOException("cannot insert a foreign key that does not exist")); //usually for insert we don't want to enter something that already exists. is this normal for weak entities?
		}

		//create query template
		$query = "INSERT INTO employ(employCompanyId, employProfileId) VALUES(:employCompanyId, :employProfileId)";
		$statement = $pdo->prepare($query);

		//bind the member variables to the placeholders in the template
		$parameters = ["employCompanyId" => $this->employCompanyId, "employProfileId" => $this->employProfileId];
		$statement->execute($parameters);
	}

	/**
	 * delete this employ into mySQL.
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function delete(\PDO $pdo) {
		//enforce that the employProfileId and employCompanyId are not null (i.e. don't delete a composite key that does not exist)
		if($this->employCompanyId === null || $this->employProfileId === null) {
			throw(new \PDOException("cannot delete a composite key that does not exist"));
		}
		//create query template
		$query = "DELETE FROM employ WHERE employCompanyId = :employCompanyId AND employProfileId = :employProfileId"; //is this correct? would we ever need to use this IRL??? composite keys should never die! right?

		$statement = $pdo->prepare($query);

		//bind the member variables to the place holder in the template
		$parameters = ["employCompanyId" => $this->employCompanyId, "employProfileId" => $this->employProfileId];
		$statement->execute($parameters);
	}

	/**
	 * get employ by employCompanyId
	 * @param \PDO $pdo PDO connection object
	 * @param int $employCompanyId employ company id to search for
	 * @return \SplFixedArray SplFixedArray of employs found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getEmployByEmployCompanyId(\PDO $pdo, int $employCompanyId) {
		//sanitize the employCompanyId before searching by checking that it is a positive number
		if($employCompanyId <= 0) {
			throw(new \PDOException("employCompanyId is not positive"));
		}
		//create query template
		$query = "SELECT employCompanyId, employProfileId FROM employ WHERE employCompanyId = :employCompanyId";
		$statement = $pdo->prepare($query);

		//bind the employCompanyId to the place holder in the  template
		$parameters = ["employCompanyId" => $employCompanyId];
		$statement->execute($parameters);

		//build an array of employs...
		$employs = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$employ = new Employ($row["employCompanyId"],$row["employProfileId"]);
				$employs[$employs->key()] = $employ;
				$employs->next();
			} catch(\Exception $exception) {
				//if the row couldn't be converted rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($employs);
	}

	/**
	 * get employ by employProfileId
	 * @param \PDO $pdo PDO connection object
	 * @param int $employProfileId employProfileId to search for
	 * @return \SplFixedArray SplFixedArray of employs found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getEmployByEmployProfileId(\PDO $pdo, int $employProfileId) {
		//sanitize the employProfileId before searching by check that it is a positive number
		if($employProfileId <= 0) {
			throw(new \PDOException("employProfileId is not positive"));
		}

		//create query template
		$query = "SELECT employCompanyId, employProfileId FROM employ WHERE employProfileId = :employProfileId";
		$statement = $pdo->prepare($query);

		//bind employProfileId to the placeholder in the template
		$parameters = ["employProfileId" => $employProfileId];
		$statement->execute($parameters);

		//build an array of employs
		$employs = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$employ = new Employ($row["employCompanyId"],$row["employProfileId"]);
				$employs[$employs->key()] = $employ;
				$employs->next();
			} catch(\Exception $exception) {
				//if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($employs);
	}

	/**
	 * get employ by employCompanyId and employProfileId
	 * @param \PDO $pdo PDO connection object
	 * @param int $employCompanyId employCompanyId to search for
	 * @param int $employProfileId employProfileId to search for
	 * @return employ|null employ if found or null if not found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/

	public static function getEmployByEmployCompanyIdAndEmployProfileId(\PDO $pdo, int $employCompanyId, int $employProfileId) {
		if($employCompanyId === null || $employProfileId === null) {
			throw(new \PDOException("employCompanyId and employProfileId must exist"));
		}

		//sanitize the employeeCompanyId and the employeeProfileId by checking that they're positive
		if($employCompanyId <= 0 || $employProfileId <= 0) {
			throw(new \PDOException("employCompanyId and employProfileId must be positive"));
		}

		//create query template
		$query = "SELECT employCompanyId, employProfileId FROM employ WHERE employCompanyId = :employCompanyId AND employProfileId = :employProfileId";
		$statement = $pdo->prepare($query);

		//bind employCompanyId and employProfileId to the placeholder in the template
		$parameters = ["employCompanyId" => $employCompanyId, "employProfileId" => $employProfileId];
		$statement->execute($parameters);

		//grab the employ from mySQL
		try {
			$employ = null;
			$statement->setFetchMode(\PDO::FETCH_ASSOC);
			$row = $statement->fetch();
			if($row !== false) {
				$employ = new Employ($row["employCompanyId"], $row["employProfileId"]);
			}
		} catch(\Exception $exception) {
			//if the row couldn't be converted, rethrow it
			throw(new \PDOException($exception->getMessage(), 0, $exception));
		}
		return $employ;
	}



	/**
	 * added get all employs. may not need this
	 *
	 * @param \PDO $pdo connection object
	 * @return \SplFixedArray SplFixedArray of Employs found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not correct data type
	 **/

	public static function getAllEmploys(\PDO $pdo){
		//create query template
		$query = "SELECT employCompanyId, employProfileId FROM employ";
		$statement = $pdo->prepare($query);
		$statement->execute();

		//build an array of employs
		$employs = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false){
			try{
				$employ = new Employ($row["employCompanyId"], $row["employProfileId"]);
				$employs[$employs->key()] = $employ;
				$employs->next();
			}catch(\Exception $exception){
				//if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return($employs);

	}

	/**
	 * formats the state variables for JSON serialization
	 *
	 * @return array resulting in state variables  to serialize
	 *
	 **/
	public function jsonSerialize() {
		$fields = get_object_vars($this);
		return ($fields);
	}

}
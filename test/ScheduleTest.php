<?php
namespace Edu\Cnm\CrumbTrail\Test;

use Edu\Cnm\CrumbTrail\{
	Company, Schedule, Profile
};

//grab the project test parameters
require_once("CrumbTrailTest.php");

//grab the class under scrutiny
require_once(dirname(__DIR__) . "/public_html/php/classes/autoload.php");

/**
 * unit test for the schedule class
 *
 * All mySQL/PDO enabled methods test for valid and invalid inputs
 *
 * @see Schedule
 * @author Victoria Chacon <victoriousdesignco@gmail.com>
 **/
class ScheduleTest extends CrumbTrailTest {
	//setting up made up variables to test
	//--------------STATE VARIABLES: PROTECTED SECTION----------------------------//
	/**
	 * company that this schedule is attributed to. This is a foreign key relationship.
	 * @var Company company
	 **/
	protected $company = null;
	//we dont need the primary or foreign key here right???
	/**
	 * Generic data for the Schedule day of the week
	 * @var string $VALID_SCHEDULEDAYOFWEEK1
	 **/
	protected $VALID_SCHEDULEDAYOFWEEK1 = "Monday";
	/**
	 * Generic data for the Schedule day of the week
	 * @var string $VALID_SCHEDULEDAYOFWEEK2
	 **/
	protected $VALID_SCHEDULEDAYOFWEEK2 = "Wednesday";
	/**
	 * Timestamp data for the Schedule End Time
	 * @var \DateTime $VALID_SCHEDULEENDTIME1
	 **/
	protected $VALID_SCHEDULEENDTIME1 = null;
	/**
	 * Timestamp data for the Schedule End Time
	 * @var \DateTime $VALID_SCHEDULEENDTIME2
	 **/
	protected $VALID_SCHEDULEENDTIME2 = null;
	/**
	 * Generic data for the Schedule location address
	 * @var string $VALID_SCHEDULELOCATIONADDRESS1
	 */
	protected $VALID_SCHEDULELOCATIONADDRESS1 = "1312 Awesome Food rd SW albuquerque NM. 87121";
	/**
	 * Generic data for the Schedule location address
	 * @var string $VALID_SCHEDULELOCATIONADDRESS2
	 */
	protected $VALID_SCHEDULELOCATIONADDRESS2 = "9201 Spicy Food ln SW albuquerque NM. 87114";
	/**
	 * Generic data for the Schedule Location Name
	 * @var string $VALID_SCHEDULELOCATIONNAME1
	 */
	protected $VALID_SCHEDULELOCATIONNAME1 = "The Rail Yards";
	/**
	 * Generic data for the Schedule Location Name
	 * @var string $VALID_SCHEDULELOCATIONNAME2
	 */
	protected $VALID_SCHEDULELOCATIONNAME2 = "418 Teapot Event ";
	/**
	 * Timestamp data for the Schedule Start Time
	 * @var /DateTime $VALID_SCHEDULESTARTTIME1
	 */
	protected $VALID_SCHEDULESTARTTIME1 = null;
	/**
	 * Timestamp data for the Schedule Start Time
	 * @var /DateTime $VALID_SCHEDULESTARTTIME2
	 */
	protected $VALID_SCHEDULESTARTTIME2 = null;

//---------CREATING ALREADY MADE STUFF THAT WILL BE INSERTED, UPDATED, DELETED-----//
//------------------------BASED ON THE INFORMATION ABOVE---------------------------//

	public final function setUp() {

		parent::setUp();

		//creating a fake company so that we can make a connection between company and schedule
		$password = "abc123";
		$salt = bin2hex(random_bytes(16));
		//what was the blue number below again??
		$hash = hash_pbkdf2("sha512", $password, $salt, 262144);

		//kept this stuff the same as my last test since it worked the last time.....why not
		//took out the second profile and company...dont think i need these in the last test i only needed them in the event that we needed a truck to move over to another company....which is not the case here hehe.

		//------------Profile 1--------------------------------------------------
		$this->profile = new Profile(null, "Terry", "test@phpunit.de", "12125551212", "0000000000000000000000000000000000000000000000000000000000004444", "00000000000000000000000000000022", "o", $hash, $salt);
		// Insert the dummy profile object into the database.
		$this->profile->insert($this->getPDO());
		$pdoProfile = Profile::getProfileByProfileId($this->getPDO(), $this->profile->getProfileId());

//	//--------------Profile 2-------------------------------------------------
//	$this->profile2 = new Profile(null, "James", "james@gmail.com", "12125555567", "0000000000000000000000000000000000000000000000000000000000005555", "00000000000000000000000000000088", "e", $hash, $salt);
//	// Insert the dummy profile object into the database.
//	$this->profile2->insert($this->getPDO());
//	$pdoProfile2 = Profile::getProfileByProfileId($this->getPDO(), $this->profile2->getProfileId());

		//create and insert a company to own the test schedule
		//---------------------company1------------------------------------------------
		$this->company = new Company(null, $pdoProfile->getProfileId(), "Terry's Tacos", "terrytacos@tacos.com", "5052345678", "12345", "2345", "attn: MR Taco", "345 Taco Street", "Taco Street 2", "Albuquerque", "NM", "87654", "We are a Taco truck description", "Tacos, Tortillas, Burritos", "84848409878765432123456789099999", 1);
		$this->company->insert($this->getPDO());

//	//--------------------------company2-----------------------------------------
//	//create and insert a second company to buy the test truck (a truck moving to another company)
//	$this->company2 = new Company(null, $pdoProfile2->getProfileId(), "Truckina's Crepes", "truckina@trucks.com", "5052345666", "45678", "4567", "attn: MRS Crepe", "456 Crepe Street", "CrepeStreet2", "Albuquerque", "NM", "45678", "We sell crepes", "crepes, ice cream, cakes", "34343409876543212345678998787654", 0);
//	$this->company2->insert($this->getPDO());
		//SETUP DATE TIME OBJECTS
		$this->VALID_SCHEDULESTARTTIME1 = new \DateTime();
		$this->VALID_SCHEDULEENDTIME1 = clone $this->VALID_SCHEDULESTARTTIME1;
		$this->VALID_SCHEDULEENDTIME1->add(new \DateInterval('PT1H'));

		$this->VALID_SCHEDULESTARTTIME2 = new \DateTime();
		$this->VALID_SCHEDULEENDTIME2 = clone $this->VALID_SCHEDULESTARTTIME2;
		$this->VALID_SCHEDULEENDTIME2->add(new \DateInterval('PT1H'));

	}
	//---------------------------NOW.....WE TEST!!!!!--------------------------------//
	/**
	 *Insert a valid schedule object into SQL
	 **/
	public function testInsertValidSchedule() {
		$numRows = $this->getConnection()->getRowCount("schedule");

		$schedule = new Schedule(null, $this->company->getCompanyId(), $this->VALID_SCHEDULEDAYOFWEEK1, $this->VALID_SCHEDULEENDTIME1, $this->VALID_SCHEDULELOCATIONADDRESS1, $this->VALID_SCHEDULELOCATIONNAME1, $this->VALID_SCHEDULELOCATIONNAME1, $this->VALID_SCHEDULESTARTTIME1);

		$schedule->insert($this->getPDO());

		//make sure this data in sql matches what we have above.....

		$pdoSchedule = Schedule::getScheduleByScheduleId($this->getPDO(), $schedule->getScheduleId());

		//grab the data from mySQL and enforce the fields match our expectations
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("schedule"));

		$this->assertEquals($pdoSchedule->getScheduleCompanyId(), $this->company->getCompanyId());
		$this->assertEquals($pdoSchedule->getScheduleDayOfWeek(), $this->VALID_SCHEDULEDAYOFWEEK1);
		$this->assertEquals($pdoSchedule->getScheduleEndTime(), $this->VALID_SCHEDULEENDTIME1);
		$this->assertEquals($pdoSchedule->getScheduleLocationAddress(), $this->VALID_SCHEDULELOCATIONADDRESS1);
		$this->assertEquals($pdoSchedule->getScheduleLocationName(), $this->VALID_SCHEDULELOCATIONNAME1);
		$this->assertEquals($pdoSchedule->getScheduleStartTime(), $this->VALID_SCHEDULESTARTTIME1);

	}
	/**
	 * Test inserting an INVALID schedule into the SQL
	 * @expectedException \PDOException
	 **/
	public function testInsertInvalidSchedule() {

	}

//   "}"   NOT SURE IF THIS BELONGED SOMEWHERE...OOOOPS


}
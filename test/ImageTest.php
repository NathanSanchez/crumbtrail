<?php
namespace Edu\Cnm\CrumbTrail\Test;

use Edu\Cnm\CrumbTrail\{Company, Profile, Image};

//grab the project test parameters
require_once("CrumbTrailTest.php");

//grab the class under scrutiny
require_once(dirname(__DIR__) . "/public_html/php/classes/autoload.php");

/**
 * Unit test for the image class 
 * 
 * All mySQL/PDO enabled methods tested for valid and invalid inputs.
 * 
 * @see Image
 * @author Victoria Chacon <victoriousdesignco@gmail.com>
 */
class ImageTest extends CrumbTrailTest {
	//setting up made up variables to test
	/**
	 * content of the image file type
	 * @var string $VALID_IMAGEFILETYPE
	 **/
	protected $VALID_IMAGEFILETYPE = "image/jpg";
	/**
	 * content of the updated image file type?
	 * @var string $VALID_IMAGEFILETYPE
	 **/
	protected $VALID_IMAGEFILETYPE2 = "image/jpeg";
	/**
	 * content of the image file name
	 * @var string $VALID_FILENAME
	 **/
	protected $VALID_IMAGEFILENAME = "TheAwesomeCuisineOrder";
	/**
	 * content for the updated image file name
	 * @var string $VALID_FILENAME
	 **/
	protected $VALID_IMAGEFILENAME2 = "SomeAwesomeUnderstatedCuisineEatery";
	/**
	 * Company that the Image belongs to; this is a foreign key relationship
	 * @var Company company
	 **/
	protected $company = null;
	/**
	 * Profile that the Image belongs to; this is a foreign key relationship
	 * @var Profile profile
	 **/
	protected $profile = null;
	/**
	 * create dependent objects before running each test
	 **/
	//run the default setUp() method first (creating a fake company to house the test image)
	public final function setUp() {
		//run the default setUp() method first
		parent::setUp();

		//--------------------------Dummy Profile------------------------------------------
	//Dummy profile to feed a foreign key into the dummy company we created
		$password = "abc123";
		$salt = bin2hex(random_bytes(16));
		$hash = hash_pbkdf2("sha512", $password, $salt, 262144);


		//create and insert a Profile to own the test image
		$this->profile = new Profile(null, "Victoria C", "victorious-design.com", "5057303164", "0000000000000000000000000000000000000000000000000000000000004444","00000000000000000000000000000022", "a", $hash , $salt);
		$this->profile->insert($this->getPDO());
		$pdoProfile = Profile::getProfileByProfileId($this->getPDO(), $this->profile->getProfileId());

		//---------------------------------Dummy companies-----------------------------------------
		//create and insert a company to own the test image
		$this->company = new Company(null, $pdoProfile->getProfileId(), "Terry's Tacos", "terrytacos@tacos.com", "5052345678", "12345", "2345", "attn: MR Taco", "345 Taco Street", "Taco Street 2", "Albuquerque", "NM", 87654, "We are a Taco truck description", "Tacos, Tortillas, Burritos", "848484", 1);
		$this->company->insert($this->getPDO());
	}
	/**
	 * insert valid image and verify that the actual mySQL data matches*
	 **/ 
	public function testInsertValidImage() {
		$numRows = $this->getConnection()->getRowCount("image");

		//create a new Image and insert it into mySQL
		//$image = new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, //$this->VALID_IMAGEFILENAME);

		$image= new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);
		$image->insert($this->getPDO());//

		//grab the data from mySQL and enforce the fields to match our expectations
		$pdoImage = Image::getImageByImageId($this->getPDO(), $image->getImageId());
		//$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));//
		$this->assertEquals($pdoImage->getImageCompanyId(), $this->company->getCompanyId());
		$this->assertEquals($pdoImage->getImageFileType(), $this->VALID_IMAGEFILETYPE);
		$this->assertEquals($pdoImage->getImageFileName(), $this->VALID_IMAGEFILENAME);
	}
	/**
	 * test inserting an Image that already exists
	 *
	 * @expectedException \PDOException
	 * @throws \RangeException
	 * @throws \InvalidArgumentException
	 * @throws \PDOException
	 **/

	public function testInsertInvalidImage() {
		//create an image with a non null image id and it will fail
		$image = new Image(CrumbTrailTest::INVALID_KEY, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);
		$image->insert($this->getPDO());
	}
	/**
	 * test inserting an Image, editing it, and then updating it
	 **/
	public function  testUpdateValidImage() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		// create a new Image and insert into mySQL
		$image = new Image(null, $this->company->getCompanyId(),$this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);
		$image->insert($this->getPDO());

		//edit the Image and update it in mySQL
		$image->setImageFileType($this->VALID_IMAGEFILETYPE2);
		$image->setImageFileName($this->VALID_IMAGEFILENAME2);
		// now set this up to update
		$image->update($this->getPDO());

		//grab the data from mySQL and enforce the fields match our expectations
		$pdoImage = Image::getImageByImageId($this->getPDO(), $image->getImageId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$this->assertEquals($pdoImage->getImageCompanyId(), $this->company->getCompanyId());
		$this->assertEquals($pdoImage->getImageFileType(), $this->VALID_IMAGEFILETYPE2);
		$this->assertEquals($pdoImage->getImageFileName(), $this->VALID_IMAGEFILENAME2);
	}
	/**
	 * test updating and image that does not exist
	 *
	 * @expectedException \PDOException
	 * @throws \RangeException
	 * @throws \InvalidArgumentException
	 * @throws \PDOException
	 **/
	public function testUpdateInvalidImage() {
		// create an Image, try to update it without actually updating it, watch if fail.
		$image = new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE,$this->VALID_IMAGEFILENAME);
		$image->update($this->getPDO());
	}
	/**
	 * test creating an image and deleting it
	 *
	 */
	public function testDeleteValidImage() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		//create new Image and insert into mySQL
		$image = new Image(null, $this->company->getCompanyId(),$this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);
		$image->insert($this->getPDO());

		//delete the image from mySQL
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$image->delete($this->getPDO());

		//grab the data from mySQL and enforce that the Image does not exist.
		$pdoImage = Image::getImageByImageId($this->getPDO(), $image->getImageId());
		$this->assertNull($pdoImage);
		$this->assertEquals($numRows, $this->getConnection()->getRowCount("image"));
	}
	/**
	 * test deleting an Image that does not exist
	 *
	 * @expectedException \PDOException
	 * @throws \RangeException
	 * @throws \InvalidArgumentException
	 * @throws \PDOException
	 **/
	public function testDeleteInvalidImage() {
		//create an Image and try to delete it without actually inserting it
		$image = new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);
		$image->delete($this->getPDO());
	}
	/**
	 * test getting images by image company id
	 *
	 **/
	public function testGetImageByImageCompanyId() {

		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		// Create a new Image and insert it into mySQL
		$image = new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME, $this->VALID_IMAGEFILETYPE2, $this->VALID_IMAGEFILENAME2);
		$image->insert($this->getPDO());

		//Grab the data from mySQL and check that the fields match our expectations.
		$results = Image::getImageByImageCompanyId($this->getPDO(), $this->company->getCompanyId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\CrumbTrail\\Image", $results);
		//grab the result from the array and validate it
		$pdoImage = $results[0];
		$this->assertEquals($pdoImage->getImageCompanyId(), $this->company->getCompanyId());
		$this->assertEquals($pdoImage->getImageFileType(), $this->VALID_IMAGEFILETYPE);
		$this->assertEquals($pdoImage->getImageFileName(), $this->VALID_IMAGEFILENAME);
	}
	/**
	 * Test getting image by image File Name
	 **/
	public function testGetImageByImageFileName() {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		//Create a new Image and insert it into mySQL
		$image = new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);

		$image->insert($this->getPDO());

		//Grab the data from mySQL and enforce the fields that match our expectations
		$results = Image::getImageByImageFileName($this->getPDO(), $this->company->getCompanyId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\CrumbTrail\\Image", $results);

		//grab the results from the array and validate it
		$pdoImage = $results;
	}

	//
	/**
	 * test grabbing all Images
	 *
	 */
	public function testGetAllValidImages() {
		//count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		// create a new Image and insert to mySQL
		$image = new Image(null, $this->company->getCompanyId(), $this->VALID_IMAGEFILETYPE, $this->VALID_IMAGEFILENAME);
		$image->insert($this->getPDO());

		//grab the data from mySQL and enforce the fields match our expectations
		$results = Image::getAllImages($this->getPDO());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\CrumbTrail\\Image", $results);

		//grab the result from the array and validate it
		$pdoImage = $results[0];
		$this->assertEquals($pdoImage->getImageCompanyId(), $this->company->getCompanyId());
		$this->assertEquals($pdoImage->getImageFileType(), $this->VALID_IMAGEFILETYPE);
		$this->assertEquals($pdoImage->getImageFileName(), $this->VALID_IMAGEFILENAME);
	}

}

//This is the end of the Image unit test, trial one//
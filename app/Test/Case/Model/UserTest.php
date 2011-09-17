<?php
/* User Test cases generated on: 2011-09-17 04:06:17 : 1316232377*/
App::uses('User', 'Model');

/**
 * User Test Case
 *
 */
class UserTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('app.user', 'app.group', 'app.instructor', 'app.course', 'app.student');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->User = ClassRegistry::init('User');
	}
	
	
/**
 * Test calculate method
 *
 * @return boolean
 */
	public function testCalculate() {
		$this->assertEquals($this->User->calculate(3, 5), 8);

	}	
	

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->User);
		ClassRegistry::flush();

		parent::tearDown();
	}

}

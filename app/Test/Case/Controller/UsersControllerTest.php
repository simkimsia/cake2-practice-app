<?php
/* UsersController Test cases generated on: 2011-09-19 07:02:33 : 1316415753*/
App::uses('UsersController', 'Controller');

/**
 * TestUsersController 
 *
 */
class TestUsersController extends UsersController {
/**
 * Auto render
 *
 * @var boolean
 */
	public $autoRender = false;

/**
 * Redirect action
 *
 * @param mixed $url
 * @param mixed $status
 * @param boolean $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}
}

/**
 * UsersController Test Case
 *
 */
class UsersControllerTestCase extends CakeTestCase {
/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->UsersController = new TestUsersController();
		$this->Users->constructClasses();
	}
	
/**
 * test index action of Users controller
 *
 * @return void
 */	
	public function testIndex() {
        $result = $this->testAction('/users/index');
        debug($result);
    }

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UsersController);
		ClassRegistry::flush();

		parent::tearDown();
	}

}

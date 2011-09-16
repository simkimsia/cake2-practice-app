<?php
/**
 * Workshops Controller
 *
 * @property Workshop $Workshop
 */
class WorkshopsController extends AppController {


	/**
	 * method that is executed before calling action methods
	 * 
	 * @return void
	 */
		public function beforeFilter() {
			parent::beforeFilter();
			// allowing following actions to be public
			$this->Auth->allow('index', 'add');
		}

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Workshop->recursive = 0;
		$this->set('workshops', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Workshop->id = $id;
		if (!$this->Workshop->exists()) {
			throw new NotFoundException(__('Invalid workshop'));
		}
		$this->set('workshop', $this->Workshop->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Workshop->create();
			if ($this->Workshop->save($this->request->data)) {
				$this->Session->setFlash(__('The workshop has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workshop could not be saved. Please, try again.'));
			}
		}
		$users = $this->Workshop->User->find('list');
		$this->set(compact('users'));
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Workshop->id = $id;
		if (!$this->Workshop->exists()) {
			throw new NotFoundException(__('Invalid workshop'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Workshop->save($this->request->data)) {
				$this->Session->setFlash(__('The workshop has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The workshop could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Workshop->read(null, $id);
		}
		$users = $this->Workshop->User->find('list');
		$this->set(compact('users'));
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Workshop->id = $id;
		if (!$this->Workshop->exists()) {
			throw new NotFoundException(__('Invalid workshop'));
		}
		if ($this->Workshop->delete()) {
			$this->Session->setFlash(__('Workshop deleted'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('Workshop was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}

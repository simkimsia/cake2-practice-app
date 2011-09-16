<?php
App::uses('BaseAuthorize', 'Controller/Component/Auth');
/*
$key (string) – The identifier of the configuration file to load.
$config (string) – The alias of the configured reader.
$merge (boolean) – Whether or not the contents of the read file should be merged, or overwrite the existing values.
*/
Configure::load('permissions', 'course_permission');

class AclAuthorize extends BaseAuthorize {


	private $customActionToModelList = array(
		'/Workshops/edit' => 'Workshop'
	);
	
	
/**
 * Returns whether a user is authorized to access an action
 *
 * @param array $user 
 * @param CakeRequest $request 
 * @return boolean
 */
	public function authorize($user, CakeRequest $request) {
		
		if (!empty($user['admin'])) {
			return true;
		}
		//return true;
$userModel = ClassRegistry::init('User');
		$userRole = $user['group_id'];
		
		$currentAction = $this->action($request);
		
		// there are 2 lists of actions
		// first list of actions we just check for roles that are allowed
		// the other list of actions, we check for roles allowed AND database for access to the object
		$rolesAllowedOnActionOnly = Configure::read('AccessToActions.' . $currentAction);
		$rolesAllowedOnObjectOnly = Configure::read('AccessToObjects.' . $currentAction);
		
		$actionIsDeniedToAll 	 = (!$rolesAllowedOnActionOnly && !$rolesAllowedOnObjectOnly);
		$actionAllowedToAllRoles = ($rolesAllowedOnActionOnly === '*' && !$rolesAllowedOnObjectOnly);
		$checkActionsOnly 		 = ($rolesAllowedOnActionOnly && !$rolesAllowedOnObjectOnly);
		$checkObjectsOnly 		 = (!$rolesAllowedOnActionOnly && $rolesAllowedOnObjectOnly);
		
		// action does NOT appear in either list
		if ($actionIsDeniedToAll ) {
			$userModel->log('deniedToall');
			return false;
		}
		
		// action appears in first list and the roles allowed is *
		if ($actionAllowedToAllRoles) {
						$userModel->log('allow to all');
			return true;
		}
		
		// we check if current user has a role that is allowed for this action
		if ($checkActionsOnly) {
			$userModel->log('checkActionsOnly');
			return $this->checkUserHasAccessToActionByRoles($userRole, $rolesAllowedOnActionOnly);
		}

		// we check if current user has a role that is allowed for this action
		// AND database for access to object
		if ($checkObjectsOnly) {
			$userModel->log('checkobjects only');
			$accessToAction = $this->checkUserHasAccessToActionByRoles($userRole, $rolesAllowedOnObjectOnly);
			
			// stop checking any further if user does NOT even have access to action
			if (!$accessToAction) {
				$userModel->log('no access to action when check objects');
				return false;
			}
			$userModel->log('check objects allow access to object');			
			return $this->checkUserHasAccessToObject($currentAction, $user['id'], $request->params['pass']);
		}			
		
		$userModel->log('denied for default');
		return false;
		
	}
	
	/**
	 * Returns whether a user is authorized to access an action based on the roles in database
	 *
	 * @param integer or string $userRole
	 * @param string $roles The roles stated in app/Config/permissions.ini for this action 
	 * @return boolean
	 */	
	private function checkUserHasAccessToActionByRoles($userRole, $rolesAllowedForAction) {
		
		if ($rolesAllowedForAction === '*') {
			return true;
		}
		$groupModel = ClassRegistry::init('Group');
		
		$groupModel->log($rolesAllowedForAction);
				
		$roles = explode(',', $rolesAllowedForAction);
		foreach($roles as $key=>$role) {
			$roles[$key] = Inflector::pluralize($role);
		}
$groupModel->log($roles);
		
		$results = $groupModel->find('all', array(
			'conditions' => array(
				'Group.name' => $roles
			),
			'fields' => array(
				'Group.id', 'Group.name'
			)
		));
		
		$groupModel->log($results);
		$allGroupIdsAllowed = Set::extract('/Group/id', $results);
		$groupModel->log($allGroupIdsAllowed);		
		return in_array($userRole, $allGroupIdsAllowed);
		
	}
	
	private function getModelForCheckingAccess($currentAction) {
		// if the action has a specific model that we need to use for checking access,
		// then we use it as stated in the list
		if (array_key_exists($currentAction, $this->customActionToModelList)) {
			return ClassRegistry::init($this->customActionToModelList[$currentAction]);
		}
		
		// otherwise, we assume that the first part of the action is the model to use
		$actionInArray = explode('/', $currentAction);
		
		if (!empty($actionInArray[0])) {
			
			$modelName = Inflector::camelize($actionInArray[0]);			
			//$modelName = 'Workshop';			
			return ClassRegistry::init($modelName);
		}

		return false;
	}
	
	private function formConditionsArrayToCheckAccess($model, $userId, $objectId) {
		
		// assume User is the parent model
		// assume User hasMany Object
		foreach($model->belongsTo as $association) {
			if ($association['className'] === 'User') {
				
				return array($model->name . '.' . $association['foreignKey'] => $userId, 
						     $model->name . '.id' 							 => $objectId);
			}
		}
	}
	
	private function checkUserHasAccessToObject($currentAction, $userId, $requestParams) {
		
		$objectId = isset($requestParams[0]) ? $requestParams[0] : 0;
		
		$model = $this->getModelForCheckingAccess($currentAction);

		if ($model) {
			$conditions = $this->formConditionsArrayToCheckAccess($model, $userId, $objectId);
			
			$exists = $model->find('count', array(
				'conditions' => $conditions
			));
			
			if ($exists) {		
				return true;
			}
		}
		
		return false;
	}
}
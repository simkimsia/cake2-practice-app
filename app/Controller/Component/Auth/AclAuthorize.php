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
			return false;
		}
		
		// action appears in first list and the roles allowed is *
		if ($actionAllowedToAllRoles) {
			return true;
		}
		
		// we check if current user has a role that is allowed for this action
		if ($checkActionsOnly) {
			return $this->checkUserHasAccessToActionByRoles($userRole, $rolesAllowedOnActionOnly);
		}

		// we check if current user has a role that is allowed for this action
		// AND database for access to object
		if ($checkObjectsOnly) {
			$accessToAction = $this->checkUserHasAccessToActionByRoles($userRole, $rolesAllowedOnObjectOnly);
			
			// stop checking any further if user does NOT even have access to action
			if (!$accessToAction) {
				return false;
			}			
			
			return $this->checkUserHasAccessToObject($currentAction, $user['id'], $request->params['pass']);
		}			
		
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

		$roles = explode(',', $rolesAllowedForAction);
		foreach($roles as $key=>$role) {
			$roles[$key] = Inflector::pluralize($role);
		}
		
		$results = $groupModel->find('all', array(
			'conditions' => array(
				'Group.name' => $roles
			),
			'fields' => array(
				'Group.id', 'Group.name'
			)
		));
		
		$allGroupIdsAllowed = Set::extract('/Group/id', $results);

		return in_array($userRole, $allGroupIdsAllowed);
		
	}
	
	
	/**
	 * 
	 * Gets the model class associated for checking access. 
	 * If User hasMany Object, then the model returned is the Object. If User hasAndBelongsToMany Object, 
	 * the model returned is the relationModel
	 *
	 * @param string $currentAction
	 * @return array An array containing the model class and the foreign keys
	**/
	private function getModelAndKeysForCheckingAccess($currentAction) {
		// if the action has a specific model that we need to use for checking access,
		// then we use it as stated in the list
				
		if (array_key_exists($currentAction, $this->customActionToModelList)) {

			$customActionToModel = $this->customActionToModelList[$currentAction];
			if (is_string($customActionToModel)) {
				$modelName = $this->customActionToModelList[$currentAction];
			} 
			if (is_array($customActionToModel) && !empty($customActionToModel['model'])) {

				$modelName = $this->customActionToModelList[$currentAction]['model'];
			}

			$model = ClassRegistry::init($modelName);
		
			$keys = isset($customActionToModel['keys']) && (is_array($customActionToModel)) ? $customActionToModel['keys'] : null;
			
			return array('model' => $model, 'keys' => $keys);
		}
		
		
		// otherwise, we assume that the first part of the action is the model to use
		$actionInArray = explode('/', $currentAction);
		$actionInArray = array_values(array_filter($actionInArray));
		
		
		if (!empty($actionInArray[0])) {
		    
			$modelName = Inflector::singularize(Inflector::camelize($actionInArray[0]));
		
			$possibleChildModel = ClassRegistry::init($modelName);
			$parentModel 		= ClassRegistry::init('User');
			
			// assume that if User hasMany possibleChildModel then it is a direct relationship
			if ($this->checkParentChildRelation($parentModel, $possibleChildModel)) {
				return array('model'=>$possibleChildModel, 'keys'=>null);
			} else {
				// now we assume it's a many-to-many and return the relationModel involved
				return $this->getRelationModelAndKeys($parentModel, $possibleChildModel);
				
			}
		}

		return false;
	}
	
	
	/**
	 * 
	 * Gets the relationModel between the 2 models. The assumption is that
	 * both models hasMany relationModel. RelationModel belongsTo both models
	 *
	 * @param Model $userModel 
	 * @param Model $objectModel 
	 * @return Model
	**/
	private function getRelationModelAndKeys($userModel, $objectModel) {
				
		$userModelHasManyModelList 	 = array();
		$objectModelHasManyModelList = array();
		
		// loop through all the hasMany of the userModel
		foreach($userModel->hasMany as $alias=>$association) {
			$className = $association['className'];
			$userModelHasManyModelList[$alias] = $className;
		}
		
		// loop through all the hasMany of the objectModel
		foreach($userModel->hasMany as $alias=>$association) {
			$className = $association['className'];
			$objectModelHasManyModelList[$alias] = $className;
		}
		
		$relationModelList = array_intersect($userModelHasManyModelList, $objectModelHasManyModelList);
		
		$keys = array();
		if (!empty($relationModelList)) {
			
			$allPossibleRelationClass = array_values($relationModelList);

			$parentAlias = array_search($allPossibleRelationClass[0], $userModelHasManyModelList);
			$keys[] = $userModel->hasMany[$parentAlias]['foreignKey'];
			
			$childAlias = array_search($allPossibleRelationClass[0], $objectModelHasManyModelList);
			$keys[] = $objectModel->hasMany[$childAlias]['foreignKey'];
			
			$model = ClassRegistry::init($allPossibleRelationClass[0]);
			
			return array('model' => $model, 'keys' => $keys);
		}
		
		return false;
	}

	/**
	 * 
	 * returns true if parentModel hasMany childModel
	 *
	 * @param Model $userModel 
	 * @param Model $objectModel 
	 * @return Model
	**/	
	private function checkParentChildRelation ($parentModel, $childModel) {
		foreach($parentModel->hasMany as $association) {
			if ($association['className'] == $childModel->name) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 
	 * customize the conditions for checking access to the records
	 *
	 * @param array $model The model used for checking access. If many-to-many, this is the relationModel.
	 * @param integer or string $userId 
	 * @param integer or string $objectId
	 * @param array $keys An array of field names for foreign keys. First one is for userId
	 * @return array
	**/
	private function formConditionsArrayToCheckAccess($model, $userId, $objectId, $keys = null) {

		if ($keys == null) {
			// direct parent child relation
			// assume User is the parent model
			// assume User hasMany Object

			foreach($model->belongsTo as $association) {
				if ($association['className'] === 'User') {

					return array($model->name . '.' . $association['foreignKey'] => $userId, 
							     $model->name . '.id' 							 => $objectId);
				}
			}
			
		} else {
			return array(
						$model->name . '.' . $keys[0] => $userId,
						$model->name . '.' . $keys[1] => $objectId,	
					);
		}
		
	}
	
	
	/**
	 * 
	 * Check if current User has access to the Object associated with current request
	 *
	 * @param string $currentAction
	 * @param integer or string $userId 
	 * @param array $requestParams Current request->params->['pass']
	 * @return boolean
	**/	
	private function checkUserHasAccessToObject($currentAction, $userId, $requestParams) {
		
		$objectId = isset($requestParams[0]) ? $requestParams[0] : 0;
		
		$modelKeysArray = $this->getModelAndKeysForCheckingAccess($currentAction);
		
		$model = $modelKeysArray['model'];

		if ($modelKeysArray) {
			$conditions = $this->formConditionsArrayToCheckAccess($model, $userId, $objectId, $modelKeysArray['keys']);
			
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
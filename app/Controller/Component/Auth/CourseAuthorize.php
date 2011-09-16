<?php
App::uses('BaseAuthorize', 'Controller/Component/Auth');
/*
$key (string) – The identifier of the configuration file to load.
$config (string) – The alias of the configured reader.
$merge (boolean) – Whether or not the contents of the read file should be merged, or overwrite the existing values.
*/
Configure::load('permissions', 'course_permission');

class CourseAuthorize extends BaseAuthorize {

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
		$role = Configure::read('Permissions.' . $this->action($request));
		
		if (!$role) {
			return false;
		}
		if ($role === '*') {
			return true;
		}
					
		$models = explode(',', Inflector::camelize($role));
		
		if (isset($request->params['pass'][0])) {
			$courseId = $request->params['pass'][0];
			foreach ($models as $model) {
				$exists = ClassRegistry::init($model)->find('count', array(
					'conditions' => array(
						'user_id' => $user['id'],
						'course_id' => $courseId,
					)
				));

				if ($exists) {		
					return true;
				}
			}
		}
		
				
		return false;
		
	}
}
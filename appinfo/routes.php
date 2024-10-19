<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Sendent\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'routes' => [
		/* V1.0 API's */
		[
			'name' => 'setting_key_api#preflighted_cors',
			'url' => '/api/1.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'setting_key_api#index', 'url' => '/api/1.0/settingkey/index', 'verb' => 'GET'],
		['name' => 'setting_key_api#showByKey', 'url' => '/api/1.0/settingkey/showByKey/{key}', 'verb' => 'GET'],
		['name' => 'setting_key_api#showByTemplateId', 'url' => '/api/1.0/settingkey/showByTemplateId/{templateid}', 'verb' => 'GET'],
		['name' => 'setting_key_api#showByTemplateKey', 'url' => '/api/1.0/settingkey/showByTemplateKey/{templatekey}', 'verb' => 'GET'],
		['name' => 'setting_key_api#showTheming', 'url' => '/api/1.0/settingkey/theming', 'verb' => 'GET'],
		['name' => 'setting_key_api#create', 'url' => '/api/1.0/settingkey', 'verb' => 'POST'],
		['name' => 'setting_key_api#update', 'url' => '/api/1.0/settingkey/{id}', 'verb' => 'PUT'],
		['name' => 'setting_key_api#destroy', 'url' => '/api/1.0/settingkey/{id}', 'verb' => 'DELETE'],
		[
			'name' => 'setting_group_value_api#preflighted_cors',
			'url' => '/api/1.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'setting_group_value_api#index', 'url' => '/api/1.0/settinggroupvalue/index', 'verb' => 'GET'],
		['name' => 'setting_group_value_api#getForDefaultGroup', 'url' => '/api/1.0/settinggroupvalue/getForDefaultGroup', 'verb' => 'GET'],
		['name' => 'setting_group_value_api#getForNCGroup', 'url' => '/api/1.0/settinggroupvalue/getForNCGroup/{ncgroup}', 'verb' => 'GET'],
		['name' => 'setting_group_value_api#show', 'url' => '/api/1.0/settinggroupvalue/{id}', 'verb' => 'GET'],
		['name' => 'setting_group_value_api#showBySettingKeyId', 'url' => '/api/1.0/settinggroupvalue/showbysettingkeyid/{settingkeyid}', 'verb' => 'GET'],
		['name' => 'setting_group_value_api#findByGroupId', 'url' => '/api/1.0/settinggroupvalue/findByGroupId/{groupid}', 'verb' => 'GET'],
		['name' => 'setting_group_value_api#create', 'url' => '/api/1.0/settinggroupvalue', 'verb' => 'POST'],
		['name' => 'setting_group_value_api#update', 'url' => '/api/1.0/settinggroupvalue/{id}', 'verb' => 'PUT'],
		['name' => 'setting_group_value_api#destroy', 'url' => '/api/1.0/settinggroupvalue/{id}', 'verb' => 'DELETE'],
		[
			'name' => 'setting_template_api#preflighted_cors',
			'url' => '/api/1.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'setting_template_api#index', 'url' => '/api/1.0/settingtemplate/index', 'verb' => 'GET'],
		['name' => 'setting_template_api#show', 'url' => '/api/1.0/settingtemplate/{id}', 'verb' => 'GET'],
		['name' => 'setting_template_api#showByTemplateKey', 'url' => '/api/1.0/settingtemplate/bytemplatekey/{templateKey}', 'verb' => 'GET'],
		['name' => 'setting_template_api#create', 'url' => '/api/1.0/settingtemplate', 'verb' => 'POST'],
		['name' => 'setting_template_api#update', 'url' => '/api/1.0/settingtemplate/{id}', 'verb' => 'PUT'],
		['name' => 'setting_template_api#destroy', 'url' => '/api/1.0/settingtemplate/{id}', 'verb' => 'DELETE'],

		[
			'name' => 'license_api#preflighted_cors',
			'url' => '/api/1.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'license_api#show', 'url' => '/api/1.0/licensestatus', 'verb' => 'GET'],
		['name' => 'license_api#showInternal', 'url' => '/api/1.0/licensestatusinternal', 'verb' => 'GET'],
		['name' => 'license_api#showForNCGroup', 'url' => '/api/1.0/licensestatusForNCGroup', 'verb' => 'GET'],
		['name' => 'license_api#showForNCGroupInternal', 'url' => '/api/1.0/licensestatusForNCGroupinternal', 'verb' => 'GET'],
		['name' => 'license_api#delete', 'url' => '/api/1.0/license', 'verb' => 'DELETE'],
		['name' => 'license_api#create', 'url' => '/api/1.0/license', 'verb' => 'POST'],
		['name' => 'license_api#validate', 'url' => '/api/1.0/licensevalidation', 'verb' => 'GET'],
		['name' => 'license_api#renew', 'url' => '/api/1.0/licenserenew', 'verb' => 'GET'],
		['name' => 'license_api#report', 'url' => '/api/1.0/licensereport', 'verb' => 'GET'],

		[
			'name' => 'connecteduser_api#preflighted_cors',
			'url' => '/api/1.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'connecteduser_api#ping', 'url' => '/api/1.0/ping', 'verb' => 'GET'],

		[
			'name' => 'status_api#preflighted_cors',
			'url' => '/api/1.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'status_api#index', 'url' => '/api/1.0/status', 'verb' => 'GET'],
		['name' => 'tag#show', 'url' => '/api/1.0/tag/{id}', 'verb' => 'GET'],
		['name' => 'tag#create', 'url' => '/api/1.0/tag', 'verb' => 'POST'],
		['name' => 'termsagreement_api#agree', 'url' => '/api/1.0/termsagreement/agree/{version}', 'verb' => 'GET'],
		['name' => 'termsagreement_api#isAgreed', 'url' => '/api/1.0/termsagreement/isagreed/{version}', 'verb' => 'GET'],

		[
			'name' => 'setting_groups_management#preflighted_cors',
			'url' => '/api/2.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
		['name' => 'setting_groups_management#update', 'url' => '/api/2.0/groups/update', 'verb' => 'POST'],
		[
			'name' => 'setting_group_value_v2_api#preflighted_cors',
			'url' => '/api/2.0/{path}',
			'verb' => 'OPTIONS',
			'requirements' => ['path' => '.+']
		],
	]
];
	?>


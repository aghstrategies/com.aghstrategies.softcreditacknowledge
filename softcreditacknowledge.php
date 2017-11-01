<?php

require_once 'softcreditacknowledge.civix.php';

function softcreditacknowledge_civicrm_buildForm( $formName, &$form ){
	if ($formName == "CRM_Contribute_Form_ContributionPage_Settings"){
    if( !array_key_exists('acknowledged_profile', $form->_elementIndex) ){
      // Get default profile
      $ufJoinDAO = new CRM_Core_DAO_UFJoin();
      $ufJoinDAO->module = 'acknowledged';
      $ufJoinDAO->entity_id = $form->getVar('_id');
      if ($ufJoinDAO->find(TRUE)) {
        $defaults['acknowledged_profile'] = $ufJoinDAO->uf_group_id;
        $defaults['acknowledged_is_active'] = $ufJoinDAO->is_active;
        $module_data = $ufJoinDAO->module_data;
        $jsonData = json_decode($module_data);
        $jsonDefaults = $jsonData->acknowledge->default;
        foreach (array('acknowledged_block_title', 'acknowledged_block_text', 'use_for_memory', 'use_for_honor') as $name) {
          $defaults[$name] = $jsonDefaults->$name;
        }

      }
      // Add the field element in the form
      $allowCoreTypes = array_merge(array('Contact', 'Individual', 'Organization', 'Household'), CRM_Contact_BAO_ContactType::subTypes('Individual'));
      $allowSubTypes = array();
      $entities = "";
      $form->addProfileSelector('acknowledged_profile', ts('Acknowledged Profile'), $allowCoreTypes, $allowSubTypes, $entities);
      $form->add('text', 'acknowledged_block_title', ts('Acknowledged Section Title'));
      $form->add('textarea', 'acknowledged_block_text', ts('Acknowledged Introductory Message'), array('rows' => 2, 'cols' => 50));
      $form->add('checkbox', 'acknowledged_is_active', ts('Enabled Profile for Acknowledged'))->setChecked( $defaults['acknowledge_is_active']);
      $form->add('checkbox', 'use_for_honor', ts('Use For In Honor Of'));
      $form->add('checkbox', 'use_for_memory', ts('Use For In Memory Of'));
      $form->setDefaults($defaults);
      // dynamically insert a template block in the page
     $templatePath = realpath(dirname(__FILE__)."/templates");
      CRM_Core_Region::instance('form-body')->add(array(
        'template' => "{$templatePath}/settings.tpl"
      ));
      return;
    }
	}

  if ($formName == "CRM_Contribute_Form_Contribution_Main"){
  	$ufJoinDAO = new CRM_Core_DAO_UFJoin();
    $ufJoinDAO->module = 'acknowledged';
    $ufJoinDAO->entity_id = $form->getVar('_id');
    if ($ufJoinDAO->find(TRUE) && $ufJoinDAO->is_active > 0) {
      $module_data = $ufJoinDAO->module_data;
      $jsonData = json_decode($module_data);
      $jsonDefaults = $jsonData->acknowledge->default;
      if ($jsonData) {
        foreach (array('acknowledged_block_title', 'acknowledged_block_text') as $name => $field) {
          if (count($form->_submitValues) && empty($form->_submitValues['soft_credit_type_id']) && !empty($field['is_required'])) {
            $field['is_required'] = false;
          }
          if(empty($form->_submitValues)){
            $form->assign($field, $jsonDefaults->$field);
          }
        }
        $softArray = array();
        foreach ($jsonDefaults as $key => $value){
        	if ($key == "use_for_memory" && !empty($value)){
        		$softArray[] = 2;
        	}
        	if ($key == "use_for_honor" && !empty($value)){
        		$softArray[] = 1;
        	}
        	CRM_Core_Resources::singleton()->addSetting(array('softcredits' => $softArray));
        }
      }

	    $profileId = $ufJoinDAO->uf_group_id;
	    $acknowledgeProfileFields = CRM_Core_BAO_UFGroup::getFields($profileId, FALSE, NULL,
	        NULL, NULL,
	        FALSE, NULL,
	        TRUE, NULL,
	        CRM_Core_Permission::CREATE
	      );
	    $form->addElement('hidden', 'acknowledge_profile_id', $profileId);
	    $form->assign('acknowledgeProfileFields', $acknowledgeProfileFields);
	    $prefix = "acknowledge";
	    foreach ($acknowledgeProfileFields as $name => $field) {
	    	// If soft credit type is not chosen then make omit requiredness from honoree profile fields
        if (count($form->_submitValues) && empty($form->_submitValues['acknowledge_active']) && !empty($field['is_required'])) {
          $field['is_required'] = FALSE;
        }
	      CRM_Core_BAO_UFGroup::buildProfile($form, $field, CRM_Profile_Form::MODE_CREATE, NULL, FALSE, FALSE, NULL, $prefix);
	    }
	    $templatePath = realpath(dirname(__FILE__)."/templates");
	    $form->add('checkbox', 'acknowledge_active', ts('I would like the family or honoree informed of this donation by email'));
	    
	    //add fields for notifying by mail
	    $form->add('checkbox', 'acknowledge_mail', ts('Yes, Please send a card acknowledging this donation to:'));
      $mailProfileFields = CRM_Core_BAO_UFGroup::getFields(20, FALSE, NULL,
	        NULL, NULL,
	        FALSE, NULL,
	        TRUE, NULL,
	        CRM_Core_Permission::CREATE
	      );
	    foreach ($mailProfileFields as $name => $field) {
	    	CRM_Core_BAO_UFGroup::buildProfile($form, $field, CRM_Profile_Form::MODE_CREATE, NULL, FALSE, FALSE, NULL, 'ack');
	    }
	    $form->addElement('hidden', 'mail_profile_id', 20);
	    $form->assign('mailProfileFields', $mailProfileFields);
	    CRM_Core_Region::instance('form-body')->add(array(
	      'template' => "{$templatePath}/main.tpl"
	    ));

    }
  }
}

function softcreditacknowledge_civicrm_postProcess( $formName, &$form ){
	if( $formName == "CRM_Contribute_Form_ContributionPage_Settings" ){
		$values = $form->_submitValues;
    if($values['acknowledged_profile']){
      $params = array(
        'acknowledge' => array(
          'default' => array(
              "acknowledged_block_title" => $values['acknowledged_block_title'],
              "acknowledged_block_text" => $values['acknowledged_block_text'],
              "use_for_memory" => $values['use_for_memory'],
              "use_for_honor"  => $values['use_for_honor'],
            ),
          ),
        );
      if(array_key_exists('acknowledged_is_active', $values)){
      	$ufParams['is_active'] = 1;
      } else {
      	$ufParams['is_active'] = 0;
      }
      $id = $form->getVar('_id');
      $ackJsonEncode = json_encode($params);
      $ufParams['entity_table'] = 'civicrm_contribution_page';
      $ufParams['entity_id'] = $id;
      $ufParams['module'] = 'acknowledged';
      $ufParams['uf_group_id'] = $values['acknowledged_profile'];
      $ufParams['module_data'] = $ackJsonEncode;
      CRM_Core_BAO_UFJoin::deleteAll($ufParams);
      CRM_Core_BAO_UFJoin::create($ufParams);
    }
	}

  if ($formName == "CRM_Contribute_Form_Contribution_Confirm"){
    $params = $form->_params;
    if (!empty($form->_honor_block_is_active) && !empty($params['soft_credit_type_id']) & !empty($params['acknowledge']) ) {
      $honorId = null;
      //check if there is any duplicate contact
      if (!empty($params['acknowledge']['first_name'])) {
        $profileContactType = CRM_Core_BAO_UFGroup::getContactType($params['acknowledge_profile_id']);
        $dedupeParams = CRM_Dedupe_Finder::formatParams($params['acknowledge'], $profileContactType);
        $dedupeParams['check_permission'] = FALSE;
        $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $profileContactType);
        if(count($ids)) {
          $acknowledgeId = CRM_Utils_Array::value(0, $ids);
        }
  
        $acknowledgeId = CRM_Contact_BAO_Contact::createProfileContact(
          $params['acknowledge'], CRM_Core_DAO::$_nullArray,
          $acknowledgeId, NULL,
          $params['acknowledge_profile_id']
        );
  
        //Create Soft Credit
        $softParams = array();
        $softParams['contribution_id'] = $form->_contributionID;
        $softParams['contact_id'] = $acknowledgeId;
        //Find dynamic way to get correct soft credit type id
        $result = civicrm_api3('OptionValue', 'get', array('sequential' => 1,'name' =>'acknowledged','return' => 'value'));
        $softParams['soft_credit_type_id'] = $result['values'][0]['value'];
        $contribution = new CRM_Contribute_DAO_Contribution();
        $contribution->id = $form->_contributionID;
        $contribution->find();
        while ($contribution->fetch()) {
          $softParams['currency'] = $contribution->currency;
          $softParams['amount'] = '0.00';
        }
        CRM_Contribute_BAO_ContributionSoft::add($softParams);
      }
      
      if (!empty($params['ack']['first_name'])) {
      	$profileContactType = CRM_Core_BAO_UFGroup::getContactType($params['mail_profile_id']);
				$dedupeParams = CRM_Dedupe_Finder::formatParams($params['ack'], $profileContactType);
				$dedupeParams['check_permission'] = FALSE;
				$ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $profileContactType);
				if(count($ids)) {
					$acknowledgeId = CRM_Utils_Array::value(0, $ids);
				}
	
				$ackMailId = CRM_Contact_BAO_Contact::createProfileContact(
					$params['ack'], CRM_Core_DAO::$_nullArray,
					$ackMailId, NULL,
					$params['mail_profile_id']
				);
	
				//Create Soft Credit
				$softParams = array();
				$softParams['contribution_id'] = $form->_contributionID;
				$softParams['contact_id'] = $ackMailId;
				//Find dynamic way to get correct soft credit type id
				$result = civicrm_api3('OptionValue', 'get', array('sequential' => 1,'name' =>'acknowledged','return' => 'value'));
				$softParams['soft_credit_type_id'] = $result['values'][0]['value'];
				$contribution = new CRM_Contribute_DAO_Contribution();
				$contribution->id = $form->_contributionID;
				$contribution->find();
				while ($contribution->fetch()) {
					$softParams['currency'] = $contribution->currency;
					$softParams['amount'] = '0.00';
				}
				CRM_Contribute_BAO_ContributionSoft::add($softParams);
      }
    }
  }
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function softcreditacknowledge_civicrm_config(&$config) {
  _softcreditacknowledge_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function softcreditacknowledge_civicrm_xmlMenu(&$files) {
  _softcreditacknowledge_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function softcreditacknowledge_civicrm_install() {
  // create soft credit type for acknowledged
	$test = civicrm_api3('OptionValue', 'get', array(
    'sequential' => 1,
    'name' => "acknowledged",
  ));
  if($test['count'] == 0){
    $result = civicrm_api3('OptionGroup', 'get', array(
	    'sequential' => 1,
	    'name' =>'soft_credit_type',
	    'api.OptionValue.create' => array(
	        'option_group_id' => '$value.id',
	        'name' => 'acknowledged',
	        'label' => 'Acknowledged',
	        'is_active' => 1,
	        'is_reserved' => 1
	     ),
	  ));
  }
  return _softcreditacknowledge_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function softcreditacknowledge_civicrm_uninstall() {
  return _softcreditacknowledge_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function softcreditacknowledge_civicrm_enable() {
  return _softcreditacknowledge_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function softcreditacknowledge_civicrm_disable() {
  return _softcreditacknowledge_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function softcreditacknowledge_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _softcreditacknowledge_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function softcreditacknowledge_civicrm_managed(&$entities) {
  return _softcreditacknowledge_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function softcreditacknowledge_civicrm_caseTypes(&$caseTypes) {
  _softcreditacknowledge_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function softcreditacknowledge_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _softcreditacknowledge_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

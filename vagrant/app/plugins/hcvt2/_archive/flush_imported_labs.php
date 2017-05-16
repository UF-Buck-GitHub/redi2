<?php
/**
 * Created by HCV-TARGET for HCV-TARGET.
 * User: kbergqui
 * Date: 10-26-2013
 */
/**
 * TESTING
 */
$debug = true;
$subjects = ''; // = ALL
$timer = array();
$timer['start'] = microtime(true);
/**
 * includes
 * adjust dirname depth as needed
 */
$base_path = dirname(dirname(dirname(__FILE__)));
require_once $base_path . "/redcap_connect.php";
require_once $base_path . '/plugins/includes/functions.php';
require_once APP_PATH_DOCROOT . '/Config/init_project.php';
require_once APP_PATH_DOCROOT . '/ProjectGeneral/header.php';
/**
 * restricted use
 */
$allowed_pids = array('26');
REDCap::allowProjects($allowed_pids);
/**
 * find imported fields in non-imported forms
 */
function is_imported($var) {
	if (strpos($var, '_im_') !== false) {
		return true;
	} else {
		return false;
	}
}
/**
 * labs field names
 */
$change_message = 'Purging imported labs, standardizations and related derivations prior to REDI refresh';
$forms = array('cbc_imported', 'cbc_im_standard', 'chemistry_imported', 'chemistry_im_standard', 'inr_imported', 'hcv_rna_imported', 'hcv_rna_im_standard', 'derived_values_baseline', 'derived_values');
//$forms = array('derived_values_baseline', 'derived_values');
foreach ($forms AS $form) {
	$fields = REDCap::getFieldNames($form);
	if (in_array($form, array('derived_values_baseline', 'derived_values'))) {
		$fields = array_filter($fields, "is_imported");
	}
	if ($debug) {
		show_var($fields, $form);
	}
	$data = REDCap::getData('array', $subjects, $fields);
	foreach ($data AS $subject_id => $subject) {
		foreach ($subject AS $event_id => $event) {
			foreach ($event AS $field => $value) {
				if ($value != '') {
					update_field_compare($subject_id, $project_id, $event_id, '', $value, $field, $debug, $change_message);
				}
			}
		}
	}
}
$timer['main_end'] = microtime(true);
$init_time = benchmark_timing($timer);
echo $init_time;
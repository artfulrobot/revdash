<?php
use CRM_Revdash_ExtensionUtil as E;

class CRM_Revdash_Page_RevenueDashboard extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    // CRM_Utils_System::setTitle(E::ts('Revenue Dashboard'));

    $stats = civicrm_api3('Contribution', 'getrevdashstats', [])['values'];
    $this->assign('stats', json_encode($stats));
    $this->assign('scriptUrl', E::url('js/revenuedashboard.js'));
    parent::run();
  }

}

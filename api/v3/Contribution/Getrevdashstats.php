<?php
use CRM_Revdash_ExtensionUtil as E;

/**
 * Contribution.GetRevdashstats API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_contribution_GetRevdashstats_spec(&$spec) {
  $spec['months']   = [
    'description' => 'How many months back to go (default 48)',
    'api.default' => 48,
  ];

  $spec['nocache']   = [
    'description' => 'Recalculate (otherwise results may be cached till the end of the day)',
    'api.default' => 0,
    'type' => 'boolean',
  ];
}

/**
 * Contribution.GetRevdashstats API
 *
 * @param array $params
 * - nocache If set to 1 then the cache is not used. Normally the results are
 *           cached if they are for full calendar months.
 * - months  Default: 48. Months to go back to generate stats for.
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_contribution_GetRevdashstats($params) {

  $result = [];
  $x = new CRM_Revdash_Stats(); // xxx include
  if ($params['list'] ?? NULL) {
    $sx = new Statx();
    $result = $sx->listStats();
    return civicrm_api3_create_success($result, $params, 'Contribution', 'GetRevdashstats');
  }
  $t = microtime(TRUE);

  $today = date('Y-m-d');

  // Get first of this month, a year ago
  $months = (int) $params['months'];
  // $params['nocache'] = 1;
  $given = new DateTimeImmutable("today - $months month");
  $startOfMonth = $given->modify('first day of');
  $endOfMonth = $startOfMonth->modify('+1 month - 1 second');
  $now = new DateTimeImmutable('today');

  $months = [];
  while ($startOfMonth < $now) {
    $months[] = [$startOfMonth->format('c'), $endOfMonth->format('c')];
    $startOfMonth = $startOfMonth->modify('+1 month');
    $endOfMonth = $startOfMonth->modify('+1 month -1 second');
  }

  $cache = CRM_Utils_Cache::create(['type' => ['SqlGroup'], 'name' => 'revenuedashboard']);
  //$value = $cache->get($cache_key, 'default'); // Will return default if cached value expired.
  //$cache->set($cache_key, $data, 60); // Keep value for 60s

  foreach ($months as $month) {
    // Append either 'incomplete' or 'full' to the month specification.
    $month[] = ($month[1] > $today) ? 'incomplete' : 'full';

    $monthStats = NULL;
    $loadedFromCache = FALSE;
    $cacheKey = "$month[0]-$month[1]";
    if ($month[2] === 'full') {
      if (empty($params['nocache'])) {
        $monthStats = $cache->get($cacheKey, NULL);
        $loadedFromCache = (bool) $monthStats;
      }
    }

    if (!$monthStats) {
      $sx = new Statx([
        'startDate' => $month[0],
        'endDate' => $month[1],
      ]);
      $monthStats = $sx->get();
      $monthStats['period'] = $month;

      // Cache the results indefinitely (until explicit cache clear) but no
      // point doing this if we already fetched them from the cache.
      if (!$loadedFromCache) {
        $cache->set($cacheKey, $monthStats);
      }
    }

    $result[] = $monthStats;
  }

  return civicrm_api3_create_success($result, $params, 'Contribution', 'GetRevdashstats');
  //throw new API_Exception(/*errorMessage*/ 'Everyone knows that the magicword is "sesame"', /*errorCode*/ 1234);
}

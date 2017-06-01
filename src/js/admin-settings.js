const tuitionFeesAdmin = ($) => {
  const $cache = $('#ucf_tuition_fees_cache_results');
  const $timeout = $('#ucf_tuition_fees_transient_expiration');
  const $timeoutWrapper = $timeout.closest('tr');

  const toggleTimeout = () => {
    if ($cache.is(':checked')) {
      $timeoutWrapper.show();
    } else {
      $timeoutWrapper.hide();
    }
  };

  if (!$cache.is(':checked')) {
    $timeoutWrapper.hide();
  }

  $cache.on('change', toggleTimeout);

};

if (typeof jQuery !== 'undefined') {
  jQuery(document).ready(($) => {
    tuitionFeesAdmin($);
  });
}

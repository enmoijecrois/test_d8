<?php

namespace Drupal\test_d8\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\test_d8\Helper\FileStorage;

/**
 * Provides a timer block.
 *
 * @Block(
 *   id = "timer_qcm_test_drupal8",
 *   admin_label = @Translation("Timer Qcm Test Drupal 8"),
 * )
 */
class TimerBlock extends BlockBase {

  /**
  * {@inheritdoc}
  */
  public function build() {
    $config = \Drupal::config('test_d8.settings');
    $time = \Drupal::time()->getCurrentTime();
    $timeLeft = $config->get('time_to_complete_test');
    $node = \Drupal::routeMatch()->getParameter('node');
    $uid = \Drupal::currentUser()->id();
    $nid = $node->id();
    $storageName = $uid.'-'.$nid.'.dat';

    # override timeLeft if a test has not ended (page refresh or else)
    //if (isset($_COOKIE['testD8'])){
    if ($storage = FileStorage::get($storageName)){
      //$cookie = unserialize($_COOKIE['testD8']);
      //$timeLeft = $cookie['qcm_timer'];
      $timeLeft = $storage['qcm_timer'];
    }

    $build = [];
    $build['#theme'] = 'timer_qcm_test_drupal8';
    $build['#attached']['drupalSettings']['TestD8']['countdown'] = $time + $timeLeft;

    return $build;
  }

}

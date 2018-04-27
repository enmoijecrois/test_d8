<?php

namespace Drupal\test_d8\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Cookie\SetCookie;

/**
 * Provides a chart block.
 *
 * @Block(
 *   id = "chart_tests_drupal8",
 *   admin_label = @Translation("Chart Tests Drupal 8"),
 * )
 */
class ChartBlock extends BlockBase {

  /**
  * {@inheritdoc}
  */
  public function build() {
    $themes = $this->getThemes();

    $config = \Drupal::config('test_d8.settings');
    $numberOfQuestions = $config->get('number_of_questions');

    $build['#theme'] = 'chart_tests_drupal8';

    $jsData = [];
    $isThereAnyTest = false;
    $chartColors = ['#008020', '#907B00', '#AC0042', '#5A0089'];
    $i = 0;
    foreach ($themes as $themeId => $themeName){
      $scores = $this->getScoresByTheme($themeId);

      if (!empty($scores)){
        $isThereAnyTest = true;

        $dataPoints = [];
        foreach ($scores as $value){
          # conversion du score en pourcentage
          $score = $value['score'] / $numberOfQuestions * 100;
          # date (* 1000 pour retourner des microsecondes, comme la méthode js Date.UTC(year, month, day))
          $date = $value['date_test'] * 1000;
          $dataPoints[] = [$date, $score];
        }

        $object = new \stdClass;
        $object->name = $themeName;
        $object->data = $dataPoints;
        $object->color = $chartColors[$i];
        ++$i;

        $jsData[] = $object;
      }
    }

    $build['#attached']['drupalSettings']['TestD8']['chart']['data'] = $jsData;
    $build['#data']['anytest'] = $isThereAnyTest;

    return $build;
  }

  protected function getThemes() {
    $node = \Drupal::entityTypeManager()->getStorage('node');
    $ids = \Drupal::entityQuery('node')->condition('type', 'test')->execute();
    $allThemes = $node->loadMultiple($ids);

    $themes = [];
    foreach($allThemes as $d){
      $themes[$d->id()] = $d->getTitle();
    }
    ksort($themes);
    return $themes;
  }

  # retourne tous les tests (filtrés par thème) de l'utilisateur courant
  protected function getScoresByTheme($themeId){
    $ids = \Drupal::entityQuery('node')
      ->condition('type','score')
      ->condition('uid', $this->getCurrentUserID())
      ->condition('field_score_nid', $themeId)
      ->sort('created', 'ASC')
      ->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
    $result = [];
    foreach ($nodes as $id => $obj){
      $result[] = [
        'score' => $obj->get('field_score_result')->getValue()[0]['value'],
        'date_test' => $obj->get('created')->getValue()[0]['value'],
      ];
    }
    return $result;
  }

  protected function getCurrentUserID() {
    return \Drupal::currentUser()->id();
  }

}

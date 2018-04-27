<?php

namespace Drupal\test_d8\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\node\NodeInterface;
use Drupal\test_d8\Helper\FileStorage;

class TestDrupal8QcmController extends ControllerBase {

  protected $configTestD8;
  protected $request;
  protected $formBuilder;
  protected $formBuilderNS;

	public function __construct(Request $request, ConfigFactory $configFactory, FormBuilderInterface $formBuilder){
    $this->request = $request->request;
    $this->configTestD8 = $configFactory->getEditable("test_d8.settings");
    $this->formBuilder = $formBuilder;
    $this->formBuilderNS = $this->configTestD8->get('namespace.qcm_form');
	}

	public static function create(ContainerInterface $container){
		return new static(
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('config.factory'),
      $container->get('form_builder')
		);
	}

  # récupération et affichage du formulaire
	public function content(NodeInterface $node = null){
    return ['form' => $this->formBuilder->getForm($this->formBuilderNS, $node)];
	}

  # stockage des réponses utilisateurs
  public function updateCookie() {
    $storageName = $this->request->get('storage_name');
  	$qid = $this->request->get('qid');
  	$answer = $this->request->get('answer');

    //if (isset($_COOKIE['testD8'])){
    if ($storage = FileStorage::get($storageName)){
      //$cookie = unserialize($_COOKIE['testD8']);
      //$cookieQuestions = $cookie['session_questions'];
      $cookieQuestions = $storage['session_questions'];
      foreach ($cookieQuestions as $key => $value){
      	if ($value['id'] == $qid){
      		$cookieQuestions[$key]['answer_user'] = ($value['answer_valid'] == $answer ? true : false);
      		$cookieQuestions[$key]['answer_num'] = $answer;
      		break;
      	}
      }
      //$cookie['session_questions'] = $cookieQuestions;
      $storage['session_questions'] = $cookieQuestions;
      //setcookie('testD8', serialize($cookie), time()+3600*24*365);
      FileStorage::set($storageName, $storage);
    }
    return [];
  }

  # set the remaining time of the current test
  public function updateTimer() {
    $storageName = $this->request->get('storage_name');
		//if (isset($_COOKIE['testD8'])){
    if ($storage = FileStorage::get($storageName)){
      //$cookie = unserialize($_COOKIE['testD8']);
      $timeLeft = \Drupal::request()->request->get('timeLeft');
      //$cookie['qcm_timer'] = $timeLeft;
      $storage['qcm_timer'] = $timeLeft;
      //setcookie('testD8', serialize($cookie), time()+3600);
		  FileStorage::set($storageName, $storage);
		}
		return [];
	}

}

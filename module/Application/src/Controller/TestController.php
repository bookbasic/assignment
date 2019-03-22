<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Http\Client as HttpClient;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

class TestController extends AbstractRestfulController
{

public function calcFunc($num) {
  if ($num <= 1) return 3;
  return $this->calcFunc($num - 1) + ($num-1) * 2;
}
public function calc($num) {
	CacheManager::setDefaultConfig(new ConfigurationOption(['path' => '.',]));
	$InstanceCache = CacheManager::getInstance('files');
	
	$CachedString = $InstanceCache->getItem($num);
	if (!$CachedString->isHit()) {
		$CachedString->set($this->calcFunc($num))->expiresAfter(10);//in seconds, also accepts Datetime
		$InstanceCache->save($CachedString); // Save the cache item just like you do with doctrine and entities
		return $CachedString->get();
	} else {
		return $CachedString->get();
	}
}

public function locationFunc($type) {
	return file_get_contents('https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=13.818235,100.528119&radius=500&types='.$type.'&name=&key=AIzaSyD94SbvugCt-C15Aj10QNgRcC_aNP1_1Gk');
}
public function location($type) {
	CacheManager::setDefaultConfig(new ConfigurationOption(['path' => '.',]));
	$InstanceCache = CacheManager::getInstance('files');
	
	$CachedString = $InstanceCache->getItem($type);
	if (!$CachedString->isHit()) {
		$CachedString->set($this->locationFunc($type))->expiresAfter(10);//in seconds, also accepts Datetime
		$InstanceCache->save($CachedString); // Save the cache item just like you do with doctrine and entities
		return $CachedString->get();
	} else {
		return $CachedString->get();
	}
}

    public function get($id)
    {
        $response = $this->getResponseWithHeader()
                         ->setContent( $this->calc($id) );
        return $response;
    }
     
    public function getList()
    {
        $response = $this->getResponseWithHeader()
                         ->setContent( $this->location('restaurant') );
        return $response;
    }
	
    // configure response
    public function getResponseWithHeader()
    {
        $response = $this->getResponse();
        $response->getHeaders()
                 //make can accessed by *   
                 ->addHeaderLine('Access-Control-Allow-Origin','*')
                 //set allow methods
                 ->addHeaderLine('Access-Control-Allow-Methods','POST PUT DELETE GET');
         
        return $response;
    }
}

<?php
	/**
	 * Crea cache en archivo
	 * */

	require_once('Zend/Cache.php');

	class cacheDB {
		
		public function fOptions(){
			return array('lifetime'=>600,'automatic_serialization'=>true);
		}
		
		public function bOptions(){
			return array('cache_dir'=>'tmpcache/');
		}
		
		
		public function cargarCache($query){
	    	$cacheLayer = Zend_Cache::factory('Core','File',self::fOptions(),self::bOptions());
			$key = md5($query);
			if(($result=$cacheLayer->load($key))===false){
				return false;
			}
			return $result;
		}
		
		public function guardarCache($obj,$query){

			$key=md5($query);
	    	$cacheLayer = Zend_Cache::factory('Core','File',self::fOptions(),self::bOptions());
	    	$cacheLayer->save($obj,$key);
		}
		
		public function limpiarCache(){
	    	$cacheLayer = Zend_Cache::factory('Core','File',self::fOptions(),self::bOptions());
	    	$cacheLayer->clean();
		}
		
	}
	
	if((isset($_GET['limpiar']))&&($_GET['limpiar']==1)){
	 cacheDB::limpiarCache();
	 echo "cache limpiado";
  }
?>
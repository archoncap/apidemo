<?php

class VerifyPlugin extends Yaf_Plugin_Abstract {
	public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {		
		/***检查控制器是否存在***/
		$config	=	Yaf_Registry::get('config');
		if( !file_exists( $config['application']['directory'].'/controllers/' . ucfirst($request->controller) . '.' . $config['application']['ext']) )
			throw new Exception("无访问权限.");
	}
}

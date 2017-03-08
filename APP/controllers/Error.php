<?php
/********************************************
                   _ooOoo_
                  o8888888o
                  88" . "88
                  (| -_- |)
                  O\  =  /O
               ____/`---'\____
             .'  \\|     |//  `.
            /  \\|||  :  |||//  \
           /  _||||| -:- |||||-  \
           |   | \\\  -  /// |   |
           | \_|  ''\---/''  |   |
           \  .-\__  `-`  ___/-. /
         ___`. .'  /--.--\  `. . __
      ."" '<  `.___\_<|>_/___.'  >'"".
     | | :  `- `.;`\ _ /`;.`/ - ` : | |
     \  \ `-.   \_ __\ /__ _/   .-` /  /
======`-.____`-.___\_____/___.-`____.-'======
                   `=---='
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         佛祖保佑       BUG永无
/********************************************/

/**
 *
 *  YAF框架报错类, 错误统统来这里。
 *
 *  默认错误会调用这个Controller 中 ErrorAction
 *
 */
class ErrorController extends Yaf_Controller_Abstract {
    /**
     * [具体错误处理]
     * @param  Exception $exception [description]
     * @return [type]               [description]
     */
    public function errorAction(Exception $exception)
    {
		$result	=	array(
						'code'		=>	0,
						'Message'	=>	$exception->getMessage(),
					);
		echo json_encode($result);
    }
	
}

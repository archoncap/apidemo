<?php

class RpcController extends Yaf_Controller_Abstract {

	public function indexAction(){
		
		$server = new Yar_Server($this);
		$server->handle();
		
	}
	
    /**
     * Add two operands
     * @param interge
     * @return interge
     */
    public function add($a, $b) {
        return $this->_add($a, $b);
    }
 
    /**
     * Sub
     */
    public function sub($a, $b) {
        return $a - $b;
    }
 
    /**
     * Mul
     */
    public function mul($a, $b) {
        return $a * $b;
    }
 
    /**
     * Protected methods will not be exposed
     * @param interge
     * @return interge
     */
    protected function _add($a, $b) {
        return $a + $b;
    }
}
 



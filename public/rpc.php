<?php
class Operator {
 
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
 
$server = new Yar_Server(new Operator());
$server->handle();

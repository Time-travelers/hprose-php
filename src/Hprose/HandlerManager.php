<?php
/**********************************************************\
|                                                          |
|                          hprose                          |
|                                                          |
| Official WebSite: http://www.hprose.com/                 |
|                   http://www.hprose.org/                 |
|                                                          |
\**********************************************************/

/**********************************************************\
 *                                                        *
 * Hprose/HandlerManager.php                              *
 *                                                        *
 * hprose HandlerManager class for php 5.3+               *
 *                                                        *
 * LastModified: Aug 7, 2016                              *
 * Author: Ma Bingyao <andot@hprose.com>                  *
 *                                                        *
\**********************************************************/

namespace Hprose;

use stdClass;
use Closure;

abstract class HandlerManager {
    private $invokeHandlers = array();
    private $beforeFilterHandlers = array();
    private $afterFilterHandlers = array();
    private $defaultInvokeHandler;
    private $defaultBeforeFilterHandler;
    private $defaultAfterFilterHandler;
    protected $invokeHandler;
    protected $beforeFilterHandler;
    protected $afterFilterHandler;
    public function __construct() {
        $self = $this;
        $this->defaultInvokeHandler = function(/*string*/ $name, array &$args, stdClass $context) use ($self) {
            return $self->invokeHandler($name, $args, $context);
        };
        $this->defaultBeforeFilterHandler = function(/*string*/ $request, stdClass $context) use ($self) {
            return $self->beforeFilterHandler($request, $context);
        };
        $this->defaultAfterFilterHandler = function(/*string*/ $request, stdClass $context) use ($self) {
            return $self->afterFilterHandler($request, $context);
        };
        $this->invokeHandler = $this->defaultInvokeHandler;
        $this->beforeFilterHandler = $this->defaultBeforeFilterHandler;
        $this->afterFilterHandler = $this->defaultAfterFilterHandler;
    }
    /*
        This method is a protected method.
        But PHP 5.3 can't call protected method in closure,
        so we comment the protected keyword.
    */
    /*protected*/ abstract function invokeHandler(/*string*/ $name, array &$args, stdClass $context);
    /*
        This method is a protected method.
        But PHP 5.3 can't call protected method in closure,
        so we comment the protected keyword.
    */
    /*protected*/ abstract function beforeFilterHandler(/*string*/ $request, stdClass $context);
    /*
        This method is a protected method.
        But PHP 5.3 can't call protected method in closure,
        so we comment the protected keyword.
    */
    /*protected*/ abstract function afterFilterHandler(/*string*/ $request, stdClass $context);
    protected function getNextInvokeHandler(Closure $next, /*callable*/ $handler) {
        return Future\wrap(function(/*string*/ $name, array &$args, stdClass $context) use ($next, $handler) {
            return call_user_func($handler, $name, $args, $context, $next);
        });
    }
    protected function getNextFilterHandler(Closure $next, /*callable*/ $handler) {
        return Future\wrap(function(/*string*/ $request, stdClass $context) use ($next, $handler) {
            return call_user_func($handler, $request, $context, $next);
        });
    }
    public function addInvokeHandler(/*callable*/ $handler) {
        if ($handler == null) return;
        $this->invokeHandlers[] = $handler;
        $next = $this->defaultInvokeHandler;
        for ($i = count($this->invokeHandlers) - 1; $i >= 0; --$i) {
            $next = $this->getNextInvokeHandler($next, $this->invokeHandlers[$i]);
        }
        $this->invokeHandler = $next;
        return $this;
    }
    public function addBeforeFilterHandler(/*callable*/ $handler) {
        if ($handler == null) return;
        $this->beforeFilterHandlers[] = $handler;
        $next = $this->defaultBeforeFilterHandler;
        for ($i = count($this->beforeFilterHandlers) - 1; $i >= 0; --$i) {
            $next = $this->getNextFilterHandler($next, $this->beforeFilterHandlers[$i]);
        }
        $this->beforeFilterHandler = $next;
        return $this;
    }
    public function addAfterFilterHandler(/*callable*/ $handler) {
        if ($handler == null) return;
        $this->afterFilterHandlers[] = $handler;
        $next = $this->defaultAfterFilterHandler;
        for ($i = count($this->afterFilterHandlers) - 1; $i >= 0; --$i) {
            $next = $this->getNextFilterHandler($next, $this->afterFilterHandlers[$i]);
        }
        $this->afterFilterHandler = $next;
        return $this;
    }
}

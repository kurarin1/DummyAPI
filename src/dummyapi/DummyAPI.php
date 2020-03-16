<?php

namespace dummyapi;

use dummyapi\dummy\Dummy;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

class DummyAPI extends PluginBase
{

    /* @var $instance DummyAPI*/
    private static $instance;

    /* @var $dummies Dummy[]*/
    private $dummies = [];

    public function onEnable()
    {
        self::$instance = $this;

        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(
            function (int $currentTick) : void {
                foreach(DummyAPI::getInstance()->getAllDummy() as $dummy){
                    $dummy->onUpdate($currentTick);
                }
            }
        ), 1);

        Dummy::__init__();

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
    }

    /*API*/
    public static function getInstance() : self {
        return self::$instance;
    }

    public function registerDummy(Dummy $dummy){
        $dummy->init();
        $this->dummies[$dummy->getEid()] = $dummy;
    }

    public function unregisterDummy(Dummy $dummy){
        $dummy->fin();
        unset($this->dummies[$dummy->getEid()]);
    }

    /* @return Dummy[]*/
    public function getAllDummy() : array {
        return $this->dummies;
    }

}
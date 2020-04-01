<?php

namespace dummyapi;

use dummyapi\dummy\Dummy;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class EventListener implements Listener
{

    /**
     * @priority LOWEST
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event){
        foreach(DummyAPI::getInstance()->getAllDummy() as $dummy){
            $dummy->spawnTo($event->getPlayer());
        }
    }

    /**
     * @priority LOWEST
     * @param EntityLevelChangeEvent $event
     */
    public function onLevelChange(EntityLevelChangeEvent $event){
        if($event->getEntity() instanceof Player){
            foreach(DummyAPI::getInstance()->getAllDummy() as $dummy){
                if($event->getOrigin() === $dummy->getLevel()){
                    $dummy->despawnFrom($event->getEntity());
                }elseif ($event->getTarget() === $dummy->getLevel()){
                    $dummy->spawnTo($event->getEntity());
                }
            }
        }
    }

    /**
     * @priority LOWEST
     * @param DataPacketReceiveEvent $event
     */
    public function onPacketReceive(DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        if($pk instanceof InventoryTransactionPacket){
            if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
                $id = $pk->trData->entityRuntimeId;
                if(isset(DummyAPI::getInstance()->getAllDummy()[$id])){
                    DummyAPI::getInstance()->getAllDummy()[$id]->onTouch($event->getPlayer());
                }
            }
        }
    }

}
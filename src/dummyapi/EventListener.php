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

    public function onJoin(PlayerJoinEvent $event){
        Dummy::create(UUID::fromRandom(), $event->getPlayer(), $event->getPlayer()->getSkin());

        foreach(DummyAPI::getInstance()->getAllDummy() as $dummy){
            $dummy->spawnTo($event->getPlayer());
        }
    }

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
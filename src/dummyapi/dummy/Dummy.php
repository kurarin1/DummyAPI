<?php

namespace dummyapi\dummy;

use dummyapi\DummyAPI;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\UUID;

class Dummy extends Location
{

    private static $empty_skin;

    /* @var $uuid UUID*/
    protected $uuid;
    /* @var $eid int*/
    protected $eid;
    /* @var $skin Skin*/
    protected $skin;
    /* @var $item Item*/
    protected $item;
    /* @var $name string*/
    protected $name = "dummy";

    public static function __init__(){
        self::$empty_skin = new Skin("empty", str_repeat(chr(0).chr(0).chr(0).chr(0), 64 * 64));
    }

    public static function create(...$params) : Dummy{
        $dummy  = new static(...$params);
        DummyAPI::getInstance()->registerDummy($dummy);
        return $dummy;
    }

    public function __construct(UUID $uuid, Location $location, Skin $skin = null, string $name = "", Item $item = null)
    {
        parent::__construct($location->x, $location->y, $location->z, $location->yaw, $location->pitch, $location->level);
        $this->uuid = $uuid;
        $this->eid = Entity::$entityCount++;
        $this->skin = $skin === null ? self::$empty_skin : $skin;
        $this->name = $name;
        $this->item = $item === null ? Item::get(0) : $item;
    }

    public function init(){
        $this->spawn();
    }

    public function fin(){
        $this->despawn();
    }

    public function onUpdate(int $currentTick){

    }

    public function onTouch(Player $player){

    }


    public function getEid() : int {
        return $this->eid;
    }

    public function getUUID() : UUID{
        return $this->uuid;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name){
        $this->name = $name;
        $this->sendName();
    }

    public function sendName(string $name = null){
        foreach ($this->level->getPlayers() as $player){
            $this->sendNameTo($player, $name);
        }
    }

    public function sendNameTo(Player $player, string $name = null){
        $pk = new SetActorDataPacket();
        $pk->entityRuntimeId = $this->eid;
        $pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $name === null ? $this->name : $name]];
        $player->dataPacket($pk);
    }

    public function spawn(){
        foreach ($this->level->getPlayers() as $player){
            $this->spawnTo($player);
        }
    }

    public function spawnTo(Player $player){
        $entry = PlayerListEntry::createAdditionEntry($this->uuid, $this->eid, $this->name, (new LegacySkinAdapter())->toSkinData($this->skin));

        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries = [$entry];
        $player->dataPacket($pk);

        $pk = new AddPlayerPacket();
        $pk->uuid = $this->uuid;
        $pk->username = $this->name;
        $pk->entityUniqueId = $this->eid;
        $pk->entityRuntimeId = $this->eid;
        $pk->position = $this;
        $pk->yaw = $this->yaw;
        $pk->headYaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->motion = new Vector3(0, 0, 0);
        $pk->item = $this->item;
        $player->dataPacket($pk);

        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE;
        $pk->entries = [$entry];
        $player->dataPacket($pk);
    }

    public function despawn(){
        foreach ($this->level->getPlayers() as $player){
            $this->despawnFrom($player);
        }
    }

    public function despawnFrom(Player $player){
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->eid;
        $player->dataPacket($pk);
    }

}
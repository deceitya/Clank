<?php

namespace hototya\item\task;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\item\ItemFactory;
use hototya\item\Clank;
use pocketmine\scheduler\ClosureTask;

class ItemAnimationTask extends Task
{
    private $player;
    private $pk;
    private $pk2;
    private $count = 0;
    private $plugin;

    public function __construct(Player $player, Item $item, Clank $plugin)
    {
        $di = $player->getDirectionVector();

        $pk = new AddItemActorPacket();
        $pk->entityUniqueId = Entity::$entityCount++;
        $pk->entityRuntimeId = $pk->entityUniqueId;
        $pk->item = $item;
        $pk->position = new Vector3($player->x + $di->x, $player->y + $player->getEyeHeight() + $di->y, $player->z + $di->z);
        $pk->motion = new Vector3(0, 0, 0);
        $pk->metadata = [Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_IMMOBILE]];

        $this->player = $player;
        $this->plugin = $plugin;
        $this->pk = $pk;
        $this->pk2 = new LevelEventPacket();
        $this->pk2->evid = LevelEventPacket::EVENT_SOUND_POP;
        $this->pk2->position = $this->pk->position;
        $this->pk2->data = 0;
    }

    public function onRun(int $currentTick)
    {
        $id = 0;
        do {
            $id = mt_rand(0, 513);
        } while (!ItemFactory::isRegistered($id));

        $pk = clone $this->pk;
        $pk->item = Item::get($id, 0, 1);
        if (40 < $this->count) {
            $pk->item = $this->pk->item;
            $this->plugin->getScheduler()->cancelTask($this->getTaskId());
        }
        $this->player->dataPacket($pk);
        $this->player->dataPacket($this->pk2);

        $this->count++;
    }

    public function onCancel()
    {
        $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
            function (int $currentTick): void {
                $pk = new RemoveActorPacket();
                $pk->entityUniqueId = $this->pk->entityUniqueId;
                $this->player->dataPacket($pk);

                $pk = new LevelEventPacket();
                $pk->evid = LevelEventPacket::EVENT_SOUND_ORB;
                $pk->position = $this->pk->position;
                $pk->data = 0;
                $this->player->dataPacket($pk);

                $this->player->getInventory()->addItem($this->pk->item);
                $this->player->sendTip("§l§o§e" . $this->pk->item->getName() . "を手に入れた！");
                $this->player->setImmobile(false);
            }
        ), 20);
    }
}

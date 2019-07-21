<?php

namespace hototya\item\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\item\Item;

use hototya\item\Clank;
use hototya\item\task\ItemAnimationTask;

use onebone\economyapi\EconomyAPI;

class ClankCommand extends Command
{
    private $plugin;

    public function __construct(Clank $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("clank", "ガチャを回します。", "/clank");
        $this->setPermission("clankplugin.command.clank");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!$this->plugin->isEnabled()) {
            return false;
        }
        if (!$this->testPermission($sender)) {
            return false;
        }
        if (!($sender instanceof \pocketmine\Player)) {
            $sender->sendMessage("サーバー内で使用して下さい。");
            return true;
        }
        if (EconomyAPI::getInstance()->myMoney($sender) < $this->plugin->getNeedMoney()) {
            $sender->sendMessage("Clank >> §eお金が足りない為、ガチャはキャンセルされました。");
            return true;
        }

        $items = $this->plugin->getItems();
        $itemData = explode(":", $items[mt_rand(0, count($items) - 1)]);
        $item = Item::get($itemData[0], $itemData[1], $itemData[2]);

        if ($sender->getInventory()->canAddItem($item)) {
            EconomyAPI::getInstance()->reduceMoney($sender, $this->plugin->getNeedMoney());

            $sender->setImmobile(true);
            $this->plugin->getScheduler()->scheduleRepeatingTask(new ItemAnimationTask($sender, $item, $this->plugin), 2);
        } else {
            $sender->sendMessage("Clank >> §eアイテムが追加できない為、ガチャはキャンセルされました");
        }

        return true;
    }
}

<?php

namespace hototya\item\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use hototya\item\Clank;

class AclankCommand extends Command
{
    private $plugin;

    public function __construct(Clank $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("aclank", "ガチャ管理コマンド", "/aclank");
        $this->setPermission("clankplugin.command.aclank");
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
        }

        $this->plugin->createWindow($sender, 1);

        return true;
    }
}

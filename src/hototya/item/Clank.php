<?php

namespace hototya\item;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\utils\Config;

class Clank extends PluginBase
{
    private $money;
    private $items;
    private $ids = [];
    private $forms = [];
    private $config;

    public function onEnable()
    {
        srand();

        $this->saveResource("clank.yml");
        $this->config = new Config($this->getDataFolder() . "clank.yml", Config::YAML);
        $this->money = $this->config->get("money");
        $this->items = $this->config->get("item");

        $this->ids = $this->genRands(5);

        for ($i = 1; $i <= 4; $i++) {
            $path = dirname(__FILE__) . "/form/" . "form${i}.json";
            $this->forms[] = file_get_contents($path);
        }

        $map = $this->getServer()->getCommandMap();
        $commands = [
            "\\hototya\\item\\command\\ClankCommand",
            "\\hototya\\item\\command\\AclankCommand"
        ];
        foreach ($commands as $class) {
            $map->register("clank", new $class($this));
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable()
    {
        $this->config->set("money", $this->money);
        $this->config->set("item", $this->items);
        $this->config->save();
    }

    public function getNeedMoney(): int
    {
        return (int) $this->money;
    }

    public function setNeedMoney(int $money)
    {
        $this->money = $money;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(string $item)
    {
        $this->items[] = $item;
    }

    public function removeItem(string $item)
    {
        $this->items = array_diff($this->items, [$item]);
        $this->items = array_values($this->items);
    }

    public function getId(int $num): ?int
    {
        return $this->ids[$num - 1];
    }

    public function getForm(int $num): ?string
    {
        return $this->forms[$num - 1];
    }

    public function createWindow(Player $player, int $num)
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $this->ids[$num - 1];
        $pk->formData = $this->forms[$num - 1];
        $player->dataPacket($pk);
    }

    private function genRands(int $count): array
    {
        $list = [];
        for ($i = 0; $i < $count; $i++) {
            $rand = null;
            do {
                $rand = mt_rand();
            } while (in_array($rand, $list));
            $list[] = $rand;
        }

        return $list;
    }
}

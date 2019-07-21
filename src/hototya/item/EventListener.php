<?php

namespace hototya\item;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

class EventListener implements Listener
{
    private $plugin;

    public function __construct(Clank $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPacketReceive(DataPacketReceiveEvent $event)
    {
        if ($event->getPacket() instanceof ModalFormResponsePacket) {
            $pk = $event->getPacket();
            $player = $event->getPlayer();
            if ($pk->formData == "null\n") {
                return;
            }

            switch ($pk->formId) {
                case $this->plugin->getId(1):
                    switch ($pk->formData) {
                        case 0:
                            $this->plugin->createWindow($player, 2);
                            break;
                        case 1:
                            $this->plugin->createWindow($player, 3);
                            break;
                        case 2:
                            $this->plugin->createWindow($player, 4);
                            break;
                        case 3:
                            $text = "";
                            foreach ($this->plugin->getItems() as $item) {
                                $text .= "${item}\n";
                            }

                            $pk = new ModalFormRequestPacket();
                            $pk->formId = $this->plugin->getId(5);
                            $pk->formData = json_encode(
                                [
                                    "type" => "modal",
                                    "title"=> "Clank >> ガチャアイテム一覧",
                                    "content" => "ID:データ値:個数\n\n" . $text,
                                    "button1" => "OK!",
                                    "button2" => "OK!"
                                ],
                                JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE
                            );
                            $player->dataPacket($pk);
                            break;
                        default:
                    }
                    break;
                case $this->plugin->getId(2):
                    $data = json_decode($pk->formData);
                    if ($data == null) {
                        $player->sendMessage("Clank >> §e値を入力してください。");
                    }
                    unset($data[0]);
                    $data = array_values($data);

                    foreach ($data as $v) {
                        if (!is_numeric($v)) {
                            $player->sendMessage("Clank >> §e整数を入力してください。");
                            return;
                        }
                    }

                    $this->plugin->addItem(implode(":", $data));
                    $player->sendMessage("Clank >> §b追加しました。§f(ID: " . $data[0] . ", データ値: " . $data[1] . ", 個数: " .$data[2] . ")");
                    break;
                case $this->plugin->getId(3):
                    $data = json_decode($pk->formData);
                    if ($data == null) {
                        $player->sendMessage("Clank >> §e入力してください。");
                    }
                    unset($data[0]);
                    $data = array_values($data);

                    foreach ($data as $v) {
                        if (!is_numeric($v)) {
                            $player->sendMessage("Clank >> §e整数を入力してください。");
                            return;
                        }
                    }

                    $text = implode(":", $data);
                    if (in_array($text, $this->plugin->getItems())) {
                        $this->plugin->removeItem($text);
                        $player->sendMessage("Clank >> §b削除しました。§f(ID: " . $data[0] . ", データ値: " . $data[1] . ", 個数: " .$data[2] . ")");
                    } else {
                        $player->sendMessage("Clank >> §eそのアイテムはガチャで排出されません。");
                    }
                    break;
                case $this->plugin->getId(4):
                    $data = json_decode($pk->formData);
                    if ($data == null) {
                        $player->sendMessage("Clank >> §e入力してください。");
                    }

                    if (is_numeric($data[1])) {
                        $this->plugin->setNeedMoney((int) $data[1]);
                        $player->sendMessage("Clank >> §変更が完了しました。§f(" . $data[1] . ")");
                    } else {
                        $player->sendMessage("Clank >> §e整数を入力してください。");
                    }
                    break;
                default:
            }
        }
    }
}

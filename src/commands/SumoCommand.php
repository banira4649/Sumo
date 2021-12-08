<?php

declare(strict_types=1);

namespace banira4649\Sumo\commands;

use pocketmine\command\{Command, CommandSender};

class SumoCommand extends Command{

    public function __construct(string $name, $main){
        $this->setPermission("pocketmine.group.operator");
        parent::__construct($name, "SUMOに関する操作を行います");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $label, array $args){
        if($args !== []){
            switch($args[0]){
                case "start":
                    if($this->main->game->getStatus() === 2){
                        $sender->sendMessage("§cSUMOはすでに開始されています");
                        return true;
                    }
                    if(empty($this->main->game->getPlayers())){
                        $sender->sendMessage("§cエントリーしたプレイヤーがいないため、トーナメントが開始できません");
                        return true;
                    }
                    $this->main->game->start();
                    return true;
                case "open":
                    if($this->main->game->getStatus() === 2){
                        $sender->sendMessage("§cトーナメントが進行中です");
                        return true;
                    }
                    if($this->main->game->getStatus() === 1){
                        $sender->sendMessage("§cエントリーはすでに開始されています");
                        return true;
                    }
                    $this->main->game->setStatus(1);
                    $this->main->getServer()->broadcastMessage("§l§f[§3SUMO§f] §a相撲イベントのエントリーが開始されました\n§l§f>> §e/entry§fで参加できます");
                    return true;
                case "ready":
                    if($this->main->game->getStatus() === 2){
                        $sender->sendMessage("§cトーナメントが進行中です");
                        return true;
                    }
                    if($this->main->game->getStatus() === 0){
                        $sender->sendMessage("§c現在エントリーは行われていません");
                        return true;
                    }
                    $this->main->game->setStatus(0);
                    $this->main->getServer()->broadcastMessage("§l§f[§3SUMO§f] §a相撲イベントのエントリーが終了されました");
                    return true;
            }
        }
        return true;
    }

}

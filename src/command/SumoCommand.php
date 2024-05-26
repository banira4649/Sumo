<?php

declare(strict_types=1);

namespace banira4649\Sumo\command;

use banira4649\Sumo\game\Game;
use banira4649\Sumo\Main;
use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;

class SumoCommand extends Command{

    private Main $main;

    public function __construct(string $name, Main $main){
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
        parent::__construct($name, "SUMOに関する操作を行います", usageMessage: "/sumo [start|open|ready]");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if(!$this->testPermission($sender)) return false;
        if($args !== []){
            switch($args[0]){
                case "start":
                    if($this->main->game->getStatus() === Game::STAT_PLAYING){
                        $sender->sendMessage("§cSUMOはすでに開始されています");
                        return true;
                    }
                    if(empty($this->main->game->getPlayers())){
                        $sender->sendMessage("§c参加者が存在しないため、トーナメントが開始できません");
                        return true;
                    }
                    $this->main->game->start();
                    return true;
                case "open":
                    if($this->main->game->getStatus() === Game::STAT_PLAYING){
                        $sender->sendMessage("§cトーナメントが進行中です");
                        return true;
                    }
                    if($this->main->game->getStatus() === Game::STAT_ENTRY){
                        $sender->sendMessage("§cエントリーはすでに開始されています");
                        return true;
                    }
                    $this->main->game->setStatus(Game::STAT_ENTRY);
                    $this->main->getServer()->broadcastMessage("§l§f[§3SUMO§f] §a相撲イベントのエントリーが開始されました\n§l§f>> §e/entry§fで参加できます");
                    return true;
                case "ready":
                    if($this->main->game->getStatus() !== Game::STAT_ENTRY){
                        $sender->sendMessage("§c現在エントリーは行われていません");
                        return true;
                    }
                    $this->main->game->setStatus(Game::STAT_READY);
                    $this->main->getServer()->broadcastMessage("§l§f[§3SUMO§f] §a相撲イベントのエントリーが締め切られました");
                    return true;
            }
        }
        return true;
    }

}

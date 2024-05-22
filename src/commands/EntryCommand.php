<?php

declare(strict_types=1);

namespace banira4649\Sumo\commands;

use banira4649\Sumo\game\Game;
use banira4649\Sumo\Main;
use pocketmine\command\{Command, CommandSender};
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class EntryCommand extends Command{

    private Main $main;

    public function __construct(string $name, Main $main){
        $this->setPermission(DefaultPermissions::ROOT_USER);
        parent::__construct($name, "SUMOトーナメントにエントリーします");
        $this->main = $main;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if(!$this->testPermission($sender)) return false;
        if($sender instanceof Player){
            if($this->main->game->getStatus() !== Game::STAT_ENTRY){
                $sender->sendMessage("§c現在エントリーはできません");
                return true;
            }
            if($this->main->game->isEntried($sender)){
                $sender->sendMessage("§cあなたはすでにエントリー済みです");
                return true;
            }
            $sender->sendMessage("§l§f[§3SUMO§f] §eエントリー§fが完了しました！");
            $this->main->getServer()->broadcastMessage("§l§f[§bエントリー§f] >> §e".$sender->getDisplayName());
            $this->main->game->addPlayer($sender);
            return true;
        }
        return true;
    }

}

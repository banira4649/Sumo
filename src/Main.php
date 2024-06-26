<?php

declare(strict_types=1);

namespace banira4649\Sumo;

use banira4649\Sumo\event\EventListener;
use pocketmine\plugin\PluginBase;
use pocketmine\player\{Player, GameMode};
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\Config;
use banira4649\Sumo\command\{SumoCommand, EntryCommand, EntrylistCommand, SumoarenaCommand};
use banira4649\Sumo\game\Game;
use Symfony\Component\Filesystem\Path;

class Main extends PluginBase{

    public const WORLD_NAME = "sumo";

    public Vector3 $sumoPos0;
    public Vector3 $sumoPos1;
    public Vector3 $sumoPos2;
    public Game $game;

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new SumoCommand("sumo", $this),
            new EntryCommand("entry", $this),
            new EntrylistCommand("entrylist", $this),
            new SumoarenaCommand("sumoarena", $this),
        ]);
        $this->game = new Game($this);
        $stageData = new Config(
            Path::join(Server::getInstance()->getDataPath(), "worlds", self::WORLD_NAME, 'config.json'),
            Config::JSON,
            ["spawn" => [0, 0, 0], "pos1" => [0, 0, 0], "pos2" => [0, 0, 0]]
        );
        $this->sumoPos0 = new Vector3(...$stageData->get("spawn"));
        $this->sumoPos1 = new Vector3(...$stageData->get("pos1"));
        $this->sumoPos2 = new Vector3(...$stageData->get("pos2"));
    }

    public static function resetPlayer(Player $player): void{
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
        $player->setHealth($player->getMaxHealth());
        $player->getXpManager()->setXpLevel(0);
        $player->getXpManager()->setXpProgress(0);
        $player->setOnFire(0);
        $player->setGameMode(GameMode::ADVENTURE());
    }
}

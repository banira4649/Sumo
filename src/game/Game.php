<?php

declare(strict_types=1);

namespace banira4649\Sumo\game;

use banira4649\Sumo\event\player\PlayerJoinArenaEvent;
use banira4649\Sumo\Main;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use banira4649\Sumo\utils\Utils;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\WorldManager;

class Game{

    public const STAT_READY = 0;
    public const STAT_ENTRY = 1;
    public const STAT_PLAYING = 2;
    public const STAT_BROKEN = 3;



    private array $players = [];
    private array $winners = [];
    private ?Player $player1 = null;
    private ?Player $player2 = null;
    private int $phase = self::STAT_READY;
    private int $status = 0;
    private ?World $stage = null;
    private Main $main;

    private WorldManager $worldManager;

    public function __construct(Main $main){
        $this->main = $main;
        $this->worldManager = Server::getInstance()->getWorldManager();
        $this->worldManager->loadWorld(Main::WORLD_NAME);
        $this->setStage($this->worldManager->getWorldByName(Main::WORLD_NAME));
    }

    public function getPlayers(): array{
        return $this->players;
    }

    public function getNameList(): array{
        $list = [];
        foreach($this->players as $players){
            $list[] = $players->getDisplayName();
        }
        return $list;
    }

    public function addPlayer(Player $player): void{
        $this->players[] = $player;
    }

    public function removePlayer(Player $player): void{
        $this->players = array_diff($this->players, [$player]);
        $this->players = array_values($this->players);
    }

    public function getEnemy(Player $player): ?Player{
        if($this->isCombat($player)){
            if($player === $this->player1){
                return $this->player2;
            }
            if($player === $this->player2){
                return $this->player1;
            }
        }
        return null;
    }

    public function isEntried(Player $player): bool{
        if(in_array($player, $this->players)){
            return true;
        }else{
            return false;
        }
    }

    public function isCombat(Player $player): bool{
        if(($player === $this->player1) || ($player === $this->player2)){
            return true;
        }else{
            return false;
        }
    }

    public function isOnStage(?Player $player): bool{
        if($player === null){
            return false;
        }elseif(!$player->isOnline()){
            return false;
        }elseif($player->getWorld() === $this->stage){
            return true;
        }else{
            return false;
        }
    }

    public function getStatus(): int{
        return $this->status;
    }

    public function setStatus(int $value): void{
        $this->status = $value;
    }

    public function getStage(): ?World{
        return $this->stage;
    }

    public function setStage(?World $stage): void{
        $this->stage = $stage;
    }

    public function open(): void{
        $this->status = self::STAT_ENTRY;
    }

    public function start(): void{
        $this->status = self::STAT_PLAYING;
        shuffle($this->players);
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
            if($players->getWorld() === $this->stage){
                $players->teleport($this->main->sumoPos0);
                $players->sendMessage(Utils::PREFIX_SUMO."§cPHASE §f: ".$this->phase);
            }
        }
        $this->next();
    }

    public function ready(): void{
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
			if($this->isOnStage($players)){
				$players->sendMessage("§l[§3SUMO§f] §cGame§f : ".$this->player1->getDisplayName()." §cvs§f ".$this->player2->getDisplayName());
			}
		}
        $this->setPlayer($this->player1);
        $this->setPlayer($this->player2);
        for($i = 1; $i <= 3; $i ++){
            $this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(
    			function () use ($i): void{
                    if(($this->player1 !== null) && ($this->player2 !== null)){
                        $s = 4 - $i;
        				foreach($this->main->getServer()->getOnlinePlayers() as $players){
        					if($players->getWorld() === $this->stage){
        						$players->sendMessage(Utils::PREFIX_SUMO."§b開始まで §f: $s");
        						Utils::sendSound($players, "random.anvil_land", 1, 2);
        					}
        				}
                    }
    			}
    		), 20 * $i);
        }
		$this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(
			function (): void{
                if(($this->player1 !== null) && ($this->player2 !== null)){
    				$this->player1->setNoClientPredictions(false);
    				$this->player2->setNoClientPredictions(false);
    				foreach($this->main->getServer()->getOnlinePlayers() as $players){
    					if($players->getWorld() === $this->stage){
    						$players->sendMessage(Utils::PREFIX_SUMO."§b開始まで §f: Go!");
    						Utils::sendSound($players, "random.explode", 1, 1);
    					}
    				}
                }
			}
		), 20 * 4);
    }

    public function next(): void{
        $this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(
			function (): void{
				$playerAll = $this->main->getServer()->getOnlinePlayers();
				if(isset($this->players[0])){
					if(isset($this->players[1])){
						$this->player1 = $this->players[0];
						if($this->isOnStage($this->player1)){
							$this->player1->teleport($this->main->sumoPos1);
						}else{
							foreach($playerAll as $players){
								if($players->getWorld() === $this->stage){
									$players->sendMessage(Utils::PREFIX_SUMO."§a".$this->players[0]->getDisplayName()."§bさんが不在のため、"."§a".$this->players[1]->getDisplayName()."§b"."さんは次のフェーズへ進みます");
								}
							}
							$this->win($this->players[1]);
							return;
						}
						$this->player2 = $this->players[1];
						if($this->isOnStage($this->player2)){
							$this->player2->teleport($this->main->sumoPos2);
						}else{
							foreach($playerAll as $players){
								if($players->getWorld() === $this->stage){
									$players->sendMessage(Utils::PREFIX_SUMO."§a".$this->players[1]->getDisplayName()."§bさんが不在のため、"."§a".$this->players[0]->getDisplayName()."§b"."さんは次のフェーズへ進みます");
								}
							}
							$this->win($this->players[0]);
							return;
						}
						$this->ready();
                    }else{
						foreach($playerAll as $players){
							if($players->getWorld() === $this->stage){
								$players->sendMessage(Utils::PREFIX_SUMO."§b人数が奇数であるため、"."§s".$this->players[0]->getDisplayName()."§b"."さんは次のフェーズへ進みます");
							}
						}
						$this->win($this->players[0]);
                    }
                }elseif(isset($this->winners[0])){
					if(isset($this->winners[1])){
						$this->players = $this->winners;
						$this->winners = [];
						shuffle($this->players);
						$this->phase++;
						foreach($playerAll as $players){
							if($players->getWorld() === $this->stage){
								$players->sendMessage(Utils::PREFIX_SUMO."§cPHASE §f: ".$this->phase);
							}
						}
						$this->next();
                    }else{
						$this->main->getServer()->broadcastMessage(Utils::PREFIX_SUMO."§cTOURNAMENT WINNER §f: §a".$this->winners[0]->getDisplayName());
						foreach($this->main->getServer()->getOnlinePlayers() as $players){
							Utils::sendSound($players, "random.explode", 1, 1);
							Utils::sendSound($players, "random.totem", 1, 1);
						}
                        $this->break();
                    }
                }
			}
		), 20);
    }

    public function win(Player $player): void{
        $this->main->getServer()->broadcastMessage(Utils::PREFIX_SUMO."§cWINNER §f: §a".$player->getDisplayName());
        $this->winners[] = $player;
        array_splice($this->players, 0, 2);
        $this->resetPlayer($this->player1);
        $this->resetPlayer($this->player2);
        $this->player1 = null;
        $this->player2 = null;
        $this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(
			function (): void{
                $this->next();
            }
        ), 20 * 3);
    }

    public function setPlayer(?Player $player): void{
        if(($player !== null) && ($player->isOnline())){
    		foreach($this->main->getServer()->getOnlinePlayers() as $players){
    			if(!$this->isCombat($players)){
    				$player->hidePlayer($players);
    			}
    		}
            $player->setNoClientPredictions();
        }
    }

    public function resetPlayer(?Player $player): void{
        if(($player !== null) && ($player->isOnline())){
            foreach($this->main->getServer()->getOnlinePlayers() as $players){
                $player->showPlayer($players);
            }
            $player->setNoClientPredictions(false);
            if($this->isOnStage($player)){
                $player->teleport($this->main->sumoPos0);
            }
        }
    }

    public function break(): void{
        $this->main->game = new Game($this->main);
        $this->status = self::STAT_BROKEN;
    }

    public function joinArena(Player $player): void{
        $ev = new PlayerJoinArenaEvent($player);
        $ev->call();
        if(!$ev->isCancelled()){
            Main::resetPlayer($player);
            $player->teleport($this->stage->getSafeSpawn());
            $player->teleport($this->main->sumoPos0);
            $player->sendMessage("§aSUMOアリーナに入場しました");
        }else{
            $player->sendMessage("§cSUMOアリーナへの入場に失敗しました");
        }
    }
}

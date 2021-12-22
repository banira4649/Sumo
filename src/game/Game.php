<?php

declare(strict_types=1);

namespace banira4649\Sumo\game;

use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use banira4649\Sumo\function\Ex;

class Game{

    private array $players = [];
    private array $winners = [];
    private ?Player $player1 = null;
    private ?Player $player2 = null;
    private int $phase = 1;
    private int $status = 0;

    public function __construct($main){
        $this->main = $main;
    }

    public function getPlayers(){
        return $this->players;
    }

    public function getNameList(){
        $list = [];
        foreach($this->players as $players){
            $list[] = $players->getName();
        }
        return $list;
    }

    public function addPlayer(Player $player){
        $this->players[] = $player;
    }

    public function removePlayer(Player $player){
        $this->players = array_diff($this->players, [$player]);
        $this->players = array_values($this->players);
    }

    public function getEnemy(Player $player){
        if($this->isCombat($player)){
            if($player === $this->player1){
                return $this->player2;
            }
            if($player === $this->player2){
                return $this->player1;
            }
        }else{
            return null;
        }
    }

    public function isEntried(Player $player){
        if(in_array($player, $this->players)){
            return true;
        }else{
            return false;
        }
    }

    public function isCombat(Player $player){
        if(($player === $this->player1) || ($player === $this->player2)){
            return true;
        }else{
            return false;
        }
    }

    public function isOnStage(?Player $player){
        if($player === null){
            return false;
        }elseif(!$player->isOnline()){
            return false;
        }elseif($player->getWorld() === $this->main->stage){
            return true;
        }else{
            return false;
        }
    }

    public function getStatus(){
        return $this->status;
    }

    public function setStatus(int $value){
        $this->status = $value;
    }

    public function open(){
        $this->status = 1;
    }

    public function start(){
        $this->status = 2;
        shuffle($this->players);
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
            if($players->getWorld() === $this->main->stage){
                $players->teleport($this->main->sumoPos0);
                $players->sendMessage("§l§f[§3SUMO§f] §ePHASE §f: ".$this->phase);
            }
        }
        $this->next();
    }

    public function ready(){
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
			if($this->isOnStage($players)){
				$players->sendMessage("§l[§3SUMO§f] §eGame§f : ".$this->player1?->getName()." §cvs§f ".$this->player2?->getName());
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
        					if($players->getWorld() === $this->main->stage){
        						$players->sendMessage("§l§f[§3SUMO§f] §e開始まで §f: {$s}");
        						Ex::sendSound($this->main, $players, "random.anvil_land", 1, 2);
        					}
        				}
                    }
    			}
    		), 20 * $i);
        }
		$this->main->getScheduler()->scheduleDelayedTask(new ClosureTask(
			function (): void{
                if(($this->player1 !== null) && ($this->player2 !== null)){
    				$this->player1?->setImmobile(false);
    				$this->player2?->setImmobile(false);
    				foreach($this->main->getServer()->getOnlinePlayers() as $players){
    					if($players->getWorld() === $this->main->stage){
    						$players->sendMessage("§l§f[§3SUMO§f] §e開始まで §f: Go!");
    						Ex::sendSound($this->main, $players, "random.explode", 1, 1);
    					}
    				}
                }
			}
		), 20 * 4);
    }

    public function next(){
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
								if($players->getWorld() === $this->main->stage){
									$players->sendMessage("§l§f[§3SUMO§f] "."§b".$this->players[0]->getName()."§dさんが不在のため、"."§b".$this->players[1]->getName()."§d"."さんは次のフェーズへ進みます");
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
								if($players->getWorld() === $this->main->stage){
									$players->sendMessage("§l§f[§3SUMO§f] "."§b".$this->players[1]->getName()."§dさんが不在のため、"."§b".$this->players[0]->getName()."§d"."さんは次のフェーズへ進みます");
								}
							}
							$this->win($this->players[0]);
							return;
						}
						$this->ready();
						return;
					}else{
						foreach($playerAll as $players){
							if($players->getWorld() === $this->main->stage){
								$players->sendMessage("§l§f[§3SUMO§f] §d人数が奇数であるため、"."§b".$this->players[0]->getName()."§d"."さんは次のフェーズへ進みます");
							}
						}
						$this->win($this->players[0]);
						return;
					}
				}elseif(isset($this->winners[0])){
					if(isset($this->winners[1])){
						$this->players = $this->winners;
						$this->winners = [];
						shuffle($this->players);
						$this->phase++;
						foreach($playerAll as $players){
							if($players->getWorld() === $this->main->stage){
								$players->sendMessage("§l§f[§3SUMO§f] §ePHASE §f: ".$this->phase);
							}
						}
						$this->next();
						return;
					}else{
						$this->main->getServer()->broadcastMessage("§l§f[§3SUMO§f] §eTournament Winner §f: §a".$this->winners[0]->getName());
						foreach($this->main->getServer()->getOnlinePlayers() as $players){
							Ex::sendSound($this->main, $players, "random.explode", 1, 1);
							Ex::sendSound($this->main, $players, "random.totem", 1, 1);
						}
                        $this->break();
						return;
					}
				}
			}
		), 20 * 1);
    }

    public function win(Player $player){
        $this->main->getServer()->broadcastMessage("§l§f[§3SUMO§f] §eWinner §f: §a".$player->getName());
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

    public function setPlayer(?Player $player){
        if(($player->isOnline()) && ($player !== null)){
    		foreach($this->main->getServer()->getOnlinePlayers() as $players){
    			if(!$this->isCombat($players)){
    				$player->hidePlayer($players);
    			}
    		}
            $player->setImmobile(true);
        }
    }

    public function resetPlayer(?Player $player){
        if(($player->isOnline()) && ($player !== null)){
            foreach($this->main->getServer()->getOnlinePlayers() as $players){
                $player->showPlayer($players);
            }
            $player->setImmobile(false);
            if($this->isOnStage($player)){
                $player->teleport($this->main->sumoPos0);
            }
        }
    }

    public function break(){
        $this->main->game = new Game($this->main);
        $this->status = 3;
    }

}

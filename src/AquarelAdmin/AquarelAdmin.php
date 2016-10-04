<?php

namespace AquarelAdmin;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\CustomInventory;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\InventoryType;
use pocketmine\item\Item;
use pocketmine\permission\Permission;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;




class AquarelAdmin extends PluginBase implements Listener{
	public $cfg;
	public $aquaCommand;
	private $warnings = [];
	private $players = [];
	
	public function onEnable(){
		$this->getLogger()->info(TF::GREEN."Loading AquarelAdmin...");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		Server::getInstance()->loadLevel("10");
		Server::getInstance()->loadLevel("50");
		Server::getInstance()->loadLevel("70");
		Server::getInstance()->loadLevel("100");
		/*Server::getInstance()->loadLevel("SteamMachina");*/
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder() . "denied-words/");
		$this->saveDefaultConfig();
		$this->cfg = $this->getConfig()->getAll();
		$this->aquaCommand = new AquaCommand($this);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		$this->aquaCommand->onCommand($sender, $command, $label, $args);
	}
	
	public function onDisable(){
		$this->getLogger()->info(TF::RED."AquarelAdmin disabled");
		
	}
	
	public function getOwner(){
		return $this->plugin;
	}
	
	public function worldChange(PlayerMoveEvent $event){
		$playerLevel = $event->getPlayer()->getLevel()->getName();
		switch($playerLevel){
			case('spawn'):
				$gamemode = 0;
				$item1 = Item::get(Item::BOOK, 0, 1);
				$item2 = Item::get(Item::COMPASS, 0, 1);
				$item3 = Item::get(Item::EMERALD, 0, 1);
				$item4 = Item::get(Item::APPLE,0, 1);
				if(!$event->getPlayer()->isOP()){
				$event->getPlayer()->getInventory()->setItem(0, $item1);
				$event->getPlayer()->getInventory()->setItem(1, $item3);
				$event->getPlayer()->getInventory()->setItem(2, $item2);
				$event->getPlayer()->getInventory()->setItem(3, $item4);
				}
				break;
			case('10'):
				$gamemode = 1;
				break;
			case('50'):
				$gamemode = 1;
				break;
			case('70'):
				$gamemode = 1;
				break;
			case('100'):
				$gamemode = 1;
				break;
			case('SteamMachina'):
				$gamemode = 3;
				break;
			default:
				$gamemode = 0;
				break;
		}
		if($event->getPlayer()->getGamemode() != $gamemode){
			if($event->getPlayer()->isOp()){
			}
			else{
				$event->getPlayer()->setGamemode($gamemode);
				$event->getPlayer()->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Bienvenue dans le monde ".$event->getPlayer()->getLevel()->getName());
				$event->getPlayer()->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."J'ai changé ton mode de jeu");
				return true;
			}
		}
		
	}
	
	public function onItemHeld(PlayerItemHeldEvent $event){
		$p = $event->getPlayer();
		$playerLevel = $p->getLevel()->getName();
		if($playerLevel == 'spawn'){
				if($p->getItemInHand()->getId() == Item::BOOK){
					$p->sendPopup(TF::AQUA."Règles");
				}
				elseif($p->getItemInHand()->getId() == Item::EMERALD){
					$p->sendPopup(TF::AQUA."Dons - Grades");
				}
				elseif($p->getItemInHand()->getId() == Item::COMPASS){
					$p->sendPopup(TF::AQUA."Warps");
				}
				elseif($p->getItemInHand()->getId() == Item::APPLE){
					$p->sendPopup(TF::AQUA."Spawn");
				}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		$p = $event->getPlayer();
		$playerLevel = $p->getLevel()->getName();
		if($playerLevel == 'spawn'){
			if(!$p->isOp()){
			$event->setCancelled();
			$p->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Tu veux que je vienne aussi casser des trucs chez toi ?");
			}
		}
	}
	
	public function onPlace(BlockPlaceEvent $event){
		$p = $event->getPlayer();
		$playerLevel = $p->getLevel()->getName();
		if($playerLevel == 'spawn'){
			if(!$p->isOp()){
			$event->setCancelled();
			$p->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Tu veux que je vienne aussi mettre du désordre chez toi ?");
			}
		}
	}
	
	public function onTouch(PlayerInteractEvent $event){
		$p = $event->getPlayer();
		$item = $event->getItem();
		$playerLevel = $p->getLevel()->getName();
		if($playerLevel == 'spawn'){
			if(!$p->isOp()){
				if($item->getId() == Item::BOOK){
					$p->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Rédaction des règles en cours..");
				}
				elseif($item->getId() == Item::EMERALD){
					$p->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Rédaction de cette partie en cours..");
				}
				elseif($item->getId() == Item::APPLE){
					$p->teleport($p->getLevel()->getSafeSpawn());
					$p->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Retour au spawn..");
				}
			}
		}
	}
	
	public function getWord($word){
		if(file_exists($this->getDataFolder() . "denied-words/" . strtolower($word . ".yml"))){
			$data = new Config($this->getDataFolder() . "denied-words/" . strtolower($word . ".yml"), Config::YAML);
			return $data->getAll();
		}else{
			return false;
		}
	}
	
	public function wordExists($word){
		return file_exists($this->getDataFolder() . "denied-words/" . strtolower($word . ".yml"));
	}
	
	public function addWord($word){
		$word = strtolower($word);
		$default = array(
			"delete-message" => false,
			"enable-replace" => true,
			"replace-word" => "****",
			"sender" => array(
				"kick" => false,
			    "ban" => false,
		        ),
			"kick" => array(
				"message" => "Kick pour insulte !"
			),
			"ban" => array(
				"message" => "Banni pour insulte !"
			)
		);
		$data = new Config($this->getDataFolder() . "denied-words/" . strtolower($word . ".yml"), Config::YAML);
		$data->setAll($default);
		$data->save();
	}
	
	public function removeWord($word){
		unlink($this->getDataFolder() . "denied-words/" . strtolower($word . ".yml"));
	}
	
	public function onChatCommand(PlayerCommandPreprocessEvent $event){
		$message = $event->getMessage();
		$player = $event->getPlayer();
		$this->cfg = $this->getConfig()->getAll();
		$tempmessage = $message;
		if(!$player->isOp()){
		$messagewords = str_word_count($message, 1);
			for($i = 0; $i < count($messagewords); $i++){
				if($this->wordExists($messagewords[$i])){
				$tmp = $this->getWord($messagewords[$i]);
					if($tmp["delete-message"] == true){
						$event->setCancelled(true);
					}
					if($tmp["enable-replace"] == true){
						$replace = $tmp["replace-word"];
						$tempmessage = str_replace($messagewords[$i],$replace,$tempmessage);
					}
					if($tmp["sender"]["kick"] == true){
						$player->kick($tmp["kick"]["message"]);
					}
					if($tmp["sender"]["ban"] == true){
						$this->getServer()->getNameBans()->addBan($player->getName(), $tmp["ban"]["message"]);
						$player->kick($tmp["ban"]["message"]);
					}
				$player->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Tu aimerais que je dise cela à ta maman ?");
				$event->setMessage($tempmessage);
				}
			}
		}
	}
	
}
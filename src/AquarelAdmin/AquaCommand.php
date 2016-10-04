<?php

namespace AquarelAdmin;

use pocketmine\plugin\PluginBase;
use pocketmine\permission\Permission;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\protocol\DisconnectPacket;


class AquaCommand{
	
	public $plugin;
	
	public function __construct(AquarelAdmin $plugin){
        $this->plugin = $plugin;
    }
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if($sender instanceof Player){
			$player = $sender->getPlayer()->getName();
			if(strtolower($command->getName('aqua'))){
				if(empty($args)){
					$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Utilise /aqua help pour avoir une liste des commandes");
					return true;
				}
				if($args[0] == "addword"){
					if($sender->hasPermission("aqua.addword")){
						if(isset($args[1])){
							$args[1] = strtolower($args[1]);
							if($this->plugin->wordExists($args[1])){
								$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Ce mot est déjà banni");
							}else{
								$this->plugin->addWord($args[1]);
								$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Nouveau mot banni !");
							}
						}else{
							$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Utilise /aqua addword <word>");
						}
					}else{
    				$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Qu'essayes tu de faire?..");
					}
				}
				if($args[0] == "removeword"){
					if($sender->hasPermission("aqua.removeword")){
						if(isset($args[1])){
							$args[1] = strtolower($args[1]);
							if($this->plugin->wordExists($args[1])){
								$this->plugin->removeWord($args[1]);
								$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Mot débanni !");
							}else{
								$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Mot inconnu");
							}
						}else{
							$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Utilise /aqua removeword <word>");
						}
					}else{
    				$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Qu'essayes tu de faire?..");
					}
				}
				if($args[0] == "help"){
					if($args[1] == 1 or empty($args[1])){
						$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Page 1 sur 1");
						$sender->sendMessage(TF::AQUA."/aqua addword ".TF::GRAY."- Ajoute un mot à la liste des mots bannis");
						$sender->sendMessage(TF::AQUA."/aqua removeword ".TF::AQUA."- Retire un mot de la liste des mots bannis");
					}
				}
				if($args[0] == "vanish"){
					if($sender->hasPermission("aqua.vanish")){
						$this->plugin->getServer()->removeOnlinePlayer($sender);
						$this->plugin->getServer()->removePlayer($sender);
							foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
								if($player->canSee($sender)){
									if(!$player->isOp()){
										$player->hidePlayer($sender);
									}
								}
							}
						$this->plugin->getServer()->broadcastMessage(TF::YELLOW.$sender->getName()." left the game");
						unset($sender->buffer);
						$sender->sendMessage("[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Tu es maintenant invisible aux yeux des joueurs");
					}
					return true;
				}
				if($args[0] == "unvanish"){
					if($sender->hasPermission("aqua.unvanish")){
						$pk = new DisconnectPacket();
						$pk->message = "[".TF::AQUA."AquarelAdmin".TF::WHITE."] ".TF::GRAY."Tu es à nouveau visible, reconnecte toi";
						$sender->directDataPacket($pk);
					}
					return true;
				}
			}
		}
	}
}
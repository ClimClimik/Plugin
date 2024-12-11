<?php
namespace Fleppy;

use pocketmine\plugin\PluginBase;

use Fleppy\Commands\{SayCmd, TellCmd, TpCmd, ListCmd, TimeCmd};

use pocketmine\utils\Config;

class MainClass extends PluginBase{
	
	public $cfg;
	
	function onEnable(){
		
	
		$this->getLogger()->info("ПЛАГИН ВКЛЮЧЕН!");
		
		if(!is_dir($this->getDataFolder()))
			mkdir($this->getDataFolder());
		
		
		$cfg = new Config($this->getDataFolder()."config.yml", Config::YAML, array(
		
			"not_permission" => "§cУ вас нет прав!",
			"!player_exists" => "§cИгрок не онлайн!",
			"say_use" => "§l§8(§eОповещение§8) §a×§r §fИспользуйте: §d/say [сообщение]",
			"say_broadcast" => "§fВнимание! §cАдминистратор §e{NAME} §fоповещает: {MESSAGE}",
			"tp_use" => "§7(§cТелепорт§7) §c§l•§r §fИспользуйте: §a/tp [имя игрока]",
			"tp_ok" => "§7(§dТелепорт§7) §c§l•§r §fВы успешно телепортивались!",
			"time_use" => "§7(§bВремя§7) §c§l•§r §fИспользуйте: §e/time [day/night]",
			"time_change" => "§l§8(§bВремя§8) §a×§r §fВы успешно сменили время на §2{TIME}",
			"tell_use" => "§l§8(§dЛС§8) §a× §r§fИспользуйте: §6/tell [игрок] [сообщение]",
			"tell_sender" => "§fВы §8-> §d{PLAYER}: §f{MESSAGE}",
			"tell_player" => "§d{PLAYER} §8-> §fВы: {MESSAGE}",
			"list" => "§fОнлайн: §e{ONLINE}/{MAX}\n§fСписок игроков:§c {PLAYERS}"
			
		));
		$this->cfg = $cfg->getAll();
			
		$this->removeCommands();
		
	}
	
	
	function removeCommands(){
		
		$cmds = ["say", "tell", "tp", "time", "list"];
		foreach($cmds as $cmd){
			
			if($this->getServer()->getCommandMap()->getCommand($cmd) !== null){
			
				$this->getServer()->getCommandMap()->getCommand($cmd)->setLabel($cmd."__");
				$this->getServer()->getCommandMap()->getCommand($cmd)->unregister($this->getServer()->getCommandMap());
			}
		}
		
		
		$commandMap = $this->getServer()->getCommandMap();
		
		$commandMap->register("say", new SayCmd($this));
		$commandMap->register("tell", new TellCmd($this));
		$commandMap->register("tp", new TpCmd($this));
		$commandMap->register("time", new TimeCmd($this));
		$commandMap->register("list", new ListCmd($this));
		
		$this->getLogger()->notice("ПЛАГИН ВЫКЛЮЧЁН!");
		
	}
}
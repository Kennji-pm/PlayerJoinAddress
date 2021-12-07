<?php

namespace PlayerJoinAddress;

use pocketmine\{Server, Player};
use pocketmine\plugin\PluginBase;
use pocketmine\command\{CommandSender, Command};
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};

class Main extends PluginBase implements Listener {
	
  protected $DeviceOS;
  protected $PlayerData;
  
  public $join, $quit;

  const CONFIG_VERSION = 1.0;
  
  public function onEnable(): void {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->saveDefaultConfig();
    @mkdir($this->getDataFolder());
    $this->getServer()->getLogger()->info("Plugin PlayerJoinAddress Has Enabled");
  }
  public function onPacketReceived(DataPacketReceiveEvent $receiveEvent) {
        $pk = $receiveEvent->getPacket();
        if($pk instanceof LoginPacket) {
            $this->PlayerData[$pk->username] = $pk->clientData;
        }
  }
  public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $name = $player->getName();
    $ip = $player->getAddress();
    $time = date("D d/m/Y(A)");
    $timecfg = date("H:i:s");
    $cdata = $this->PlayerData[$player->getName()];
    $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "tvOS", "Dedicated", "Orbis", "PS4", "Nintendo Switch", "Xbox One"];
    
    $message = "§e{$time}: {$name}(IP:{$ip}, OS: ".$os[$cdata["DeviceOS"]].") Connected to the game";
    $this->getLogger()->info($message);
    if($this->getConfig()->get("Setting")["saveDataJoin"] == true){
        $this->join = new Config($this->getDataFolder() . "PlayerJoin.txt", Config::ENUM);
        $this->join->set("[{$timecfg}] {$time}: {$name}(IP:{$ip}, OS: ".$os[$cdata["DeviceOS"]].") Connected to the game");
        $this->join->save();
    }
    foreach($this->getServer()->getOnlinePlayers() as $player){
      if($player->isOp()){
          $player->sendMessage($message);
      }
    }
  }
  public function onQuit(PlayerQuitEvent $event){
    $player = $event->getPlayer();
    $name = $player->getName();
    $ip = $player->getAddress();
    $time = date("D d/m/Y(A)");
    $timecfg = date("H:i:s");
    
    $message = "§e{$time}: {$name}(IP:{$ip}) Left the game";
    $this->getLogger()->info($message);
    if($this->getConfig()->get("Setting")["saveDataQuit"] == true){
        $this->quit = new Config($this->getDataFolder() . "PlayerQuit.txt", Config::ENUM);
        $this->quit->set("[{$timecfg}] {$time}: {$name}(IP:{$ip}) Left the game");
        $this->quit->save();
    }
    foreach($this->getServer()->getOnlinePlayers() as $player){
      if($player->isOp()){
          $player->sendMessage($message);
      }
    }
  }
}

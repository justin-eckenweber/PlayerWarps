<?php

namespace SchdowNVIDIA\PlayerWarps;



use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    public function onEnable()
    {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder().'/pwarps');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $createPrice = $this->getConfig()->getNested("price.create");
        $deletePrice = $this->getConfig()->getNested("price.delete");
        $newposPrice = $this->getConfig()->getNested("price.newpos");
        $name = $sender->getName();
        $economy = EconomyAPI::getInstance();
        if($command->getName() == "pwarp") {
            if(!isset($args[0])) {
                $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about pwarp.");
                return true;
            }
            switch($args[0]) {
                case "create":
                    if(!isset($args[1])) {
                        $sender->sendMessage("§cWRONG USAGE: §fRun /pwarp help to get help about pwarp.");
                        return;
                    }
                    $pwarpname = $args[1];
                    $money = $economy->myMoney($name);
                    if($money < $createPrice) {
                        $sender->sendMessage("§cYou don't have enough money to create a pwarp! You need " . $createPrice . "$ to create one!");
                        return true;
                    }
                    if(file_exists($this->getDataFolder() . "/pwarps/" . $pwarpname . ".yml")) {
                        $sender->sendMessage("§cERROR: §fThere is already a pwarp with the name" . $pwarpname . "!");
                        return true;
                    }
                    $economy->reduceMoney($name, $createPrice);
                    $x = $sender->getX();
                    $y = $sender->getY();
                    $z = $sender->getZ();
                    $world = $sender->getLevel()->getName();
                    $newPWARP = new Config($this->getDataFolder() . "/pwarps/" . $pwarpname . ".yml", Config::YAML);
                    $newPWARP->set("owner", strtolower($name));
                    $newPWARP->set("x", $x);
                    $newPWARP->set("y", $y);
                    $newPWARP->set("z", $z);
                    $newPWARP->set("world", $world);
                    $newPWARP->save();
                    $sender->sendMessage("§c§8[§aPlayerWarps§8] The pwarp §b" . $pwarpname ." §fhas been successfully created!");
                    return true;
                case "help":
                    break;
                default:
                    $pwarpname = $args[0];
                    if(!file_exists($this->getDataFolder() . "/pwarps/" . $pwarpname . ".yml")) {
                        $sender->sendMessage("§cERROR: §fThere is no pwarp with the name" . $pwarpname . "!");
                        return true;
                    }
                    $PWARP = new Config($this->getDataFolder() . "./pwarps/" . $pwarpname . ".yml", Config::YAML);
                    $x = $PWARP->get("x");
                    $y = $PWARP->get("y");
                    $z = $PWARP->get("z");
                    $world = $PWARP->get("world");
                    $sender->teleport($this->getServer()->getLevelByName($world)->getSafeSpawn());
                    $sender->teleport(new Vector3($x, $y, $z));
                    return true;
            }
        }
    }
}
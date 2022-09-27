<?php

declare(strict_types = 1);

namespace EssentialsPE\Commands;

use EssentialsPE\BaseFiles\BaseAPI;
use EssentialsPE\BaseFiles\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Hat extends BaseCommand{
    /**
     * @param BaseAPI $api
     */
    public function __construct(BaseAPI $api){
        parent::__construct($api, "hat", "Get some new cool headgear", "[remove]", false, ["head"]);
        $this->setPermission("essentials.hat");
    }

	/**
	 * @param CommandSender $sender
	 * @param string        $alias
	 * @param array         $args
	 *
	 * @return bool
	 */
    public function execute(CommandSender $sender, string $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        if(!$sender instanceof Player){
            $this->sendUsage($sender, $alias);
            return false;
        }
        $remove = false;
        if(isset($args[0])){
            if($args[0] === "remove"){
                $remove = true;
            }else{
                $this->sendUsage($sender, $alias);
                return false;
            }
        }
        $new = ItemFactory::getInstance()->get(ItemIds::AIR);
        $old = $sender->getArmorInventory()->getHelmet();
        $slot = $sender->getInventory()->canAddItem($old) ? $sender->getInventory()->firstEmpty() : null;
        if(!$remove){
            $new = $sender->getInventory()->getItemInHand();
            if($new->getId() === ItemIds::AIR){
                $sender->sendMessage(TextFormat::RED . "[Error] Please specify an item to wear");
                return false;
            }
            $slot = $sender->getArmorInventory()->getHelmet();
        }
        $sender->getArmorInventory()->setHelmet($new);
        if($slot !== null){
            $sender->getInventory()->setItem($slot, $old);
        }
        $sender->sendMessage(TextFormat::AQUA . ($new->getId() === ItemIds::AIR ? "Hat removed!" : "You got a new hat!"));
        return true;
    }
}
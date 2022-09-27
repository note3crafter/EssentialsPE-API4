<?php

declare(strict_types = 1);

namespace EssentialsPE\Commands;

use EssentialsPE\BaseFiles\BaseAPI;
use EssentialsPE\BaseFiles\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Speed extends BaseCommand{

    public function __construct(BaseAPI $api){
        parent::__construct($api, "speed", "Change your speed limit", "<speed> [player]");
        $this->setPermission("essentials.speed");
    }

	/**
	 * @param CommandSender $sender
	 * @param string        $alias
	 * @param array         $args
	 *
	 * @return bool
	 */
    public function execute(CommandSender $sender, string $alias, array $args): bool{
        if($this->testPermission($sender)){
            return false;
        }
        if(!$sender instanceof Player || count($args) < 1){
            $this->sendUsage($sender, $alias);
            return false;
        }
        if(!is_numeric($args[0])){
            $sender->sendMessage(TextFormat::RED . "[Error] Please provide a valid value");
            return false;
        }
        $player = $sender;
        if(isset($args[1]) && !($player = $this->getAPI()->getPlayer($args[1]))){
            $sender->sendMessage(TextFormat::RED . "[Error] Player not found");
            return false;
        }
        if((int) $args[0] === 0) {
            $player->getEffects()->remove(VanillaEffects::SPEED());
        } elseif ((int)$args[0] > 255) {
            $player->sendMessage(TextFormat::RED . "[Error] Use /speed [0-255]");
            return false;
        } else {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), (2147483647), ($args[0]), false));
        }
        $sender->sendMessage(TextFormat::YELLOW . "Speed amplified by " . TextFormat::WHITE . $args[0]);
        return true;
    }
}
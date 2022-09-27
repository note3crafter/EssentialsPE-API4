<?php

declare(strict_types = 1);

namespace EssentialsPE;

use EssentialsPE\BaseFiles\BaseAPI;
use EssentialsPE\BaseFiles\BaseCommand;

use EssentialsPE\Commands\{
    AFK,
    Antioch,
    Back,
    BreakCommand,
    Broadcast,
    Burn,
    ClearInventory,
    Compass,
    Condense,
    Depth,
    Feed,
    Extinguish,
    Fly,
    GetPos,
    God,
    Heal,
    Home\DelHome,
    Home\Home,
    Home\SetHome,
    ItemCommand,
    ItemDB,
    Jump,
    KickAll,
    Lightning,
    More,
    Mute,
    Near,
    Nick,
    Nuke,
    Override\Gamemode,
    Override\Kill,
    Override\Msg,
    Ping,
    PowerTool\PowerTool,
    PowerTool\PowerToolToggle,
    PTime,
    PvP,
    RealName,
    Repair,
    Reply,
    Seen,
    SetSpawn,
    Spawn,
    Speed,
    Sudo,
    Suicide,
    TempBan,
    Top,
    Unlimited,
    Vanish,
    Warp\DelWarp,
    Warp\Setwarp,
    Warp\Warp,
    Whois,
    World
};

use EssentialsPE\Commands\Teleport{
    TPA,
    TPAccept,
    TPAHere,
    TPAll,
    TPDeny,
    TPAHere
};

use EssentialsPE\EventHandlers\{
    OtherEvents,
    PlayerEvents,
    SignEvents
};

use EssentialsPE\Events\CreateAPIEvent;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

use JackMD\UpdateNotifier\UpdateNotifier;

class Loader extends PluginBase{

    /** @var BaseAPI */
    private BaseAPI $api;
    /** @var string */
    private const version = "0.0.4";

	public function onLoad() : void {
		// Before anything else...
		$this->checkConfig();
		UpdateNotifier::checkUpdate($this, $this->getDescription()->getName(), $this->getDescription()->getVersion());
	}

    public function onEnable() : void {
        // Custom API Setup :3
        $ev = new CreateAPIEvent($this, BaseAPI::class);
	    try{
		    $ev->call();
	    }
	    catch(\ReflectionException $exception){
	    	$this->getLogger()->logException($exception);
	    }
	    $class = $ev->getClass();
        $this->api = new $class($this);

        // Other startup code...
        if(!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }

        $this->registerEvents();
        $this->registerCommands();
        if(count($p = $this->getServer()->getOnlinePlayers()) > 0){
            $this->getAPI()->createSession($p);
        }
        $this->getAPI()->scheduleAutoAFKSetter();
    }

    public function onDisable() : void {
        if(count($l = $this->getServer()->getOnlinePlayers()) > 0){
            $this->getAPI()->removeSession($l);
        }
        $this->getAPI()->close();
    }

    /**
     * Function to register all the Event Handlers that EssentialsPE provide
     */
    public function registerEvents() : void {
        $this->getServer()->getPluginManager()->registerEvents(new OtherEvents($this->getAPI()), $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerEvents($this->getAPI()), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignEvents($this->getAPI()), $this);
    }

    /**
     * Function to register all EssentialsPE's commands...
     * And to override some default ones
     */
    private function registerCommands(): void{
        $commands = [
            new AFK($this->getAPI()),
            new Antioch($this->getAPI()),
            new Back($this->getAPI()),
            //new BigTreeCommand($this->getAPI()), TODO
            new BreakCommand($this->getAPI()),
            new Broadcast($this->getAPI()),
            new Burn($this->getAPI()),
            new ClearInventory($this->getAPI()),
            new Compass($this->getAPI()),
            new Condense($this->getAPI()),
            new Depth($this->getAPI()),
            new Extinguish($this->getAPI()),
            new Fly($this->getAPI()),
            new GetPos($this->getAPI()),
            new God($this->getAPI()),
            //new Hat($this->getAPI()), TODO: Implement when MCPE implements "Block-Hat rendering"
            new Heal($this->getAPI()),
            new ItemCommand($this->getAPI()),
            new ItemDB($this->getAPI()),
            new Jump($this->getAPI()),
            new KickAll($this->getAPI()),
            new Lightning($this->getAPI()),
            new More($this->getAPI()),
            new Mute($this->getAPI()),
            new Near($this->getAPI()),
            new Nick($this->getAPI()),
            new Nuke($this->getAPI()),
            new Ping($this->getAPI()),
            new Feed($this->getAPI()),
            new PTime($this->getAPI()),
            new PvP($this->getAPI()),
            new RealName($this->getAPI()),
            new Repair($this->getAPI()),
            new Seen($this->getAPI()),
            new SetSpawn($this->getAPI()),
            new Spawn($this->getAPI()),
            new Speed($this->getAPI()),
            new Sudo($this->getAPI()),
            new Suicide($this->getAPI()),
            new TempBan($this->getAPI()),
            new Top($this->getAPI()),
            //new TreeCommand($this->getAPI()), TODO
            new Unlimited($this->getAPI()),
            new Vanish($this->getAPI()),
            new Whois($this->getAPI()),
            new World($this->getAPI()),

            // Messages
            new Msg($this->getAPI()),
            new Reply($this->getAPI()),

            // Override
            new Gamemode($this->getAPI()),
            new Kill($this->getAPI())
		];

		$homeCommands = [
	        new DelHome($this->getAPI()),
	        new Home($this->getAPI()),
	        new SetHome($this->getAPI())
		];

		$powertoolCommands = [
	        new PowerTool($this->getAPI()),
			new PowerToolToggle($this->getAPI())
		];

		$teleportCommands = [
	        new TPA($this->getAPI()),
	        new TPAccept($this->getAPI()),
	        new TPAHere($this->getAPI()),
	        new TPAll($this->getAPI()),
	        new TPDeny($this->getAPI()),
	        new TPHere($this->getAPI())
		];

		$warpCommands = [
	        new DelWarp($this->getAPI()),
	        new Setwarp($this->getAPI()),
	        new Warp($this->getAPI())
		];


		if($this->getServer()->getPluginManager()->getPlugin("SimpleWarp") === null) {
	            foreach($warpCommands as $warpCommand) {
		        if($this->getConfig()->get("warps") === true) {
			    $commands[] = $warpCommand;
		        }
		    }
	    } else {
	        $this->getLogger()->info(TextFormat::YELLOW . "SimpleWarp installed, disabling EssentialsPE warps...");
	    }

		foreach($teleportCommands as $teleportCommand) {
		    if($this->getConfig()->get("teleporting") === true) {
			 $commands[] = $teleportCommand;
		    }
		}

		foreach($powertoolCommands as $powertoolCommand) {
		    if($this->getConfig()->get("powertool") === true) {
			 $commands[] = $powertoolCommand;
		    }
		}

		foreach($homeCommands as $homeCommand) {
		    if($this->getConfig()->get("homes") === true) {
			 $commands[] = $homeCommand;
		    }
		}

        $aliased = [];
        foreach($commands as $cmd){
            /** @var BaseCommand $cmd */
            $commands[$cmd->getName()] = $cmd;
            $aliased[$cmd->getName()] = $cmd->getName();
            foreach($cmd->getAliases() as $alias){
                $aliased[$alias] = $cmd->getName();
            }
        }
        $cfg = $this->getConfig()->get("commands", []);
        foreach($cfg as $del){
            if(isset($aliased[$del])){
                unset($commands[$aliased[$del]]);
            }else{
                $this->getLogger()->debug("\"$del\" command not found inside EssentialsPE, skipping...");
            }
        }
        $this->getServer()->getCommandMap()->registerAll("EssentialsPE", $commands);
    }

    public function checkConfig(): void{
        if(!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
        if(!file_exists($this->getDataFolder() . "config.yml")){
            $this->saveDefaultConfig();
        }
        $this->saveResource("Warps.yml");
        $cfg = $this->getConfig();

        if(!$cfg->exists("version") || $cfg->get("version") !== self::version){
            $this->getLogger()->debug(TextFormat::RED . "An invalid config file was found, generating a new one...");
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml.old");
            $this->saveDefaultConfig();
            $cfg = $this->getConfig();
        }

        $booleans = ["enable-custom-colors"];
        foreach($booleans as $key){
            $value = null;
            if(!$cfg->exists($key) || !is_bool($cfg->get($key))){
                switch($key){
                    // Properties to auto set true
                    case "safe-afk":
                        $value = true;
                        break;
                    // Properties to auto set false
                    case "enable-custom-colors":
                        $value = false;
                        break;
                }
            }
            if($value !== null){
                $cfg->set($key, $value);
            }
        }

        $integers = ["oversized-stacks", "near-radius-limit", "near-default-radius"];
        foreach($integers as $key){
            $value = null;
            if(!is_numeric($cfg->get($key))){
                switch($key){
                    case "auto-afk-kick":
                        $value = 300;
                        break;
                    case "oversized-stacks":
                        $value = 64;
                        break;
                    case "near-radius-limit":
                        $value = 200;
                        break;
                    case "near-default-radius":
                        $value = 100;
                        break;
                }
            }
            if($value !== null){
                $cfg->set($key, $value);
            }
        }

        $afk = ["safe", "auto-set", "auto-broadcast", "auto-kick", "broadcast"];
        foreach($afk as $key){
            $value = null;
            $k = $this->getConfig()->getNested("afk." . $key);
            switch($key){
                case "safe":
                case "auto-broadcast":
                case "broadcast":
                    if(!is_bool($k)){
                        $value = true;
                    }
                    break;
                case "auto-set":
                case "auto-kick":
                    if(!is_int($k)){
                        $value = 300;
                    }
                    break;
            }
            if($value !== null){
                $this->getConfig()->setNested("afk." . $key, $value);
            }
        }
    }

    /**
     * @return BaseAPI
     */
    public function getAPI(): BaseAPI{
        return $this->api;
    }
}

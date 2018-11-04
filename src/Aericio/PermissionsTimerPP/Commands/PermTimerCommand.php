<?php

declare(strict_types=1);

namespace Aericio\PermissionsTimerPP\Commands;

use Aericio\PermissionsTimerPP\PermissionsTimerPP;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

/**
 * Class PermTimerCommand
 * @package Aericio\PermissionsTimerPP\Commands
 */
class PermTimerCommand extends PluginCommand
{

    /**
     * RankTimerCommand constructor.
     * @param string             $name
     * @param PermissionsTimerPP $plugin
     */
    public function __construct(string $name, PermissionsTimerPP $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription('Timed permissions');
        $this->setAliases(['ptpp']);
        $this->setUsage('/permtimer <help|about|add|remove|set|time>');
        $this->setPermission('permissionstimerpp.command.permtimer');
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     * @return bool|mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof PermissionsTimerPP) {
            $database = $plugin->getDatabase();
            $perms = TF::RED . "You do not have permission to use this command!";
            if (isset($args[0])) {
                switch ($args[0]) {
                    case 'help':
                        if (!$sender->hasPermission("permissionstimerpp.command.ranktimer.help")) {
                            $sender->sendMessage($perms);
                            return;
                        }
                        $sender->sendMessage(TF::RED . '[PTPP] /permtimer <help|about|add|remove|set|time>');
                        return;
                    case 'about':
                        if (!$sender->hasPermission("permissionstimerpp.command.ranktimer.about")) {
                            $sender->sendMessage($perms);
                            return;
                        }
                        $sender->sendMessage(TF::GREEN . 'Currently running PermissionsTimerPP (PTPP) v' . $plugin->getDescription()->getVersion() . ' by Aericio.');
                        return;
                    case 'add':
                        if (!$sender->hasPermission("permissionstimerpp.command.ranktimer.add")) {
                            $sender->sendMessage($perms);
                            return;
                        }
                        if (!isset($args[1], $args[2], $args[3])) {
                            $sender->sendMessage(TF::RED . 'Usage: /permtimer add <player> <permission> <time>');
                            return;
                        }
                        var_dump("ADD " . $args[1], $args[2], $args[3]);
                        $player = $plugin->getServer()->getPlayerExact($args[1]);
                        if ($player instanceof Player && $player->isOnline()) {
                            if (is_null($database->getPlayer($player))) {
                                $sender->sendMessage(TF::RED . 'User could not be found in the database!');
                                return;
                            } else {
                                $database->addPermissionTime($player, $args[2], intval($args[3]));
                                $sender->sendMessage(TF::GREEN . 'Successfully added ' . $args[3] . ' seconds for ' . $args[2] . ' for ' . $player->getName());
                            }
                        } else {
                            $sender->sendMessage(TF::RED . 'Error: player must be online to add time!');
                        }
                        return;
                    case 'remove':
                        if (!$sender->hasPermission("permissionstimerpp.command.ranktimer.remove")) {
                            $sender->sendMessage($perms);
                            return;
                        }
                        if (!isset($args[1], $args[2], $args[3])) {
                            $sender->sendMessage(TF::RED . 'Usage: /permtimer remove <player> <permission> <time>');
                            return;
                        }
                        var_dump("REMOVE " . $args[1], $args[2], intval($args[3]));
                        $player = $plugin->getServer()->getPlayerExact($args[1]);
                        if ($player instanceof Player && $player->isOnline()) {
                            if (is_null($database->getPlayer($player))) {
                                $sender->sendMessage(TF::RED . 'User could not be found in the database!');
                                return;
                            } else {
                                $database->removePermissionTime($player, $args[2], $args[3]);
                                $sender->sendMessage(TF::GREEN . 'Successfully removed ' . $args[3] . ' seconds for ' . $args[2] . ' for ' . $player->getName());
                            }
                        } else {
                            $sender->sendMessage(TF::RED . 'Error: player must be online to remove time!');
                        }
                        return;
                    case 'set':
                        if (!$sender->hasPermission("permissionstimerpp.command.ranktimer.set")) {
                            $sender->sendMessage($perms);
                            return;
                        }
                        if (!isset($args[1], $args[2], $args[3])) {
                            $sender->sendMessage(TF::RED . 'Usage: /permtimer set <player> <permission> <time>');
                            return;
                        }
                        var_dump("SET " . $args[1], $args[2], $args[3]);
                        $player = $plugin->getServer()->getPlayerExact($args[1]);
                        if ($player instanceof Player && $player->isOnline()) {
                            $database->setPermissionTime($player, $args[2], intval($args[3]));
                            $plugin->getPurePerms()->getUserDataMgr()->setNode($player, $args[2], 0);
                            $sender->sendMessage(TF::GREEN . 'Successfully set ' . $args[3] . ' seconds for ' . $args[2] . ' for ' . $player->getName());
                        } else {
                            $sender->sendMessage(TF::RED . 'Error: player must be online to set time!');
                        }
                        return;
                    case 'time':
                        if (!$sender->hasPermission("permissionstimerpp.command.ranktimer.time")) {
                            $sender->sendMessage($perms);
                            return;
                        }
                        if (!isset($args[1], $args[2])) {
                            $sender->sendMessage(TF::RED . 'Usage: /permtimer time <player> <permission>');
                            return;
                        }
                        var_dump("TIME " . $args[1], $args[2]);
                        $player = $plugin->getServer()->getPlayerExact($args[1]);
                        if ($player instanceof Player && $player->isOnline()) {
                            $online = $database->getPermissionsTime($player, $args[2]);
                            if (is_null($online)) {
                                $sender->sendMessage(TF::RED . 'User could not be found in the database!');
                                return;
                            } else {
                                $time = $online - time();
                                $days = floor($time / 86400);
                                $hours = floor($time / 3600 - ($days * 24));
                                $minutes = floor($time / 60 - (($hours + ($days * 24)) * 60));
                                $seconds = floor($time - (($minutes + (($hours + ($days * 24)) * 60)) * 60));
                                var_dump($online - time());
                                $sender->sendMessage(TF::GREEN . $player->getName() . ' has ' . $days . ':' . $hours . ':' . $minutes . ':' . $seconds . ' remaining for permission ' . $args[2]);
                            }
                        } elseif (is_null($offline = $database->getOfflinePermissionsTime($args[1], $args[2]))) {
                            $sender->sendMessage(TF::RED . 'User could not be found in the database!');
                            return;
                        } else {
                            $time = $offline - time();
                            $days = floor($time / 86400);
                            $hours = floor($time / 3600 - ($days * 24));
                            $minutes = floor($time / 60 - (($hours + ($days * 24)) * 60));
                            $seconds = floor($time - (($minutes + (($hours + ($days * 24)) * 60)) * 60));
                            $sender->sendMessage(TF::GREEN . $args[1] . ' has ' . $days . ':' . $hours . ':' . $minutes . ':' . $seconds . ' remaining for permission ' . $args[2]);
                        }
                        return;
                    default:
                        $sender->sendMessage(TF::RED . 'Usage: /permtimer <help|about|add|remove|set|time>');
                        return;
                }
            } else {
                $sender->sendMessage(TF::RED . 'Usage: /permtimer <help|about|add|remove|set|time>');
            }
        }
        return;
    }
}
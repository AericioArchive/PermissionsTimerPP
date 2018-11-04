<?php

declare(strict_types=1);

namespace Aericio\PermissionsTimerPP\Tasks;

use Aericio\PermissionsTimerPP\PermissionsTimerPP;
use pocketmine\scheduler\Task;

/**
 * Class RemovePermissionTask
 * @package Aericio\PermissionsTimerPP\Tasks
 */
class RemovePermissionTask extends Task
{

    /** @var PermissionsTimerPP */
    private $plugin;

    /**
     * CheckTimeTask constructor.
     * @param PermissionsTimerPP $plugin
     */
    public function __construct(PermissionsTimerPP $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        $plugin = $this->plugin;
        $database = $plugin->getDatabase();
        foreach ($plugin->getServer()->getOnlinePlayers() as $player) {
            if (is_null($database->getPlayer($player))) continue;
            foreach ($database->getPermissions($player) as $permissionName => $permission) {
                $time = $database->getPermissionsTime($player, $permissionName);
                if (is_null($time)) continue;
                if (time() > $time) {
                    $plugin->getPurePerms()->getUserDataMgr()->removeNode($player, $permissionName);
                    $database->setPermissionTime($player, $permissionName, 0);
                    if ($database->getPermissionsTime($player, $permissionName) === 0) {
                        $database->deleteEntry($player, $permissionName);
                    }
                    return;
                }
            }
        }
    }
}
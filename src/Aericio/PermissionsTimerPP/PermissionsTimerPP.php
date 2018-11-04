<?php

declare(strict_types=1);

namespace Aericio\PermissionsTimerPP;

use _64FF00\PurePerms\PurePerms;
use Aericio\PermissionsTimerPP\Database\SQLite3;
use Aericio\PermissionsTimerPP\Tasks\RemovePermissionTask;
use pocketmine\plugin\PluginBase;

/**
 * Class PermissionsTimerPP
 * @package Aericio\PermissionsTimerPP
 */
class PermissionsTimerPP extends PluginBase
{

    /** @var SQLite3 */
    public $db;

    public function onEnable(): void
    {
        if (is_null($this->getPurePerms())) {
            $this->getLogger()->alert('PurePerms could not be found! Disabling...');
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        $this->saveDefaultConfig();
        $this->db = new SQLite3($this);
        $this->getServer()->getCommandMap()->register('permtimer', new Commands\PermTimerCommand('permtimer', $this));
        $this->getScheduler()->scheduleRepeatingTask(new RemovePermissionTask($this), intval($this->getConfig()->get('check-tick-rate')));
    }

    /**
     * @return null|PurePerms
     */
    public function getPurePerms(): ?PurePerms
    {
        $pureperms = $this->getServer()->getPluginManager()->getPlugin('PurePerms');
        if ($pureperms instanceof PurePerms) {
            return $pureperms;
        } else {
            return null;
        }
    }

    /**
     * @return SQLite3
     */
    public function getDatabase(): SQLite3
    {
        return $this->db;
    }
}

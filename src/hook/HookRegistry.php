<?php
namespace SuiteMapper\Hook;

/**
 * Class HookRegistry
 * @package SuiteMapper\Hook
 */
class HookRegistry
{
    const EXEC_TYPE_PRE = 'pre';
    const EXEC_TYPE_POST = 'post';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $registeredHooks = [
        self::EXEC_TYPE_PRE => [],
        self::EXEC_TYPE_POST => []
    ];

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;

        /** @var Hook $hook */
        foreach ($this->getAllHooks() as $hook) {
            if (!isset($this->registeredHooks[$hook->getExecType()][$hook->getSyncType()])) {
                $this->registeredHooks[$hook->getExecType()][$hook->getSyncType()] = [];
            }
            $this->registeredHooks[$hook->getExecType()][$hook->getSyncType()][] = $hook;
        }
    }

    public function executeHooksBySyncType($syncType, $execType, array &$data)
    {
        if (empty($this->registeredHooks[$execType][$syncType])) {
            return true;
        }

        /** @var Hook $hook */
        foreach ($this->registeredHooks[$execType][$syncType] as $hook) {
            $hook->execute($data);
        }
    }

    /**
     * @return array
     */
    public function getAllHooks()
    {
        return [
            new CategoryProviderHook($this->pdo),
        ];
    }
}
<?php


namespace Hyper\Application;


use Closure;
use Hyper\Exception\HyperHttpException;

/**
 * @Annotation
 * Class MiddleWare
 * @package hyper\Application
 */
class MiddleWare
{
    /**
     * @param $roles
     * @param Closure $callback
     */
    public static function protectFrom($roles, Closure $callback)
    {

    }

    /**
     * @assert($roles !== null)
     * @param string|array $roles
     * @param Closure $callback
     */
    public static function allow($roles, Closure $callback)
    {
        foreach (self::getRoles($roles) as $role)
            if (MiddleWare::role($role))
                $callback();
            else (new HyperHttpException())->notAuthorised();
    }

    /**
     * @param $role
     * @return array
     */
    private static function getRoles($role): array
    {
        if (is_string($role)) return explode("|", $role);
        if (is_array($role)) return $role;
        return null;
    }

    /**
     * @param $role
     * @return bool
     */
    public static function role($role): bool
    {
        return true;
    }
}
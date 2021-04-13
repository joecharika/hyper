<?php


namespace Hyper\Application\api;


use Hyper\{Application\Authorization,
    Models\Claim,
    Models\Token,
    Models\User,
    SQL\Database\DatabaseContext,
    Utils\General,
    Utils\Generator};

class HyperApiAuthController
{
    private DatabaseContext $users, $claims;

    public function __construct()
    {
        $this->users = new DatabaseContext(User::class);
        $this->claims = new DatabaseContext(Claim::class);
    }

    /**
     * @param $username
     * @param $password
     * @return Token|string
     */
    public function login($username, $password)
    {
        $user = $user ?? $this->users->first('username', $username);

        if (isset($user)) {
            if ($user->lockedOut) return 'Your account has been disabled. Contact admin for more';

            if (password_verify($password . $user->salt, $user->key)) {
                $newToken = Generator::token($user->id);

                # Create a new login claim
                $update = $this->claims
                    ->add((new Claim)
                        ->setId(uniqid())
                        ->setToken($newToken)
                        ->setUserId($user->id)
                        ->setBrowser(General::browser())
                        ->setIPAddress(General::ipAddress())
                        ->setState(true)
                    );

                # Check if the update was accepted or not
                if ($update) {
                    return new Token($newToken, $user);
                }
            } else return 'Password is incorrect';
        }

        return 'User is not registered';
    }

    public function register($username, $password, $role = 'default')
    {
        $reg = (new Authorization)->register($username, $password, $role);

        return is_string($reg)
            ? $reg
            : $this->token($username, $password);
    }

    public function check($token): bool
    {
        $claim = $this->claims->first('token', $token);

        return $claim instanceof Claim;
    }

    public function logout($token)
    {
        return $this->claims->delete($this->claims->first('token', $token));
    }
}
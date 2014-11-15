<?php

namespace RKA;

class UserMapper
{
    protected $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function load($username)
    {
        $sql = <<<sql
        SELECT id, username, password, secret
        FROM user
        WHERE username = ?
sql;

        $sth = $this->dbh->prepare($sql);
        $sth->execute([$username]);
        
        $sth->setFetchMode(\PDO::FETCH_CLASS, 'RKA\User');
        $data = $sth->fetch();

        return $data;
    }

    public function save(User $user)
    {
        if ($user->getId()) {
            $sql = <<<sql
                UPDATE user
                SET
                    username = ?,
                    password = ?,
                    secret   = ?
                WHERE id = ?
sql;
            $sth = $this->dbh->prepare($sql);
LDBG( $sth);
            $sth->execute([$user->getUsername(), $user->getPassword(), $user->getSecret(), $user->getId()]);
        } else {
            throw new \Exception("Creating new users is not implemented yet!");
        }
    }
}

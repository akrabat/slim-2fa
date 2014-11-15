<?php

namespace RKA;

class User
{
    protected $id;
    protected $username;
    protected $password;
    protected $secret;

    public function getArrayCopy()
    {
        return [
            'id'       => $this->getId(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'secret'   => $this->getSecret(),
        ];
    }

    // Getter and setter for $this->id
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    // Getter and setter for $this->username
    public function getUsername()
    {
        return $this->username;
    }
    
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    
    // Getter and setter for $this->password
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    // Getter and setter for $this->secret
    public function getSecret()
    {
        return $this->secret;
    }
    
    public function setSecret($secret)
    {
        $this->secret = $secret;
        return $this;
    }
}

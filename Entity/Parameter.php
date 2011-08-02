<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class Parameter
{
    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank
     */
    protected $key;

    /** @ORM\Column(type="string") */
    protected $value;

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}

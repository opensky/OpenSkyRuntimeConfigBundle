<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class Parameter
{
    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected $name;

    /** @ORM\Column(type="string") */
    protected $value;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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

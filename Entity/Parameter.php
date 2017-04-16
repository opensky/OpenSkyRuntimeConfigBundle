<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenSky\Bundle\RuntimeConfigBundle\Model\Parameter as BaseParameter;
use Symfony\Bridge\Doctrine\Validator\Constraints as AssertORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 * @AssertORM\UniqueEntity(groups={"Entity"}, fields={"name"}, message="A parameter with the same name already exists; name must be unique")
 */
class Parameter extends BaseParameter
{
    /**
     * @ORM\Column(type="string")
     * @Assert\Length(groups={"Entity"}, max=255)
     * @Assert\NotBlank(groups={"Entity"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(groups={"Entity"}, max=255)
     */
    protected $value;
}

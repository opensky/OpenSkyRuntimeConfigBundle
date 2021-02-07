<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenSky\Bundle\RuntimeConfigBundle\Model\Parameter as BaseParameter;
use Symfony\Bridge\Doctrine\Validator\Constraints as AssertORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

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

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateValueAsJson(ExecutionContextInterface $context)
    {
        @json_encode($this->value);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $context->buildViolation('This value is not valid JSON')
                ->atPath('value')
                ->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateValueAsYaml(ExecutionContextInterface $context)
    {
        try {
            Yaml::parse($this->value);
        } catch (ParseException $e) {
            $context->buildViolation('This value is not valid YAML syntax')
                ->atPath('value')
                ->addViolation();
        }
    }
}

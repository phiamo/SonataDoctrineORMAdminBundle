<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrineORMAdminBundle\Guesser;

use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * @final since sonata-project/doctrine-orm-admin-bundle 3.24
 */
class TypeGuesser extends AbstractTypeGuesser
{
    /**
     * This is a mapping between the old deprecated value we provided in the TypeGuesser
     * and the value we should use to get the correct SonataAdminBundle template. Making the change
     * directly in the TypeGuesser would be a BC-break if a user overrides the templates config.
     *
     * NEXT_MAJOR: remove this constant.
     *
     * @internal
     */
    public const DEPRECATED_TYPES = [
    ];

    public function guessType(string $class, string $property, ModelManagerInterface $modelManager): ?TypeGuess
    {
        if (!$ret = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return new TypeGuess(TemplateRegistry::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        [$metadata, $propertyName, $parentAssociationMappings] = $ret;

        if ($metadata->hasAssociation($propertyName)) {
            $mapping = $metadata->getAssociationMapping($propertyName);

            switch ($mapping['type']) {
                case ClassMetadata::ONE_TO_MANY:
                    // NEXT_MAJOR: return new TypeGuess(TemplateRegistry::TYPE_ONE_TO_MANY, [], Guess::HIGH_CONFIDENCE)
                    return new TypeGuess('orm_one_to_many', [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY_TO_MANY:
                    // NEXT_MAJOR: return new TypeGuess(TemplateRegistry::TYPE_MANY_TO_MANY, [], Guess::HIGH_CONFIDENCE)
                    return new TypeGuess('orm_many_to_many', [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::MANY_TO_ONE:
                    // NEXT_MAJOR: return new TypeGuess(TemplateRegistry::TYPE_MANY_TO_ONE, [], Guess::HIGH_CONFIDENCE)
                    return new TypeGuess('orm_many_to_one', [], Guess::HIGH_CONFIDENCE);

                case ClassMetadata::ONE_TO_ONE:
                    // NEXT_MAJOR: return new TypeGuess(TemplateRegistry::TYPE_ONE_TO_ONE, [], Guess::HIGH_CONFIDENCE)
                    return new TypeGuess('orm_one_to_one', [], Guess::HIGH_CONFIDENCE);
            }
        }

        switch ($metadata->getTypeOfField($propertyName)) {
            case 'array':
            case 'simple_array':
            case 'json':
            case 'json_array':
                return new TypeGuess(FieldDescriptionInterface::TYPE_ARRAY, [], Guess::HIGH_CONFIDENCE);
            case 'boolean':
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case 'datetime':
            case 'datetime_immutable':
            case 'vardatetime':
            case 'datetimetz':
            case 'datetimetz_immutable':
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case 'date':
            case 'date_immutable':
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'bigint':
            case 'smallint':
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            case 'text':
                return new TypeGuess(FieldDescriptionInterface::TYPE_TEXTAREA, [], Guess::MEDIUM_CONFIDENCE);
            case 'time':
            case 'time_immutable':
                return new TypeGuess(FieldDescriptionInterface::TYPE_TIME, [], Guess::HIGH_CONFIDENCE);
            default:
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }
    }
}

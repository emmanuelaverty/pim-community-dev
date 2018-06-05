<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use PimEnterprise\Component\Security\Model\AttributeGroupAccessInterface;

/**
 * Attribute Group Access entity
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AttributeGroupAccess implements AttributeGroupAccessInterface
{
    /** @var int */
    protected $id;

    /** @var AttributeGroupInterface */
    protected $attributeGroup;

    /** @var GroupInterface */
    protected $userGroup;

    /** @var bool */
    protected $viewAttributes;

    /** @var bool */
    protected $editAttributes;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeGroup()
    {
        return $this->attributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroup(AttributeGroupInterface $attributeGroup)
    {
        $this->attributeGroup = $attributeGroup;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserGroup(GroupInterface $group)
    {
        $this->userGroup = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewAttributes()
    {
        return $this->viewAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewAttributes($viewAttributes)
    {
        $this->viewAttributes = $viewAttributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditAttributes()
    {
        return $this->editAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditAttributes($editAttributes)
    {
        $this->editAttributes = $editAttributes;

        return $this;
    }
}

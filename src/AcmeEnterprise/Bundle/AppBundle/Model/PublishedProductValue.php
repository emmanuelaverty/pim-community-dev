<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmeEnterprise\Bundle\AppBundle\Model;

use Acme\Bundle\AppBundle\Entity\Color;
use Acme\Bundle\AppBundle\Entity\Fabric;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValueInterface;

/**
 * Acme override of the product value
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class PublishedProductValue extends AbstractProductValue implements PublishedProductValueInterface
{
    /** @var ArrayCollection */
    protected $fabrics;

    /** @var array (used only in MongoDB implementation) */
    protected $fabricIds;

    /** @var Color */
    protected $color;

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->fabrics = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getFabrics()
    {
        return $this->fabrics;
    }

    /**
     * @param ArrayCollection $fabrics
     */
    public function setFabrics(ArrayCollection $fabrics)
    {
        $this->fabrics = $fabrics;
    }

    /**
     * @param Fabric $fabric
     */
    public function addFabric(Fabric $fabric)
    {
        $this->fabrics->add($fabric);
    }

    /**
     * @param Fabric $fabric
     */
    public function removeFabric(Fabric $fabric)
    {
        $this->fabrics->removeElement($fabric);
    }

    /**
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param Color $color
     */
    public function setColor(Color $color = null)
    {
        $this->color = $color;
    }
}

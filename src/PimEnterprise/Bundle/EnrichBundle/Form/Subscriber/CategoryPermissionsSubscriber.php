<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;

/**
 * Subscriber to manage permissions on categories
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var CategoryAccessManager */
    protected $accessManager;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var array store the previous roles to be able to do a diff of added/removed */
    protected $previousRoles = ['view' => [], 'edit' => [], 'own' => []];

    /**
     * @param CategoryAccessManager $accessManager
     * @param SecurityFacade        $securityFacade
     */
    public function __construct(CategoryAccessManager $accessManager, SecurityFacade $securityFacade)
    {
        $this->accessManager  = $accessManager;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        );
    }

    /**
     * Add the permissions subform to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $event->getForm()->add('permissions', 'pimee_enrich_category_permissions');
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function postSetData(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $form = $event->getForm()->get('permissions');

        $viewRoles = $this->accessManager->getViewUserGroups($event->getData());
        $form->get('view')->setData($viewRoles);
        $this->previousRoles['view'] = ($viewRoles instanceof ArrayCollection) ? $viewRoles->toArray() : $viewRoles;

        $editRoles = $this->accessManager->getEditUserGroups($event->getData());
        $form->get('edit')->setData($editRoles);
        $this->previousRoles['edit'] = ($editRoles instanceof ArrayCollection) ? $editRoles->toArray() : $editRoles;

        $ownRoles = $this->accessManager->getOwnUserGroups($event->getData());
        $form->get('own')->setData($ownRoles);
        $this->previousRoles['own'] = ($ownRoles instanceof ArrayCollection) ? $ownRoles->toArray() : $ownRoles;
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $form = $event->getForm();
        if ($form->isValid()) {
            $viewRoles = $form->get('permissions')->get('view')->getData();
            $editRoles = $form->get('permissions')->get('edit')->getData();
            $ownRoles = $form->get('permissions')->get('own')->getData();
            $this->accessManager->setAccess($event->getData(), $viewRoles, $editRoles, $ownRoles);

            $updateChildren = $form->get('permissions')->get('apply_on_children')->getData();
            if ($updateChildren === true) {
                $this->updateChildren($event->getData(), $viewRoles, $editRoles, $ownRoles);
            }
        }
    }

    /**
     * Update children categories
     *
     * @param CategoryInterface     $parent
     * @param array|ArrayCollection $viewRoles
     * @param array|ArrayCollection $editRoles
     * @param array|ArrayCollection $ownRoles
     */
    protected function updateChildren(CategoryInterface $parent, $viewRoles, $editRoles, $ownRoles)
    {
        $currentRoles = [];
        $currentRoles['view'] = ($viewRoles instanceof ArrayCollection) ? $viewRoles->toArray() : $viewRoles;
        $currentRoles['edit'] = ($editRoles instanceof ArrayCollection) ? $editRoles->toArray() : $editRoles;
        $currentRoles['own'] = ($ownRoles instanceof ArrayCollection) ? $ownRoles->toArray() : $ownRoles;

        $addedViewRoles = array_diff($currentRoles['view'], $this->previousRoles['view']);
        $addedEditRoles = array_diff($currentRoles['edit'], $this->previousRoles['edit']);
        $addedOwnRoles = array_diff($currentRoles['own'], $this->previousRoles['own']);
        $removedViewRoles = array_diff($this->previousRoles['view'], $currentRoles['view']);
        $removedEditRoles = array_diff($this->previousRoles['edit'], $currentRoles['edit']);
        $removedOwnRoles = array_diff($this->previousRoles['own'], $currentRoles['own']);

        $changedRoles = count($addedViewRoles) > 0 || count($addedEditRoles) > 0 || count($addedOwnRoles) > 0
            || count($removedViewRoles) > 0 || count($removedEditRoles) > 0 || count($removedOwnRoles) > 0;

        if ($changedRoles) {
            $this->accessManager->updateChildrenAccesses(
                $parent,
                $addedViewRoles,
                $addedEditRoles,
                $addedOwnRoles,
                $removedViewRoles,
                $removedEditRoles,
                $removedOwnRoles
            );
        }
    }

    /**
     * Indicates whether the permissions should be added to the form
     *
     * @param FormEvent $event
     *
     * @return boolean
     */
    protected function isApplicable(FormEvent $event)
    {
        return null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_enrich_category_edit_permissions');
    }
}

<?php

namespace PimEnterprise\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\CategoryManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Bundle\EnrichBundle\Controller\ProductController as BaseProductController;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Product Controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductController extends BaseProductController
{
    /**
     * @var UserContext
     */
    protected $userContext;

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_enrich_product_index")
     * @Template
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        try {
            $this->userContext->getAccessibleUserTree();

            return parent::indexAction($request);
        } catch (\LogicException $e) {
            $this->addFlash('error', 'category.permissions.no_access_to_products');

            return $this->redirectToRoute('oro_default');
        }
    }

    /**
     * Dispatch to product view or product edit when a user click on a product grid row
     *
     * @param Request $request
     * @param integer $id
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     *
     * @AclAncestor("pim_enrich_product_edit")
     */
    public function dispatchAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $editProductGranted = $this->securityContext->isGranted(Attributes::EDIT_PRODUCT, $product);
        $locale = $this->userContext->getCurrentLocale();
        $editLocaleGranted = $this->securityContext->isGranted(Attributes::EDIT_PRODUCTS, $locale);

        if ($editProductGranted && $editLocaleGranted) {
            $parameters = $this->editAction($this->request, $id);

            return $this->render('PimEnrichBundle:Product:edit.html.twig', $parameters);

        } elseif ($this->securityContext->isGranted(Attributes::VIEW_PRODUCT, $product)) {
            $parameters = $this->showAction($this->request, $id);

            return $this->render('PimEnrichBundle:Product:show.html.twig', $parameters);
        }

        throw new AccessDeniedException();
    }

    /**
     * Show product
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_enrich_product_edit")
     * @return array
     */
    public function showAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $locale = $this->userContext->getCurrentLocale();
        $viewLocaleGranted = $this->securityContext->isGranted(Attributes::VIEW_PRODUCTS, $locale);
        if (!$viewLocaleGranted) {
            throw new AccessDeniedException();
        }

        return [
            'product'    => $product,
            'dataLocale' => $this->getDataLocale(),
            'locales'    => $this->userContext->getUserLocales(),
            'created'    => $this->versionManager->getOldestLogEntry($product),
            'updated'    => $this->versionManager->getNewestLogEntry($product),
        ];
    }

    /**
     * Show a product value
     *
     * @param Request $request
     * @param string  $productId
     * @param string  $attributeCode
     *
     * @return Response
     */
    public function showAttributeAction(Request $request, $productId, $attributeCode)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $product = $this->findProductOr404($productId);
        $locale = $request->query->get('locale');
        $scope = $request->query->get('scope');

        $value = $product->getValue($attributeCode, $locale, $scope);

        return new Response((string) $value);
    }

    /**
     * Override to get only the granted path for the filled tree
     *
     * {@inheritdoc}
     */
    protected function getFilledTree(CategoryInterface $parent, Collection $categories)
    {
        return $this->categoryManager->getGrantedFilledTree($parent, $categories);
    }

    /**
     * Override to get only the granted count for the granted tree
     *
     * {@inheritdoc}
     */
    protected function getProductCountByTree(ProductInterface $product)
    {
        return $this->productCatManager->getProductCountByGrantedTree($product);
    }
}

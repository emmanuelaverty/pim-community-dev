<?php

namespace Pim\Bundle\DataGridBundle\Normalizer\Product;

use Pim\Component\Catalog\ProductValue\OptionProductValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($option, $format = null, array $context = [])
    {
        $locale = isset($context['data_locale']) ? $context['data_locale'] : null;
        $translation = $option->getData()->getTranslation($locale);
        $label = null !== $translation->getValue() ? $translation->getValue() : sprintf('[%s]', $option->getCode());

        return [
            'locale' => $option->getLocale(),
            'scope'  => $option->getScope(),
            'data'   => $label
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'datagrid' === $format && $data instanceof OptionProductValueInterface;
    }
}

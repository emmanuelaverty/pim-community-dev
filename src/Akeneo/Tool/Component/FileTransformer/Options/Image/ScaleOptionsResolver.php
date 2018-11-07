<?php

declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Component\FileTransformer\Options\Image;

use Akeneo\Tool\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Tool\Component\FileTransformer\Options\TransformationOptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Option resolver for Scale transformation.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ScaleOptionsResolver implements TransformationOptionsResolverInterface
{
    /** @var OptionsResolver */
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver->setDefined(['ratio', 'width', 'height']);
        $this->resolver->setAllowedTypes('ratio', ['int', 'null']);
        $this->resolver->setAllowedTypes('width', ['int', 'null']);
        $this->resolver->setAllowedTypes('height', ['int', 'null']);
        $this->resolver->setDefaults(['ratio' => null, 'width' => null, 'height' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(array $options)
    {
        try {
            $options = $this->resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, 'scale');
        }

        $ratio = $options['ratio'];
        $width = $options['width'];
        $height = $options['height'];

        if (null === $ratio && null === $width && null === $height) {
            throw InvalidOptionsTransformationException::chooseOneOption(['ratio', 'width', 'height'], 'scale');
        }

        if (null !== $ratio && ($ratio < 0 || $ratio > 100)) {
            throw InvalidOptionsTransformationException::ratio('ratio', 'scale');
        }

        return $options;
    }
}
<?php

namespace DamEnterprise\Component\Transformer\Transformation\Image;

use DamEnterprise\Component\Transformer\Exception\InvalidOptionsTransformationException;
use DamEnterprise\Component\Transformer\Exception\NotApplicableTransformationException;
use DamEnterprise\Component\Transformer\Transformation\AbstractTransformation;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Resize extends AbstractTransformation
{
    //TODO: the list of mimetypes is defined here
    // vendor/symfony/symfony/src/Symfony/Component/HttpFoundation/File/MimeType/MimeTypeExtensionGuesser.php
    public function __construct(array $mimeTypes = ['image/jpeg', 'image/tiff'])
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function transform(\SplFileInfo $file, array $options = [])
    {
        $options = $this->checkOptions($options);

        $imagine = new Imagine();
        $image   = $imagine->open($file->getPathname());

        if ($options['width'] > $image->getSize()->getWidth()) {
            throw NotApplicableTransformationException::imageWidthTooBig($file->getPathname(), $this->getName());
        } elseif ($options['height'] > $image->getSize()->getHeight()) {
            throw NotApplicableTransformationException::imageHeightTooBig($file->getPathname(), $this->getName());
        }

        $image->resize(new Box($options['width'], $options['height']));
        $image->save();
    }

    public function getName()
    {
        return 'resize';
    }

    protected function checkOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['width', 'height']);
        $resolver->setAllowedTypes(['width' => 'int', 'height' => 'int']);

        try {
            $options = $resolver->resolve($options);
        } catch (\Exception $e) {
            throw InvalidOptionsTransformationException::general($e, $this->getName());
        }

        return $options;
    }
}

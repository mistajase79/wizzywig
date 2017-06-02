<?php
namespace Prototype\PageBundle\Conversion;

use Doctrine\Common\Annotations\Reader;

class ProtoCmsComponentConverter
{
    private $reader;
    private $annotationClass = 'Prototype\\PageBundle\\Annotation\\ProtoCmsComponent';

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function convert($originalObject)
    {
        $convertedObject = new \stdClass;

        $reflectionObject = new \ReflectionObject($originalObject);

        foreach ($reflectionObject->getMethods() as $reflectionMethod) {
            // fetch the @StandardObject annotation from the annotation reader
            $annotation = $this->reader->getMethodAnnotation($reflectionMethod, $this->annotationClass);
            if (null !== $annotation) {
                $propertyName = $annotation->getPropertyName();

                // retrieve the value for the property, by making a call to the method
                $value = $reflectionMethod->invoke($originalObject);

                // try to convert the value to the requested type
                $type = $annotation->getComponentType();
                if (false === settype($value, $type)) {
                    throw new \RuntimeException(sprintf('PCGC Could not convert value to type "%s"', $value));
                }

                $convertedObject->$propertyName = $value;
            }
        }

        return $convertedObject;
    }
}

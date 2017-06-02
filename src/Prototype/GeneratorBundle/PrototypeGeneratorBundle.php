<?php

namespace Prototype\GeneratorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Symfony\Component\Console\Application;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\Filesystem\FileSystem;

class PrototypeGeneratorBundle extends Bundle
{

	public function getParent()
	{
		return 'SensioGeneratorBundle';
	}
}

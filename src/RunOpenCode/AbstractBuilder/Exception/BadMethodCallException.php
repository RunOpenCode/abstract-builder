<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Exception;

use RunOpenCode\AbstractBuilder\Contract\ExceptionInterface;

/**
 * Class BadMethodCallException
 *
 * @package RunOpenCode\AbstractBuilder\Exception
 */
class BadMethodCallException extends \BadMethodCallException implements ExceptionInterface
{

}
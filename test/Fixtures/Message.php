<?php
/*
 * This file is part of the Abstract builder package, an RunOpenCode project.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\AbstractBuilder\Tests\Fixtures;

use Psr\Log\LoggerAwareTrait;

/**
 * Class Message
 *
 * @package RunOpenCode\AbstractBuilder\Test\Fixtures
 */
class Message extends Dummy
{
    use LoggerAwareTrait {
        setLogger as private privateSetLogger;
    }

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var int
     */
    private $count;

    /**
     * Message constructor.
     *
     * @param int $id
     * @param string $message
     * @param \DateTime $timestamp
     */
    public function __construct($id, $message, \DateTime $timestamp, $count = 0)
    {
        $this->id = $id;
        $this->message = $message;
        $this->timestamp = $timestamp;
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }
}

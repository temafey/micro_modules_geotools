<?php

/*
 * This file is part of the Geotools library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Geotools\Tests\CLI\Command\Geohash;

use League\Geotools\CLI\Application;
use League\Geotools\CLI\Command\Geohash\Decode;
use RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class DecodeTest extends \League\Geotools\Tests\TestCase
{
    protected $application;
    protected $command;
    protected $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Application;
        $this->application->add(new Decode);

        $this->command = $this->application->find('geohash:decode');

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithoutArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->commandTester->execute(array(
            'command' => $this->command->getName(),
        ));
    }

    public function testExecuteInvalidArguments()
    {
        $this->expectException(\League\Geotools\Exception\RuntimeException::class);
        $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'geohash' => 'foo, bar',
        ));
    }

    public function testExecuteShortGeohash()
    {
        $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'geohash' => 'dp',
        ));

        $this->assertTrue(is_string($this->commandTester->getDisplay()));
        $this->assertMatchesRegularExpression('/42\.1875, -84\.375/', $this->commandTester->getDisplay());
    }

    public function testExecuteLongGeohash()
    {
        $this->commandTester->execute(array(
            'command' => $this->command->getName(),
            'geohash' => 'dppnhep00mpx',
        ));

        $this->assertTrue(is_string($this->commandTester->getDisplay()));
        $this->assertMatchesRegularExpression('/40\.446195071563, -79\.948862101883/', $this->commandTester->getDisplay());
    }
}

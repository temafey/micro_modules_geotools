<?php

/*
 * This file is part of the Geotools library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Geotools\Tests\Coordinate;

use League\Geotools\Coordinate\Ellipsoid;
use League\Geotools\Exception\InvalidArgumentException;
use League\Geotools\Exception\NotMatchingEllipsoidException;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class EllipsoidTest extends \League\Geotools\Tests\TestCase
{
    /**
     * @dataProvider constructorArgumentsWhichThrowException
     */
    public function testConstructWithInverseFlatteningEqualsToZero($invF)
    {
        $this->expectException(InvalidArgumentException::class);
        new Ellipsoid('foo', 'bar', $invF);
    }

    public static function constructorArgumentsWhichThrowException(): array
    {
        return array(
            array(-123),
            array(-0.1),
            array(0),
            array(0.0),
            array('foo'),
            array(' '),
            array(array()),
            array(null),
        );
    }

    /**
     * @dataProvider constructorArgumentsProvider
     */
    public function testConstructor($name, $a, $invF, $expected)
    {
        $ellipsoid = new Ellipsoid($name, $a, $invF);

        $this->assertSame($expected[0], $ellipsoid->getName());
        $this->assertSame($expected[1], $ellipsoid->getA());
        $this->assertSame($expected[2], $ellipsoid->getB());
        $this->assertSame($expected[3], $ellipsoid->getInvF());
        $this->assertSame($expected[4], $ellipsoid->getArithmeticMeanRadius());
    }

    public function constructorArgumentsProvider()
    {
        return array(
            array('name', 'a', 1, array('name', 0.0, 0.0, 1.0, 0.0)),
            array('foo', 'bar', 123, array('foo', 0.0, 0.0, 123.0, 0.0)),
            array(123, 456, 789, array(123, 456.0, 455.4220532319391, 789.0, 455.80735107731306)),
        );
    }

    public function testCreateFromNameUnavailableEllipsoidThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        Ellipsoid::createFromName('foo');
    }

    public function testCreateFromNameEmptyNameThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        Ellipsoid::createFromName(' ');
    }

    public function testCreateFromName()
    {
        $ellipsoid = Ellipsoid::createFromName(Ellipsoid::WGS84);

        $this->assertTrue(is_object($ellipsoid));
        $this->assertInstanceOf('League\Geotools\Coordinate\Ellipsoid', $ellipsoid);
        $this->assertSame('WGS 84', $ellipsoid->getName());
        $this->assertSame(6378136.0, $ellipsoid->getA());
        $this->assertEquals(6356751.31759799, $ellipsoid->getB());
        $this->assertSame(298.257223563, $ellipsoid->getInvF());
        $this->assertEquals(6371007.772532663, $ellipsoid->getArithmeticMeanRadius());
    }

    /**
     * @dataProvider createFromArrayProvider
     */
    public function testCreateFromArrayThrowsException($newEllipsoid)
    {
        $this->expectException(InvalidArgumentException::class);
        Ellipsoid::createFromArray($newEllipsoid);
    }

    public static function createFromArrayProvider(): array
    {
        return array(
            array(
                array()
            ),
            array(
                array(' ')
            ),
            array(
                array('foo')
            ),
            array(
                array(
                    'foo' => 'foo',
                    'bar' => 'bar',
                    'baz' => 'baz'
                )
            ),
            array(
                array(
                    'name' => 'name',
                    'a'    => 'a',
                    'foo'  => 'foo'
                )
            ),
        );
    }

    public function testCreateFromArray()
    {
        $newEllipsoid = array(
            'name' => 'foo ellipsoid',
            'a'    => 6378136.0,
            'invF' => 298.257223563,
        );

        $ellipsoid = Ellipsoid::createFromArray($newEllipsoid);

        $this->assertTrue(is_object($ellipsoid));
        $this->assertInstanceOf('League\Geotools\Coordinate\Ellipsoid', $ellipsoid);
        $this->assertSame('foo ellipsoid', $ellipsoid->getName());
        $this->assertSame(6378136.0, $ellipsoid->getA());
        $this->assertEquals(6356751.31759799, $ellipsoid->getB());
        $this->assertSame(298.257223563, $ellipsoid->getInvF());
        $this->assertEquals(6371007.772532663, $ellipsoid->getArithmeticMeanRadius());
    }

    public function testCoordinatesWithDifferentEllipsoids()
    {
        $this->expectException(NotMatchingEllipsoidException::class);
        $WGS84       = Ellipsoid::createFromName(Ellipsoid::WGS84);
        $ANOTHER_ONE = Ellipsoid::createFromArray(array(
            'name' => 'foo ellipsoid',
            'a'    => 123.0,
            'invF' => 456.0
        ));

        $a = $this->getMockCoordinateReturns(array(1, 2), $WGS84);
        $b = $this->getMockCoordinateReturns(array(3, 4), $ANOTHER_ONE);

        Ellipsoid::checkCoordinatesEllipsoid($a, $b);
    }
}

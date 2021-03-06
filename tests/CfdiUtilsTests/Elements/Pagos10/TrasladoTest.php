<?php
namespace CfdiUtilsTests\Elements\Pagos10;

use CfdiUtils\Elements\Pagos10\Traslado;
use PHPUnit\Framework\TestCase;

class TrasladoTest extends TestCase
{
    /** @var Traslado */
    public $element;

    protected function setUp()
    {
        parent::setUp();
        $this->element = new Traslado();
    }

    public function testGetElementName()
    {
        $this->assertSame('pagos10:Traslado', $this->element->getElementName());
    }
}

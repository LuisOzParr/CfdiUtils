<?php
namespace CfdiUtilsTests\Cleaner;

use CfdiUtils\Cleaner\Cleaner;
use CfdiUtils\Cleaner\CleanerException;
use CfdiUtilsTests\TestCase;

class CleanerTest extends TestCase
{
    public function testConstructorWithEmptyText()
    {
        $cleaner = new Cleaner('');

        $this->expectException(CleanerException::class);
        $cleaner->load('');
    }

    public function testConstructorWithNonCFDI()
    {
        $cleaner = new Cleaner('');
        $this->expectException(CleanerException::class);

        $cleaner->load('<node></node>');
    }

    public function testConstructorWithBadVersion()
    {
        $this->expectException(CleanerException::class);
        new Cleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" version="3.15" />
        ');
    }

    public function testConstructorWithoutInvalidXml()
    {
        $this->expectException(CleanerException::class);

        new Cleaner('<' . 'node>');
    }

    public function testConstructorWithoutVersion()
    {
        $this->expectException(CleanerException::class);
        $this->expectExceptionMessage('version');

        new Cleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" />
        ');
    }

    public function testConstructorWithMinimalCompatibilityVersion32()
    {
        $cleaner = new Cleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" version="3.2" />
        ');
        $this->assertInstanceOf(Cleaner::class, $cleaner, 'Cleaner created with minimum compatibility');
    }

    public function testConstructorWithMinimalCompatibilityVersion33()
    {
        $cleaner = new Cleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" Version="3.3" />
        ');
        $this->assertInstanceOf(Cleaner::class, $cleaner, 'Cleaner created with minimum compatibility');
    }

    public function testCleanOnDetail()
    {
        $basefile = $this->utilAsset('cleaner/v32-dirty.xml');
        $step1 = $this->utilAsset('cleaner/v32-no-addenda.xml');
        $step2 = $this->utilAsset('cleaner/v32-no-nonsat-nodes.xml');
        $step3 = $this->utilAsset('cleaner/v32-no-nonsat-schemalocations.xml');
        $step4 = $this->utilAsset('cleaner/v32-no-nonsat-xmlns.xml');
        foreach ([$basefile, $step1, $step2, $step3, $step4] as $filename) {
            $this->assertFileExists($basefile, "The file $filename for testing does not exists");
        }
        $cleaner = new Cleaner(file_get_contents($basefile));
        $this->assertXmlStringEqualsXmlFile(
            $basefile,
            $cleaner->retrieveXml(),
            'Compare that the document was loaded without modifications'
        );

        $cleaner->removeAddenda();
        $this->assertXmlStringEqualsXmlFile(
            $step1,
            $cleaner->retrieveXml(),
            'Compare that addenda was removed'
        );

        $cleaner->removeNonSatNSNodes();
        $this->assertXmlStringEqualsXmlFile(
            $step2,
            $cleaner->retrieveXml(),
            'Compare that non SAT nodes were removed'
        );

        $cleaner->removeNonSatNSschemaLocations();
        $this->assertXmlStringEqualsXmlFile(
            $step3,
            $cleaner->retrieveXml(),
            'Compare that non SAT schemaLocations were removed'
        );

        $cleaner->removeUnusedNamespaces();
        $this->assertXmlStringEqualsXmlFile(
            $step4,
            $cleaner->retrieveXml(),
            'Compare that xmlns definitions were removed'
        );

        $this->assertXmlStringEqualsXmlFile(
            $step4,
            Cleaner::staticClean(file_get_contents($basefile)),
            'Check static method for cleaning is giving the same results as detailed execution'
        );
    }
}

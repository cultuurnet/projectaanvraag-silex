<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class IntegrationTypeTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * Test IntegrationType
     */
    public function testIntegrationType()
    {
        $type = new IntegrationType();
        $actionButton = 'test';

        $type->setId('api');
        $type->setUrl('http://www.culturefeed.be/api');
        $type->setPrice(80.0);
        $type->setGroupId('group_id');
        $type->setExtraInfo(['Platformonafhankelijk', 'Individuele oplossingen op maat zijn mogelijk']);
        $type->setDescription('Met deze API krijg je toegang tot de zoekengine van de UiTdatabank');
        $type->setName('API');
        $type->setSapiVersion(2);
        $type->setSelfService(true);
        $type->setEnableActivation(true);
        $type->setActionButton($actionButton);
        $type->setType('output');

        $this->assertEquals('api', $type->getId(), 'It correctly returns the integration type id.');
        $this->assertEquals('http://www.culturefeed.be/api', $type->getUrl(), 'It correctly returns the integration type url.');
        $this->assertEquals(80.0, $type->getPrice(), 'It correctly returns the integration type price.');
        $this->assertEquals('group_id', $type->getGroupId(), 'It correctly returns the integration type group id.');
        $this->assertEquals(['Platformonafhankelijk', 'Individuele oplossingen op maat zijn mogelijk'], $type->getExtraInfo(), 'It correctly returns the integration type extra info.');
        $this->assertEquals('Met deze API krijg je toegang tot de zoekengine van de UiTdatabank', $type->getDescription(), 'It correctly returns the integration type description.');
        $this->assertEquals('API', $type->getName(), 'It correctly returns the integration type name.');
        $this->assertEquals(2, $type->getSapiVersion(), 'It correctly returns the action button.');
        $this->assertEquals($actionButton, $type->getActionButton(), 'It correctly returns the action button.');
        $this->assertEquals(true, $type->getSelfService(), 'It correctly returns the service.');
        $this->assertEquals(true, $type->getEnableActivation(), 'It correctly returns the service.');
        $this->assertEquals('output', $type->getType(), 'It correctly returns the type.');

        $this->assertJsonEquals(json_encode($type), 'IntegrationType/data/serialized/integration_type.json');
    }
}

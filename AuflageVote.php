<?php

namespace AuflageVote;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\UninstallContext;
use AuflageVote\Models\AuflageVoteModel;



class AuflageVote extends Plugin
{
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        /** @var CrudService $crud */
        $crud = $this->container->get('shopware_attribute.crud_service');

        $crud->update('s_articles_attributes', 'Neuauflage', 'multi_selection', [
            'displayInBackend' => true,
            'label' => 'Neuauflage von Artikel',
            'arrayStore' => "",
            'entity' => 'Shopware\Models\Article\Article',
        ], null, true);

        $this->updateSchema();
    
         
    }

    public function uninstall(UninstallContext $context)
	{
	
	}


    private function updateSchema()
    {
        /** @var ModelManager $entityManager */
        $entityManager = $this->container->get('models');

        $tool = new SchemaTool($entityManager);

        $classMetaData = [
            $entityManager->getClassMetadata(AuflageVoteModel::class)
        ];

        $tool->createSchema($classMetaData);

    }

}
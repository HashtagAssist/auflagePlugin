<?php
namespace AuflageVote\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="auflage_vote", options={"collate"="utf8_unicode_ci"})
 */
class AuflageVoteModel extends ModelEntity
{
    /**
    * @var int
    *
    * @ORM\Column(name="id", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="IDENTITY")
    */
    private $id;
    
    /**
    * @var string
    * @ORM\Column(name="article_id", type="string")
    */
    private $articleId;

    /**
    * @var string
    * @ORM\Column(name="article_id_old", type="string")
    */
    private $preEditionId;

    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $articleId
     */
    public function setName($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->articleId;
    }

     /**
     * @param string $preEditionId
     */
    public function setPreEditionId($preEditionId)
    {
        $this->preEditionId = $preEditionId;
    }

    /**
     * @return string
     */
    public function getPreEditionId()
    {
        return $this->preEditionId;
    }
}
<?php
namespace ArticleVote\Components;

/**
 * Class AuflageVote
 *
 * @package AuflageVote\Components
 */
class PreEditionVote
{

    /**
     * @param string $PreEditionVote
     * @return string
     */
    public function getPreEditionVote($PreEditionVote)
    {
        switch (strlen($PreEditionVote)) {
            case 8:
                $PreEditionVote = 'gtin8';
                break;
            case 12:
                $PreEditionVote = 'gtin12';
                break;
            case 13:
                $PreEditionVote = 'gtin13';
                break;
            case 14:
                $PreEditionVote = 'gtin14';
                break;
            default:
                $PreEditionVote = '';
        }
        return $PreEditionVote;
    }
}
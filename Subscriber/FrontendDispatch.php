<?php

namespace AuflageVote\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\Shop;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;

class FrontendDispatch implements SubscriberInterface
{


    /** @var ConfigReader */
    private $configReader;

    /** @var Container */
    private $container;

    /** @var string */
    private $pluginName;

    /**
     * Constructor of the subscriber. Sets the instance of the bootstrap.
     *
     * @param $pluginName
     * @param ConfigReader $configReader
     * @param Container $container
     */
    public function __construct($pluginName, ConfigReader $configReader, Container $container)
    {
        $this->container      = $container;
        $this->configReader   = $configReader;
        $this->pluginName     = $pluginName;

    }
    

    /**
     * Returns the subscribed events.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onPostDispatchListing',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Search' => 'onListingExtension',
        ];
    }

    public function onFrontendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        //debug
        function console_log( $data ){
            echo '<script>';
            echo 'console.log('. json_encode( $data ) .')';
            echo '</script>';
        }

        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->getSubject();

        /** @var \Enlight_View_Default $view */
        $view = $controller->View();

        $config = $this->configReader->getByPluginName($this->pluginName, $this->container->get('shop'));

        $sArticle = $view->getAssign('sArticle');
        $testVote     = '';
        console_log($sArticle);
          
        if (!is_null($sArticle) && isset($sArticle['neuauflage'])) {
            $preEditionfromArticles = substr($sArticle['neuauflage'],0, -1);
            $preEditionfromArticles = substr($preEditionfromArticles,1);
            $preEditionfromArticles = preg_split("/([|])/", $sArticle['neuauflage'], -1, PREG_SPLIT_NO_EMPTY);
            $i = 0;
            foreach($preEditionfromArticles as $key => $value){
                $ordernumber = $value;
                //get article ID from ordernumber
                $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();
                $queryBuilder->select('article.articleID')
                ->from('s_articles_details', 'article')
                ->where('article.ordernumber = :ordernumber')
                ->setParameter(':ordernumber', $ordernumber);
                $articleIdQuery = $queryBuilder->execute()->fetchAll();
                if(!$articleId){
                    $articleId[] = $articleIdQuery[0]['articleID'];
                }
                else{
                    array_push($articleId ,$articleIdQuery[0]['articleID']);
                }
                //get comments
                $connection =  $this->container->get('dbal_connection');
                $sql = 'SELECT sa.name AS saname, sv.name, sv.headline, sv.comment, sv.points, sv.datum FROM s_articles_vote sv LEFT JOIN s_articles sa ON sv.articleID = sa.id WHERE sv.articleID = :articleId ';
                $articleVotesMerged[] = $connection->fetchAll($sql, [':articleId' => $articleId[$i]]);

                //Get revierws of vorauflage
                foreach ($articleIdQuery as $key => $value) {

                    $articleIdArr = $value['articleID'];
                    $ordernumber = $sArticle['ordernumber'];
                  
                    $connection =  $this->container->get('dbal_connection');
                    $sql = 'SELECT sv.points AS vpoints FROM s_articles_vote sv LEFT JOIN s_articles sa ON sv.articleID = sa.id WHERE sv.articleID = :articleId ';
                    $articleVotesCounts = $connection->fetchAll($sql, [':articleId' => $articleIdArr]);
                    //$counter = $counter + count($articleVotesCounts);
                 
                    foreach ($articleVotesCounts as $articleVotesCount) {
                        //get old full average
                        $oldAv = round($sArticle['sVoteAverage']['average'] * $sArticle['sVoteAverage']['count'], 2);
                        //new average
                        $newAv =  round($articleVotesCount['vpoints']*2 + $oldAv,2);
                        //newCount
                        $newCount =$sArticle['sVoteAverage']['count'] + 1;
                        //new calculated average
                        $sArticle['sVoteAverage']['average'] = round($newAv / $newCount,2);
                        //new count
                        $sArticle['sVoteAverage']['count'] = $newCount;
                        $sArticle['sVoteAverage']['pointCount'][]= array('total'=>strval($oldVotes+1), 'points'=>$articleVotesCount['vpoints']);
                    }
                }
                $i++;
            }
            $view->assign('sArticle', $sArticle);

        } 

        $counter = 0;
        $points = 0;
        $i=0;
        foreach($articleVotesMerged as $articleVotePoints){
            foreach($articleVotePoints as $articleVotePoint){
            $counter = $counter + 1;
            $pointcounter = $articleVotePoint['points'];
            $points =  $points + $pointcounter;
            $i++;
            }
        }

        $view->assign(
            'ArticleVote',
            [
                'active'  => $config['neuauflageActive'],
                'vorauflageVote' => empty($articleVotesMerged) ? false : $articleVotesMerged,
            ]
        );
    }



    public function onPostDispatchListing(\Enlight_Event_EventArgs $args)
    {
        $controller = $args->getSubject();

        $request = $controller->Request();

        $response = $controller->Response();

        $view = $controller->View();

        function console_log( $data ){
            echo '<script>';
            echo 'console.log('. json_encode( $data ) .')';
            echo '</script>';
        }

        //Get article from View  
        $sArticles = $view->getAssign('sArticles');
        //iterate throug article
        foreach ($sArticles as $sArticle) { 
                //prepare empty arrays
                $articleIdQuery = [];
                //old votes
                if (!is_null($sArticle['sVoteAverage']['pointCount'])) {
                    $oldVotes = count($sArticle['sVoteAverage']['pointCount']);
                } else {
                    $oldVotes = 0;
                }
                //check if vorauflage is set
                if (!is_null($sArticle['neuauflage']) && $sArticle['neuauflage'] !== "") {
                    $preEditionfromArticles = substr($sArticle['neuauflage'], 0, -1);
                    $preEditionfromArticles = substr($preEditionfromArticles, 1);
                    $preEditionfromArticles = preg_split("/([|])/", $sArticle['neuauflage'], -1, PREG_SPLIT_NO_EMPTY);

                    //Get article ID of variations
                    foreach ($preEditionfromArticles as $preEditionfromArticle) {
                        $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();
                        $queryBuilder->select('article.articleID')
                        ->from('s_articles_details', 'article')
                        ->where('article.ordernumber = :ordernumber')
                        ->setParameter(':ordernumber', $preEditionfromArticle);
                        $articleIdQuery[] = $queryBuilder->execute()->fetchAll();
                    }
                    //Get revierws of vorauflage
                    foreach ($articleIdQuery as $key => $value) {
                        $articleId = $value[0]['articleID'];
                        $ordernumber = $sArticle['ordernumber'];

                        $connection =  $this->container->get('dbal_connection');
                        $sql = 'SELECT sv.points AS vpoints FROM s_articles_vote sv LEFT JOIN s_articles sa ON sv.articleID = sa.id WHERE sv.articleID = :articleId ';
                        $articleVotesCounts = $connection->fetchAll($sql, [':articleId' => $articleId]);
                        //$counter = $counter + count($articleVotesCounts);

                        foreach ($articleVotesCounts as $articleVotesCount) {
                            //get old full average
                            $oldAv = round($sArticle['sVoteAverage']['average'] * $sArticle['sVoteAverage']['count'], 2);
                            //new average
                            $newAv =  round($articleVotesCount['vpoints']*2 + $oldAv,2);
                            //newCount
                            $newCount =$sArticle['sVoteAverage']['count'] + 1;
                            //new calculated average
                            $sArticle['sVoteAverage']['average'] = round($newAv / $newCount,2);
                            //new count
                            $sArticle['sVoteAverage']['count'] = $newCount;
                            $sArticle['sVoteAverage']['pointCount'][]= array('total'=>strval($oldVotes+1), 'points'=>$articleVotesCount['vpoints']);
                        }
                    }
                }
                $newSArticle[] = $sArticle;
        }
        $view->assign('sArticles', $newSArticle);
    }



    public function onListingExtension(\Enlight_Event_EventArgs $args)
    {
        $controller = $args->getSubject();

        $request = $controller->Request();

        $response = $controller->Response();

        $view = $controller->View();

        function console_log($data)
        {
            echo '<script>';
            echo 'console.log('. json_encode($data) .')';
            echo '</script>';
        }

        //Get article from View
        $sArticles = $view->getAssign('sSearchResults');
        $sArticles = $sArticles['sArticles'];
        console_log($sArticles);

        //iterate throug article
        foreach ($sArticles as $sArticle) {
            //prepare empty arrays
            $articleIdQuery = [];
            //old votes
            if (!is_null($sArticle['sVoteAverage']['pointCount'])) {
                $oldVotes = count($sArticle['sVoteAverage']['pointCount']);
            } else {
                $oldVotes = 0;
            }
            //check if vorauflage is set
            if (!is_null($sArticle['neuauflage']) && $sArticle['neuauflage'] !== "") {
                $preEditionfromArticles = substr($sArticle['neuauflage'], 0, -1);
                $preEditionfromArticles = substr($preEditionfromArticles, 1);
                $preEditionfromArticles = preg_split("/([|])/", $sArticle['neuauflage'], -1, PREG_SPLIT_NO_EMPTY);

                //Get article ID of variations
                foreach ($preEditionfromArticles as $preEditionfromArticle) {
                    $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();
                    $queryBuilder->select('article.articleID')
                    ->from('s_articles_details', 'article')
                    ->where('article.ordernumber = :ordernumber')
                    ->setParameter(':ordernumber', $preEditionfromArticle);
                    $articleIdQuery[] = $queryBuilder->execute()->fetchAll();
                }
                //Get revierws of vorauflage
                foreach ($articleIdQuery as $key => $value) {
                    $articleId = $value[0]['articleID'];
                    $ordernumber = $sArticle['ordernumber'];

                    $connection =  $this->container->get('dbal_connection');
                    $sql = 'SELECT sv.points AS vpoints FROM s_articles_vote sv LEFT JOIN s_articles sa ON sv.articleID = sa.id WHERE sv.articleID = :articleId ';
                    $articleVotesCounts = $connection->fetchAll($sql, [':articleId' => $articleId]);
                    //$counter = $counter + count($articleVotesCounts);

                    foreach ($articleVotesCounts as $articleVotesCount) {
                        //get old full average
                        $oldAv = round($sArticle['sVoteAverage']['average'] * $sArticle['sVoteAverage']['count'], 2);
                        //new average
                        $newAv =  round($articleVotesCount['vpoints']*2 + $oldAv, 2);
                        //newCount
                        $newCount =$sArticle['sVoteAverage']['count'] + 1;
                        //new calculated average
                        $sArticle['sVoteAverage']['average'] = round($newAv / $newCount, 2);
                        //new count
                        $sArticle['sVoteAverage']['count'] = $newCount;
                        $sArticle['sVoteAverage']['pointCount'][]= array('total'=>strval($oldVotes+1), 'points'=>$articleVotesCount['vpoints']);
                    }
                }
            }
            $newSArticle[] = $sArticle;
        }
        console_log($newSArticle);
        $view->assign('sArticles', $newSArticle);    
        $view->assign(
            'sSearchResults',
            [
                'active'  => $config['sArticles'],
                'sArticles' => $newSArticle,
            ]
        );
    }

}

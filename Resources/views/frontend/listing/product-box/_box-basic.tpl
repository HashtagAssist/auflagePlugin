 {extends file='parent:frontend/listing/product-box/box-basic.tpl'}
    {block name='frontend_listing_box_article_rating'}
        {if !{config name=VoteDisable}}
            <div class="product--rating-container">
                {if $sArticle.sVoteAverage.average || $sArticle.vaverage}
                {assign var='points' value=$sArticle.sVoteAverage.average}
                {assign var='starCounter' value=$sArticle.sVoteAverage.count}
                {$points|print_r}
                    {include file='frontend/_includes/rating.tpl' points=$points type="aggregated" label=true count=$starCounter microData=false}
                {/if}
            </div>
        {/if}
    {/block}

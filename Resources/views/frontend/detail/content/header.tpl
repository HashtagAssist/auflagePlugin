{extends file='parent:frontend/detail/content/header.tpl'}

{block name="frontend_detail_comments_overview"}
    {if !{config name=VoteDisable}}
        <div class="product--rating-container">
            {s namespace="frontend/detail/actions" name="DetailLinkReview" assign="snippetDetailLinkReview"}{/s}
            <a href="#product--publish-comment" class="product--rating-link" rel="nofollow" title="{$snippetDetailLinkReview|escape}">
            {assign var='points' value=$sArticle.sVoteAverage.average}
            {assign var='starCounter' value=$sArticle.sVoteAverage.count}
                {include file='frontend/_includes/rating.tpl' points=$points type="aggregated" count=$starCounter}
            </a>
        </div>
    {/if}
{/block}
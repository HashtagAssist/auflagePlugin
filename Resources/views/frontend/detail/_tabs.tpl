{extends file='parent:frontend/detail/tabs.tpl'}
{block name="frontend_detail_tabs_rating"}
    {if !{config name=VoteDisable}}
        <a href="#" class="tab--link" title="{s name='DetailTabsRating'}{/s}" data-tabName="rating">
            {s name='DetailTabsRating'}{/s}
            {block name="frontend_detail_tabs_navigation_rating_count"}
                <span class="product--rating-count">{assign var='voteCount' value=0+$sArticle.sVoteAverage.count+$ArticleVote.vorauflageVoteCount} {$voteCount}</span>
            {/block}
        </a>
    {/if}
{/block}
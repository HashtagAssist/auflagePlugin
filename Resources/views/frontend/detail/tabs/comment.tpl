{extends file='parent:frontend/detail/tabs/comment.tpl'}
        {block name='frontend_detail_comment_post'}
        {if $ArticleVote.vorauflageVote}
            {foreach from=$ArticleVote.vorauflageVote item=articlevotewrapper key=i}
                {foreach from=$articlevotewrapper item=articlevote key=i}
                <div class="review--entry" itemprop="review" itemscope itemtype="https://schema.org/Review">
                    <div class="pre-edition-stars">{include file="frontend/_includes/rating.tpl" points=$articlevote.points base=5}</div> 
                    <div class="pre-edition-title">Bewertung von Vorauflage: {$articlevote.saname} </div> 
                    <strong class="content--label">{s namespace="frontend/detail/comment" name="DetailCommentInfoFrom"}{/s}</strong>
                    <span class="content--field" itemprop="author">{if $articlevote.name}{$articlevote.name}{else}{s namespace="frontend/detail/comment" name="DetailCommentAnonymousName"}{/s}{/if}</span>  
                    <strong class="content--label">{s namespace="frontend/detail/comment" name="DetailCommentInfoAt"}{/s}</strong>
                    <meta itemprop="datePublished" content="{$articlevote.datum|date_format:'%Y-%m-%d'}">
                    <span class="content--field">{$articlevote.datum|date:"DATE_MEDIUM"}</span>
                    <h4 class="content--title" itemprop="name">
                        {$articlevote.headline}
                    </h4>
                    {$articlevote.comment} 
                </div>
                {/foreach}
            {/foreach}
        {/if}
    {$smarty.block.parent}
    {/block}

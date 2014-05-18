<div class="tweet-con">
  <ul class="tweet-list clearfix">
    <li class="item clearfix">
      <ul class="tweet-items">
      </ul>
    </li>
    <li class="item">
      <ul class="tweet-items">
      </ul>
    </li>
    <li class="item">
      <ul class="tweet-items">
      </ul>
    </li>
    <li class="item">
      <ul class="tweet-items">
      </ul>
    </li>
  </ul>
</div>

<script type="text/tpl" id="tweet-item-tpl">
        <li class="tweet-signle-item clearfix" data-uuid="{{uuid}}">
        {{#if media}}<div class="avatar"><img src="{{media}}" alt="" /></div>{{/if}}
          <div class="desc {{#unless media}}no-media{{/unless}}">
            <div class="title">@{{name}}</div>
            <div class="from from-{{from}}">{{from}}</div>
            <div class="profile-msg">{{content}}</div>
            <div class="time">{{date}}</div>
          </div>
          <div class="btns">
            <ul class="btn-items">
              <li class="retweet">Retweet</li>
              <li class="comment">Comment</li>
            </ul>
          </div>
        </li>
</script>

<script type="text/tpl" id="tweet-panel-tpl">
  
</script>
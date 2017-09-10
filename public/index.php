<?php

require_once '../library/Podcast.php';

$episodes = Podcast::getEpisodes();

?>
<!doctype html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1">
    <title>Decivilization: A Post-Apocalyptic RPG Podcast</title>

    <meta name="description" content="Decivilization is a post-apocalyptic tabletop RPG in a world full of warring city-states, raiders, and strange creatures, all fighting to survive.">
    <meta name="keywords" content="Decivilization,Post-Apocalyptic,RPG,Podcast,Role-Playing Game,Tabletop Game">

    <meta property="twitter:site:id" content="904913802403217408">
    <meta property="twitter:site" content="@DecivPodcast">
    <meta property="twitter:domain" content="deciv.com">
    <meta property="twitter:card" content="summary">
    <meta property="twitter:title" content="Decivilization: A Post-Apocalyptic RPG Podcast">
    <meta property="twitter:description" content="Decivilization is a post-apocalyptic tabletop RPG in a world full of warring city-states, raiders, and strange creatures, all fighting to survive.">
    <meta property="twitter:image" content="http://deciv.com/images/podcast-cover.jpg">
    <meta property="twitter:image:alt" content="Decivilization Logo">
    <meta property="twitter:imageUrl" content="http://deciv.com/images/podcast-cover.jpg">
    <meta property="og:site_name" content="The Decivilization Podcast">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Decivilization: A Post-Apocalyptic RPG Podcast">
    <meta property="og:url" content="http://deciv.com/">
    <meta property="og:image" content="http://deciv.com/images/podcast-cover.jpg">
    <meta property="og:description" content="Decivilization is a post-apocalyptic tabletop RPG in a world full of warring city-states, raiders, and strange creatures, all fighting to survive.">

    <link rel="canonical" href="http://deciv.com/">
    <link rel="apple-touch-icon-precomposed" href="http://deciv.com/images/share-icon.png">
    <link rel="alternate" type="application/rss+xml" title="The Decivilization Podcast" href="/feed">

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Rock+Salt" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="/css/base.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
    <script src="https://use.fontawesome.com/5507da34bb.js"></script>
    <script src="/js/base.js"></script>
</head>
<body class="no-js">
<script>$(document.body).removeClass('no-js');</script>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-448886-3', 'auto');
    ga('send', 'pageview');
</script>

<header>
    <h1><img src="images/logo.png">Decivilization</h1>
    <h2>A Post-Apocalyptic RPG Podcast</h2>
</header>

<nav>
    <ul>
        <li><a href="/feed"><i class="fa fa-rss"></i>RSS Feed</a></li>
        <li><a href="https://twitter.com/DecivPodcast" target="_blank"><i class="fa fa-twitter"></i>Twitter</a></li>
    </ul>
</nav>

<main>
    <?php foreach ($episodes as $episode): ?>
    <article>
        <h3><?=$episode['title']?></h3>
        <div class="description"><?=$episode['htmlDescription']?></div>
        <p class="play"></p>
        <div class="download">
            <a href="javascript:;" class="btn" onclick="displayPlayControls.call(this, '<?=$episode['relativeUrl']?>')"><i class="fa fa-play"></i>&nbsp; Play</a>
            <a href="<?=$episode['relativeUrl']?>" class="btn" download><i class="fa fa-download"></i>&nbsp; Download</a>
            <span class="publish-date"><?=date('F jS, Y', $episode['timestamp'])?></span>
        </div>
    </article>
    <?php endforeach; ?>
</main>

<footer>A role-playing game by River City Games, coming soon.</footer>

</body>
</html>
